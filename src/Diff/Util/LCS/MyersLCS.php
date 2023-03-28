<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Util\LCS;

use DR\JBDiff\Diff\Util\DiffConfig;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Util\BitSet;
use RuntimeException;
use function count;

/**
 * Algorithm for finding the longest common subsequence of two strings
 * Based on E.W. Myers / An O(ND) Difference Algorithm and Its Variations / 1986
 * O(ND) runtime, O(N) memory
 */
class MyersLCS
{
    private int $count1;
    private int $count2;

    /** @var int[] */
    private array $vForward = [];
    /** @var int[] */
    private array $vBackward = [];

    /**
     * @param int[] $first
     * @param int[] $second
     */
    public function __construct(
        private readonly array $first,
        private readonly array $second,
        private readonly int $start1 = 0,
        ?int $count1 = null,
        private readonly int $start2 = 0,
        ?int $count2 = null,
        private readonly BitSet $changes1 = new BitSet(),
        private readonly BitSet $changes2 = new BitSet()
    ) {
        $this->count1 = $count1 ?? count($first);
        $this->count2 = $count2 ?? count($second);

        $this->changes1->set($this->start1, $this->start1 + $this->count1);
        $this->changes2->set($this->start2, $this->start2 + $this->count2);
    }

    /**
     * @return array{0: BitSet, 1: BitSet}
     */
    public function getChanges(): array
    {
        return [$this->changes1, $this->changes2];
    }

