<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Util\LCS;

use DR\JBDiff\Util\Arrays;
use function count;

class UniqueLCS
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
    ) {
        $this->count1 = $count1 ?? count($first);
        $this->count2 = $count2 ?? count($second);
    }

    /**
     * @return int[][]|null
     */
    public function execute(): ?array
    {
        /** @var array<int, int> $map */
        $map = [];
        /** @var array<int, int> $match */
        $match = [];

        for ($i = 0; $i < $this->count1; $i++) {
            $index = $this->start1 + $i;
            $val   = $map[$this->first[$index] ?? 0] ?? 0;

            if ($val === -1) {
                continue;
            }
            if ($val === 0) {
                $map[$this->first[$index] ?? 0] = $i + 1;
            } else {
                $map[$this->first[$index] ?? 0] = -1;
            }
        }

        $count = 0;
        for ($i = 0; $i < $this->count2; $i++) {
            $index = $this->start2 + $i;
            $val   = $map[$this->second[$index] ?? 0] ?? 0;

            if ($val === 0 || $val === -1) {
                continue;
            }
            if (($match[$val - 1] ?? 0) === 0) {
                $match[$val - 1] = $i + 1;
                ++$count;
            } else {
                // @codeCoverageIgnoreStart
                // difficult to hit with coverage
                $match[$val - 1]            = 0;
                $map[$this->second[$index]] = -1;
                --$count;
                // @codeCoverageIgnoreEnd
            }
        }

        if ($count === 0) {
            return null;
        }

        // Largest increasing subsequence on unique elements
        $sequence    = [];
        $lastElement = [];
        $predecessor = [];

        $length = 0;
        for ($i = 0; $i < $this->count1; $i++) {
            if (($match[$i] ?? 0) === 0) {
                continue;
            }

            $j = self::binarySearch($sequence, $match[$i] ?? 0, $length);
            if ($j === $length || ($match[$i] ?? 0) < ($sequence[$j] ?? 0)) {
                $sequence[$j]    = $match[$i] ?? 0;
                $lastElement[$j] = $i;
                $predecessor[$i] = $j > 0 ? ($lastElement[$j - 1] ?? 0) : -1;
                if ($j === $length) {
                    ++$length;
                }
            }
        }

        /** @var int[][] $ret */
        $ret = [];

        $i    = $length - 1;
        $curr = $lastElement[$length - 1] ?? 0;
        while ($curr !== -1) {
            $ret[0][$i] = $curr;
            $ret[1][$i] = ($match[$curr] ?? 0) - 1;
            --$i;
            $curr = $predecessor[$curr];
        }

        return $ret;
    }

    /**
     * find max i: a[i] < val
     * return i + 1
     * assert a[i] != val
     *
     * @param int[] $sequence
     */
    private static function binarySearch(array $sequence, int $val, int $length): int
    {
        $i = Arrays::binarySearch($sequence, 0, $length, $val);
        assert($i < 0);

        return -$i - 1;
    }
}
