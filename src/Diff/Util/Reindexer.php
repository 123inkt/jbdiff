<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Util;

use DR\JBDiff\Util\BitSet;
use function count;

class Reindexer
{
    /** @var int[][] */
    private array $oldIndices = [];

    /** @var array{0: int, 1: int} */
    private array $originalLengths = [-1, 1];

    /** @var array{0: int, 1: int} */
    private array $discardedLengths = [-1, 1];

    /**
     * @param int[] $ints1
     * @param int[] $ints2
     *
     * @return array{0: int[], 1: int[]}
     */
    public function discardUnique(array $ints1, array $ints2): array
    {
        $discarded = $this->discard($ints2, $ints1, 0);

        return [$discarded, $this->discard($discarded, $ints2, 1)];
    }

    /**
     * @param array{0: BitSet, 1: BitSet} $discardedChanges
     */
    public function reindex(array $discardedChanges, LCSBuilderInterface $builder): void
    {
        if ($this->discardedLengths[0] === $this->originalLengths[0] && $this->discardedLengths[1] === $this->originalLengths[1]) {
            $changes1 = $discardedChanges[0];
            $changes2 = $discardedChanges[1];
        } else {
            $changes1 = new BitSet();
            $changes2 = new BitSet();

            $x = $y = 0;
            while ($x < $this->discardedLengths[0] || $y < $this->discardedLengths[1]) {
                if (($x < $this->discardedLengths[0] && $y < $this->discardedLengths[1])
                    && $discardedChanges[0]->has($x) === false
                    && $discardedChanges[1]->has($y) === false
                ) {
                    $x = self::increment($this->oldIndices[0], $x, $changes1, $this->originalLengths[0]);
                    $y = self::increment($this->oldIndices[1], $y, $changes2, $this->originalLengths[1]);
                } elseif ($discardedChanges[0]->has($x)) {
                    $changes1->set(self::getOriginal($this->oldIndices[0], $x));
                    $x = self::increment($this->oldIndices[0], $x, $changes1, $this->originalLengths[0]);
                } elseif ($discardedChanges[1]->has($y)) {
                    $changes2->set(self::getOriginal($this->oldIndices[1], $y));
                    $y = self::increment($this->oldIndices[1], $y, $changes2, $this->originalLengths[1]);
                }
            }

            if ($this->discardedLengths[0] === 0) {
                $changes1->set(0, $this->originalLengths[0]);
            } else {
                $changes1->set(0, $this->oldIndices[0][0]);
            }
            if ($this->discardedLengths[1] === 0) {
                $changes2->set(0, $this->originalLengths[1]);
            } else {
                $changes2->set(0, $this->oldIndices[1][0]);
            }
        }

        $x = 0;
        $y = 0;
        while ($x < $this->originalLengths[0] && $y < $this->originalLengths[1]) {
            $startX = $x;
            while ($x < $this->originalLengths[0] && $y < $this->originalLengths[1] && $changes1->has($x) === false && $changes2->has($y) === false) {
                ++$x;
                ++$y;
            }

            if ($x > $startX) {
                $builder->addEqual($x - $startX);
            }
            $dx = 0;
            $dy = 0;
            while ($x < $this->originalLengths[0] && $changes1->has($x)) {
                ++$dx;
                ++$x;
            }
            while ($y < $this->originalLengths[1] && $changes2->has($y)) {
                ++$dy;
                ++$y;
            }
            if ($dx !== 0 || $dy !== 0) {
                $builder->addChange($dx, $dy);
            }
        }

        if ($x !== $this->originalLengths[0] || $y !== $this->originalLengths[1]) {
            $builder->addChange($this->originalLengths[0] - $x, $this->originalLengths[1] - $y);
        }
    }

    /**
     * @param int[]    $needed
     * @param int[]    $toDiscard
     * @param int<0,1> $arrayIndex
     *
     * @return int[]
     */
    private function discard(array $needed, array $toDiscard, int $arrayIndex): array
    {
        $discarded = array_intersect($toDiscard, $needed);

        $this->oldIndices[$arrayIndex]       = array_keys($discarded);
        $this->originalLengths[$arrayIndex]  = count($toDiscard);
        $this->discardedLengths[$arrayIndex] = count($discarded);

        return array_values($discarded);
    }

    /**
     * @param int[] $indexes
     */
    private static function getOriginal(array $indexes, int $i): int
    {
        return $indexes[$i];
    }

    /**
     * @param int[] $indexes
     */
    private static function increment(array $indexes, int $i, BitSet $set, int $length): int
    {
        if ($i + 1 < count($indexes)) {
            $set->set($indexes[$i] + 1, $indexes[$i + 1]);
        } else {
            $set->set($indexes[$i] + 1, $length);
        }

        return $i + 1;
    }
}