    /**
     * Runs O(ND) Myers algorithm where D is bound by A + B * sqrt(N)
     * <p/>
     * Under certain assumptions about the distribution of the elements of the sequences the expected
     * running time of the myers algorithm is O(N + D^2). Thus under given constraints it reduces to O(N).
     */
    public function executeLinear(): void
    {
        try {
            $threshold = 20000 + (10 * (int)sqrt($this->count1 + $this->count2));
            $this->execute($threshold, false);
            // @codeCoverageIgnoreStart
        } catch (DiffToBigException $e) {
            throw new RuntimeException('Illegal state, should not throw file too big diff exception', 0, $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws DiffToBigException
     */
    public function executeWithThreshold(): void
    {
        $threshold = max(20000 + (10 * (int)sqrt($this->count1 + $this->count2)), DiffConfig::DELTA_THRESHOLD_SIZE);
        $this->execute($threshold, true);
    }

    /**
     * @throws DiffToBigException
     */
    private function execute(int $threshold, bool $throwException): void
    {
        if ($this->count1 === 0 || $this->count2 === 0) {
            return;
        }

        $this->executeAlgorithm(0, $this->count1, 0, $this->count2, min($threshold, $this->count1 + $this->count2), $throwException);
    }

    /**
     * @throws DiffToBigException
     */
    private function executeAlgorithm(int $oldStart, int $oldEnd, int $newStart, int $newEnd, int $differenceEstimate, bool $throwException): void
    {
        assert($oldStart <= $oldEnd && $newStart <= $newEnd);

        if ($oldStart < $oldEnd && $newStart < $newEnd) {
            $oldLength = $oldEnd - $oldStart;
            $newLength = $newEnd - $newStart;

            $this->vForward[$newLength + 1]  = 0;
            $this->vBackward[$newLength + 1] = 0;

            $halfD = (int)(($differenceEstimate + 1) / 2);
            $xx    = $kk = $td = -1;

            // :loop
            for ($d = 0; $d <= $halfD; ++$d) {
                $L = $newLength + max(-$d, -$newLength + (($d ^ $newLength) & 1));
                $R = $newLength + min($d, $oldLength - (($d ^ $oldLength) & 1));

                for ($k = $L; $k <= $R; $k += 2) {
                    $x = ($k === $L || ($k !== $R && $this->vForward[$k - 1] < $this->vForward[$k + 1]))
                        ? $this->vForward[$k + 1]
                        : $this->vForward[$k - 1] + 1;

                    $y                  = $x - $k + $newLength;
                    $x                  += $this->commonSubsequenceLengthForward(
                        $oldStart + $x,
                        $newStart + $y,
                        min($oldEnd - $oldStart - $x, $newEnd - $newStart - $y)
                    );
                    $this->vForward[$k] = $x;
                }

                if (($oldLength - $newLength) % 2 !== 0) {
                    for ($k = $L; $k <= $R; $k += 2) {
                        if ($oldLength - ($d - 1) <= $k && $k <= $oldLength + ($d - 1)) {
                            if ($this->vForward[$k] + $this->vBackward[$newLength + $oldLength - $k] >= $oldLength) {
                                $xx = $this->vForward[$k];
                                $kk = $k;
                                $td = 2 * $d - 1;
                                break 2; // :loop
                            }
                        }
                    }
                }

                for ($k = $L; $k <= $R; $k += 2) {
                    $x = ($k === $L || ($k !== $R && $this->vBackward[$k - 1] < $this->vBackward[$k + 1]))
                        ? $this->vBackward[$k + 1]
                        : $this->vBackward[$k - 1] + 1;

                    $y                   = $x - $k + $newLength;
                    $x                   += $this->commonSubsequenceLengthBackward(
                        $oldEnd - 1 - $x,
                        $newEnd - 1 - $y,
                        min($oldEnd - $oldStart - $x, $newEnd - $newStart - $y)
                    );
                    $this->vBackward[$k] = $x;
                }

                if (($oldLength - $newLength) % 2 === 0) {
                    for ($k = $L; $k <= $R; $k += 2) {
                        if ($oldLength - $d <= $k && $k <= $oldLength + $d) {
                            if ($this->vForward[$oldLength + $newLength - $k] + $this->vBackward[$k] >= $oldLength) {
                                $xx = $oldLength - $this->vBackward[$k];
                                $kk = $oldLength + $newLength - $k;
                                $td = 2 * $d;
                                break 2; // :loop
                            }
                        }
                    }
                }
            }

            if ($td > 1) {
                $yy      = $xx - $kk + $newLength;
                $oldDiff = (int)(($td + 1) / 2);
                if (0 < $xx && 0 < $yy) {
                    $this->executeAlgorithm($oldStart, $oldStart + $xx, $newStart, $newStart + $yy, $oldDiff, $throwException);
                }
                if ($oldStart + $xx < $oldEnd && $newStart + $yy < $newEnd) {
                    $this->executeAlgorithm($oldStart + $xx, $oldEnd, $newStart + $yy, $newEnd, $td - $oldDiff, $throwException);
                }
            } elseif ($td >= 0) {
                $x = $oldStart;
                $y = $newStart;
                while ($x < $oldEnd && $y < $newEnd) {
                    $commonLength = $this->commonSubsequenceLengthForward($x, $y, min($oldEnd - $x, $newEnd - $y));
                    if ($commonLength > 0) {
                        $this->addUnchanged($x, $y, $commonLength);
                        $x += $commonLength;
                        $y += $commonLength;
                    } elseif ($oldEnd - $oldStart > $newEnd - $newStart) {
                        ++$x;
                    } else {
                        ++$y;
                    }
                }
                // @codeCoverageIgnoreStart
            } elseif ($throwException) {
                // The difference is more than the given estimate
                throw new DiffToBigException();
                // @codeCoverageIgnoreEnd
            }
        }
    }

    private function addUnchanged(int $start1, int $start2, int $count): void
    {
        $this->changes1->clear($this->start1 + $start1, $this->start1 + $start1 + $count);
        $this->changes2->clear($this->start2 + $start2, $this->start2 + $start2 + $count);
    }

    private function commonSubsequenceLengthForward(int $oldIndex, int $newIndex, int $maxLength): int
    {
        $x = $oldIndex;
        $y = $newIndex;

        $maxLength = min($maxLength, $this->count1 - $oldIndex, $this->count2 - $newIndex);
        while ($x - $oldIndex < $maxLength && $this->first[$this->start1 + $x] === $this->second[$this->start2 + $y]) {
            ++$x;
            ++$y;
        }

        return $x - $oldIndex;
    }

    private function commonSubsequenceLengthBackward(int $oldIndex, int $newIndex, int $maxLength): int
    {
        $x = $oldIndex;
        $y = $newIndex;

        $maxLength = min($maxLength, min($oldIndex, $newIndex) + 1);
        while ($oldIndex - $x < $maxLength && $this->first[$this->start1 + $x] === $this->second[$this->start2 + $y]) {
            --$x;
            --$y;
        }

        return $oldIndex - $x;
    }
}
