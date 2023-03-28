<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Util\LCS;

use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Util\BitSet;
use function count;

class PatienceIntLCS
{
    private int $count1;
    private int $count2;

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
    }

    /**
     * @return array{0: BitSet, 1: BitSet}
     */
    public function getChanges(): array
    {
        return [$this->changes1, $this->changes2];
    }

    /**
     * @throws DiffToBigException
     */
    public function execute(bool $failOnSmallReduction = false): void
    {
        $thresholdCheckCounter = $failOnSmallReduction ? 2 : -1;
        $this->executeAlgorithm($this->start1, $this->count1, $this->start2, $this->count2, $thresholdCheckCounter);
    }

    /**
     * @throws DiffToBigException
     */
    private function executeAlgorithm(int $start1, int $count1, int $start2, int $count2, int $thresholdCheckCounter): void
    {
        if ($count1 === 0 && $count2 === 0) {
            return;
        }

        if ($count1 === 0 || $count2 === 0) {
            $this->addChange($start1, $count1, $start2, $count2);

            return;
        }

        $startOffset = $this->matchForward($start1, $count1, $start2, $count2);
        $start1      += $startOffset;
        $start2      += $startOffset;
        $count1      -= $startOffset;
        $count2      -= $startOffset;

        $endOffset = $this->matchBackward($start1, $count1, $start2, $count2);
        $count1    += $endOffset;
        $count2    += $endOffset;

        if ($count1 === 0 || $count2 === 0) {
            $this->addChange($start1, $count1, $start2, $count2);
        } else {
            if ($thresholdCheckCounter === 0) {
                $this->checkReduction($count1, $count2);
            }
            $thresholdCheckCounter = max(-1, $thresholdCheckCounter - 1);

            $uniqueLCS = new UniqueLCS($this->first, $this->second, $start1, $count1, $start2, $count2);
            $matching  = $uniqueLCS->execute();

            if ($matching === null) {
                if ($thresholdCheckCounter >= 0) {
                    $this->checkReduction($count1, $count2);
                }
                $intLCS = new MyersLCS($this->first, $this->second, $start1, $count1, $start2, $count2, $this->changes1, $this->changes2);
                $intLCS->executeLinear();
            } else {
                $matched = count($matching[0] ?? []);
                assert($matched > 0);

                $c1 = $matching[0][0] ?? 0;
                $c2 = $matching[1][0] ?? 0;

                $this->executeAlgorithm($start1, $c1, $start2, $c2, $thresholdCheckCounter);

                $matchingLen = count($matching[0]);
                for ($i = 1; $i < $matchingLen; $i++) {
                    $s1 = ($matching[0][$i - 1] ?? 0) + 1;
                    $s2 = ($matching[1][$i - 1] ?? 0) + 1;

                    $c1 = ($matching[0][$i] ?? 0) - $s1;
                    $c2 = ($matching[1][$i] ?? 0) - $s2;

                    if ($c1 > 0 || $c2 > 0) {
                        $this->executeAlgorithm($start1 + $s1, $c1, $start2 + $s2, $c2, $thresholdCheckCounter);
                    }
                }

                if (($matching[0][$matched - 1] ?? 0) === $count1 - 1) {
                    $s1 = $count1 - 1;
                    $c1 = 0;
                } else {
                    $s1 = ($matching[0][$matched - 1] ?? 0) + 1;
                    $c1 = $count1 - $s1;
                }

                if (($matching[1][$matched - 1] ?? 0) === $count2 - 1) {
                    $s2 = $count2 - 1;
                    $c2 = 0;
                } else {
                    $s2 = ($matching[1][$matched - 1] ?? 0) + 1;
                    $c2 = $count2 - $s2;
                }

                $this->executeAlgorithm($start1 + $s1, $c1, $start2 + $s2, $c2, $thresholdCheckCounter);
            }
        }
    }

    private function matchForward(int $start1, int $count1, int $start2, int $count2): int
    {
        $size = min($count1, $count2);
        $idx  = 0;
        for ($i = 0; $i < $size; $i++) {
            if (($this->first[$start1 + $i] ?? 0) !== ($this->second[$start2 + $i] ?? 0)) {
                break;
            }
            ++$idx;
        }

        return $idx;
    }

    private function matchBackward(int $start1, int $count1, int $start2, int $count2): int
    {
        $size = min($count1, $count2);
        $idx  = 0;
        for ($i = 0; $i <= $size; $i++) {
            if (($this->first[$start1 + $count1 - $i] ?? 0) !== ($this->second[$start2 + $count2 - $i] ?? 0)) {
                break;
            }

            ++$idx;
        }

        return $idx;
    }

    private function addChange(int $start1, int $count1, int $start2, int $count2): void
    {
        $this->changes1->set($start1, $start1 + $count1);
        $this->changes2->set($start2, $start2 + $count2);
    }

    /**
     * @throws DiffToBigException
     */
    private function checkReduction(int $count1, int $count2): void
    {
        if ($count1 * 2 < $this->count1) {
            return;
        }
        if ($count2 * 2 < $this->count2) {
            return;
        }
        throw new DiffToBigException();
    }
}
