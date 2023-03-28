<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff;

use DR\JBDiff\ComparisonPolicy;
use DR\JBDiff\Diff\Comparison\DefaultCorrector;
use DR\JBDiff\Diff\Comparison\IgnoreSpacesCorrector;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\SubiterableDiffIterable;
use DR\JBDiff\Diff\Comparison\LineFragmentSplitter;
use DR\JBDiff\Diff\Comparison\TrimSpacesCorrector;
use DR\JBDiff\Diff\Comparison\WordChunkOptimizer;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Character\CharSequenceInterface as CharSequence;
use DR\JBDiff\Entity\Character\MergingCharSequence;
use DR\JBDiff\Entity\Chunk\InlineChunk;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Entity\LineFragmentSplitter\DiffFragment;
use DR\JBDiff\Entity\LineFragmentSplitter\DiffFragmentInterface;
use DR\JBDiff\Entity\LineFragmentSplitter\LineBlock;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\Util\Character;
use IntlChar;
use InvalidArgumentException;

class ByWordRt
{
    private const NEW_LINE = 10;

    /**
     * @return LineBlock[]
     * @throws DiffToBigException
     */
    public static function compareAndSplit(CharSequence $text1, CharSequence $text2, ComparisonPolicy $policy): array
    {
        $words1 = self::getInlineChunks($text1);
        $words2 = self::getInlineChunks($text2);

        $wordChanges = DiffIterableUtil::diff($words1, $words2);
        $wordChanges = (new WordChunkOptimizer($words1, $words2, $text1, $text2, $wordChanges))->build();

        $wordBlocks = (new LineFragmentSplitter($text1, $text2, $words1, $words2, $wordChanges))->run();
        $lineBlocks = [];

        foreach ($wordBlocks as $block) {
            $offsets = $block->offsets;
            $words   = $block->words;

            $subText1 = $text1->subSequence($offsets->start1, $offsets->end1);
            $subText2 = $text2->subSequence($offsets->start2, $offsets->end2);

            $subWords1 = array_slice($words1, $words->start1, $words->end1 - $words->start1);
            $subWords2 = array_slice($words2, $words->start2, $words->end2 - $words->start2);

            $subiterable = DiffIterableUtil::fair(
                new SubiterableDiffIterable($wordChanges, $words->start1, $words->end1, $words->start2, $words->end2)
            );

            $delimitersIterable = DiffIterableUtil::matchAdjustmentDelimiters(
                $subText1,
                $subText2,
                $subWords1,
                $subWords2,
                $subiterable,
                $offsets->start1,
                $offsets->start2
            );

            $iterable  = self::matchAdjustmentWhitespaces($subText1, $subText2, $delimitersIterable, $policy);
            $fragments = self::convertIntoDiffFragments($iterable);

            $lineBlocks[] = new LineBlock($fragments, $offsets, self::countNewlines($subWords1), self::countNewlines($subWords2));
        }

        return $lineBlocks;
    }

    /**
     * @return InlineChunk[]
     */
    public static function getInlineChunks(CharSequence $text): array
    {
        $wordStart = -1;
        $chunks    = [];

        foreach ($text->chars() as $offset => $char) {
            $ch         = IntlChar::ord($char);
            $isAlpha    = Character::isAlpha($ch);
            $isWordPart = $isAlpha && Character::isContinuousScript($ch) === false;

            if ($isWordPart) {
                if ($wordStart === -1) {
                    $wordStart = $offset;
                }
            } else {
                if ($wordStart !== -1) {
                    $chunks[]  = new WordChunk($text, $wordStart, $offset);
                    $wordStart = -1;
                }

                if ($isAlpha) { // continuous script
                    $chunks[] = new WordChunk($text, $offset, $offset + 1);
                } elseif ($ch === self::NEW_LINE) {
                    $chunks[] = new NewlineChunk($offset);
                }
            }
        }

        if ($wordStart !== -1) {
            $chunks[] = new WordChunk($text, $wordStart, $text->length());
        }

        return $chunks;
    }

    /**
     * Compare one char sequence with two others (as if they were single sequence)
     * Return two DiffIterable: (0, len1) - (0, len21) and (0, len1) - (0, len22)
     * @return array{0: FairDiffIterableInterface, 1: FairDiffIterableInterface}
     * @throws DiffToBigException
     */
    public static function comparePunctuation2Side(CharSequence $text1, CharSequence $text21, CharSequence $text22): array
    {
        $text2   = new MergingCharSequence($text21, $text22);
        $changes = ByCharRt::comparePunctuation($text1, $text2);

        [$first, $second] = self::splitIterable2Side($changes, $text21->length());

        $iterable1 = DiffIterableUtil::fair(DiffIterableUtil::createUnchanged($first, $text1->length(), $text21->length()));
        $iterable2 = DiffIterableUtil::fair(DiffIterableUtil::createUnchanged($second, $text1->length(), $text22->length()));

        return [$iterable1, $iterable2];
    }

    public static function matchAdjustmentWhitespaces(
        CharSequence $text1,
        CharSequence $text2,
        FairDiffIterableInterface $iterable,
        ComparisonPolicy $policy
    ): DiffIterableInterface {
        switch ($policy) {
            case ComparisonPolicy::DEFAULT:
                return (new DefaultCorrector($iterable, $text1, $text2))->build();
            case ComparisonPolicy::TRIM_WHITESPACES:
                return (new TrimSpacesCorrector((new DefaultCorrector($iterable, $text1, $text2))->build(), $text1, $text2))->build();
            case ComparisonPolicy::IGNORE_WHITESPACES:
                return (new IgnoreSpacesCorrector($iterable, $text1, $text2))->build();
            // @codeCoverageIgnoreStart
            default:
                throw new InvalidArgumentException('invalid policy');
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @return DiffFragmentInterface[]
     */
    public static function convertIntoDiffFragments(DiffIterableInterface $changes): array
    {
        $fragments = [];
        foreach ($changes->changes() as $range) {
            $fragments[] = new DiffFragment($range->start1, $range->end1, $range->start2, $range->end2);
        }

        return $fragments;
    }

    /**
     * @param InlineChunk[] $words
     */
    public static function countNewlines(array $words): int
    {
        $count = 0;
        foreach ($words as $word) {
            if ($word instanceof NewLineChunk) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return array{0: Range[], 1: Range[]}
     */
    private static function splitIterable2Side(FairDiffIterableInterface $changes, int $offset): array
    {
        $ranges1 = [];
        $ranges2 = [];

        foreach ($changes->unchanged() as $ch) {
            if ($ch->end2 <= $offset) {
                $ranges1[] = new Range($ch->start1, $ch->end1, $ch->start2, $ch->end2);
            } elseif ($ch->start2 >= $offset) {
                $ranges2[] = new Range($ch->start1, $ch->end1, $ch->start2 - $offset, $ch->end2 - $offset);
            } else {
                $len2 = $offset - $ch->start2;

                $ranges1[] = new Range($ch->start1, $ch->start1 + $len2, $ch->start2, $offset);
                $ranges2[] = new Range($ch->start1 + $len2, $ch->end1, 0, $ch->end2 - $offset);
            }
        }

        return [$ranges1, $ranges2];
    }
}
