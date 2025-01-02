<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Entity\Character\CharSequenceInterface;
use DR\JBDiff\Entity\Chunk\InlineChunk;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\Entity\Side;
use DR\JBDiff\Util\Character;

/**
 * 1. Minimise amount of chunks
 *      good: "AX[AB]" - "[AB]"
 *      bad: "[A]XA[B]" - "[A][B]"
 * 2. Minimise amount of modified 'sentences', where sentence is a sequence of words, that are not separated by whitespace
 *      good: "[AX] [AZ]" - "[AX] AY [AZ]"
 *      bad: "[AX A][Z]" - "[AX A]Y A[Z]"
 *      ex: "1.0.123 1.0.155" vs "1.0.123 1.0.134 1.0.155"
 * @extends AbstractChunkOptimizer<InlineChunk>
 */
class WordChunkOptimizer extends AbstractChunkOptimizer
{
    /**
     * @param list<InlineChunk> $words1
     * @param list<InlineChunk> $words2
     */
    public function __construct(
        array $words1,
        array $words2,
        private readonly CharSequenceInterface $text1,
        private readonly CharSequenceInterface $text2,
        FairDiffIterableInterface $changes
    ) {
        parent::__construct($words1, $words2, $changes);
    }

    protected function getShift(Side $touchSide, int $equalForward, int $equalBackward, Range $range1, Range $range2): int
    {
        $touchWords = $touchSide->select($this->data1, $this->data2);
        $touchText  = $touchSide->select($this->text1, $this->text2);
        $touchStart = $touchSide->select($range2->start1, $range2->start2);

        // check if chunks are already separated by whitespaces
        if (self::isSeparatedWithWhitespace($touchText, $touchWords[$touchStart - 1], $touchWords[$touchStart])) {
            return 0;
        }

        // shift chunks left [X]A Y[A ZA] -> [XA] YA [ZA]
        //                   [X][A ZA] -> [XA] [ZA]
        $leftShift = self::findSequenceEdgeShift($touchText, $touchWords, $touchStart, $equalForward, true);
        if ($leftShift > 0) {
            return $leftShift;
        }

        // shift chunks right [AX A]Y A[Z] -> [AX] AY [AZ]
        //                    [AX A][Z] -> [AX] [AZ]
        $rightShift = self::findSequenceEdgeShift($touchText, $touchWords, $touchStart - 1, $equalBackward, false);
        if ($rightShift > 0) {
            return -$rightShift;
        }

        // nothing to do
        return 0;
    }

    /**
     * @param InlineChunk[] $words
     */
    private static function findSequenceEdgeShift(CharSequenceInterface $text, array $words, int $offset, int $count, bool $leftToRight): int
    {
        for ($i = 0; $i < $count; $i++) {
            if ($leftToRight) {
                $word1 = $words[$offset + $i];
                $word2 = $words[$offset + $i + 1];
            } else {
                $word1 = $words[$offset - $i - 1];
                $word2 = $words[$offset - $i];
            }

            if (self::isSeparatedWithWhitespace($text, $word1, $word2)) {
                return $i + 1;
            }
        }

        return -1;
    }

    private static function isSeparatedWithWhitespace(CharSequenceInterface $text, InlineChunk $word1, InlineChunk $word2): bool
    {
        if ($word1 instanceof NewLineChunk || $word2 instanceof NewLineChunk) {
            return true;
        }

        $chars   = $text->chars();
        $offset1 = $word1->getOffset2();
        $offset2 = $word2->getOffset1();

        for ($i = $offset1; $i < $offset2; $i++) {
            if (Character::IS_WHITESPACE[$chars[$i]] ?? false) {
                return true;
            }
        }

        return false;
    }
}
