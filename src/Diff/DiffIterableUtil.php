<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff;

use DR\JBDiff\Diff\AdjustmentPunctuationMatcher\AdjustmentPunctuationMatcher;
use DR\JBDiff\Diff\Comparison\Iterables\DiffChangeDiffIterable;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableWrapper;
use DR\JBDiff\Diff\Comparison\Iterables\InvertedDiffIterableWrapper;
use DR\JBDiff\Diff\Comparison\Iterables\RangesDiffIterable;
use DR\JBDiff\Diff\Util\Diff;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Change\Change;
use DR\JBDiff\Entity\Character\CharSequenceInterface;
use DR\JBDiff\Entity\Chunk\InlineChunk;
use DR\JBDiff\Entity\EquatableInterface;
use DR\JBDiff\Entity\Range;

class DiffIterableUtil
{
    /**
     * @param int[]|EquatableInterface[] $objects1
     * @param int[]|EquatableInterface[] $objects2
     *
     * @throws DiffToBigException
     */
    public static function diff(array $objects1, array $objects2): FairDiffIterableInterface
    {
        $change = (new Diff())->buildChanges($objects1, $objects2);

        return self::fair(self::create($change, count($objects1), count($objects2)));
    }

    public static function create(?Change $change, int $length1, int $length2): DiffIterableInterface
    {
        return new DiffChangeDiffIterable($change, $length1, $length2);
    }

    /**
     * @param Range[] $ranges
     */
    public static function createFromRanges(array $ranges, int $length1, int $length2): DiffIterableInterface
    {
        return new RangesDiffIterable($ranges, $length1, $length2);
    }

    /**
     * @param Range[] $ranges
     */
    public static function createUnchanged(array $ranges, int $length1, int $length2): DiffIterableInterface
    {
        return new InvertedDiffIterableWrapper(new RangesDiffIterable($ranges, $length1, $length2));
    }

    public static function fair(DiffIterableInterface $iterable): FairDiffIterableInterface
    {
        if ($iterable instanceof FairDiffIterableInterface) {
            return $iterable;
        }

        return new FairDiffIterableWrapper($iterable);
    }

    /**
     * @param InlineChunk[] $words1
     * @param InlineChunk[] $words2
     *
     * @throws DiffToBigException
     */
    public static function matchAdjustmentDelimiters(
        CharSequenceInterface $text1,
        CharSequenceInterface $text2,
        array $words1,
        array $words2,
        FairDiffIterableInterface $changes,
        int $startShift1,
        int $startShift2
    ): FairDiffIterableInterface {
        return (new AdjustmentPunctuationMatcher($text1, $text2, $words1, $words2, $startShift1, $startShift2, $changes))->build();
    }
}
