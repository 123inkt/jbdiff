<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Entity\Character\CharSequenceInterface;
use DR\JBDiff\Entity\Chunk\InlineChunk;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\LineFragmentSplitter\PendingChunk;
use DR\JBDiff\Entity\LineFragmentSplitter\WordBlock;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\Util\Strings;
use function count;

/**
 * Given matchings on words, split initial line block into 'logically different' line blocks
 */
class LineFragmentSplitter
{
    private int           $last1        = -1;
    private int           $last2        = -1;
    private ?PendingChunk $pendingChunk = null;
    /** @var WordBlock[] */
    private array $result = [];

    /**
     * @param InlineChunk[] $words1
     * @param InlineChunk[] $words2
     */
    public function __construct(
        private readonly CharSequenceInterface $text1,
        private readonly CharSequenceInterface $text2,
        private readonly array $words1,
        private readonly array $words2,
        private readonly FairDiffIterableInterface $iterable
    ) {
    }

    /**
     * @return WordBlock[]
     */
    public function run(): array
    {
        $hasEqualWords = false;
        foreach ($this->iterable->unchanged() as $range) {
            $count = $range->end1 - $range->start1;
            for ($i = 0; $i < $count; $i++) {
                $index1 = $range->start1 + $i;
                $index2 = $range->start2 + $i;

                if (self::isNewline($this->words1, $index1) && self::isNewline($this->words2, $index2)) {
                    // split by matched newlines
                    $this->addLineChunk($index1, $index2, $hasEqualWords);
                } else {
                    if (self::isFirstInLine($this->words1, $index1) && self::isFirstInLine($this->words2, $index2)) {
                        // split by matched first word in line
                        $this->addLineChunk($index1 - 1, $index2 - 1, $hasEqualWords);
                    }
                    $hasEqualWords = true;
                }
            }
        }
        $this->addLineChunk(count($this->words1), count($this->words2), $hasEqualWords);
        if ($this->pendingChunk !== null) {
            $this->result[] = $this->pendingChunk->block;
        }

        return $this->result;
    }

    private function addLineChunk(int $end1, int $end2, bool $hasEqualWords): void
    {
        if ($this->last1 > $end1 || $this->last2 > $end2) {
            return;
        }

        $chunk = $this->createChunk($this->last1, $this->last2, $end1, $end2, $hasEqualWords);
        if ($chunk->block->offsets->isEmpty()) {
            return;
        }

        if ($this->pendingChunk !== null && self::shouldMergeChunks($this->pendingChunk, $chunk)) {
            $this->pendingChunk = self::mergeChunks($this->pendingChunk, $chunk);
        } else {
            if ($this->pendingChunk !== null) {
                $this->result[] = $this->pendingChunk->block;
            }
            $this->pendingChunk = $chunk;
        }

        $this->last1 = $end1;
        $this->last2 = $end2;
    }

    private function createChunk(int $start1, int $start2, int $end1, int $end2, bool $hasEqualWords): PendingChunk
    {
        $startOffset1 = self::getOffset($this->words1, $this->text1, $start1);
        $startOffset2 = self::getOffset($this->words2, $this->text2, $start2);
        $endOffset1   = self::getOffset($this->words1, $this->text1, $end1);
        $endOffset2   = self::getOffset($this->words2, $this->text2, $end2);

        $start1 = max(0, $start1 + 1);
        $start2 = max(0, $start2 + 1);
        $end1   = min($end1 + 1, count($this->words1));
        $end2   = min($end2 + 1, count($this->words2));

        $block = new WordBlock(new Range($start1, $end1, $start2, $end2), new Range($startOffset1, $endOffset1, $startOffset2, $endOffset2));

        return new PendingChunk($block, $hasEqualWords, $this->hasWordsInside($block), $this->isEqualsIgnoreWhitespace($block));
    }

    private static function shouldMergeChunks(PendingChunk $chunk1, PendingChunk $chunk2): bool
    {
        if ($chunk1->hasEqualWords === false && $chunk2->hasEqualWords === false) {
            // combine lines, that matched only by '\n'
            return true;
        }

        if ($chunk1->isEqualIgnoreWhitespaces && $chunk2->isEqualIgnoreWhitespaces) {
            // combine whitespace-only changed lines
            return true;
        }

        if ($chunk1->hasWordsInside === false || $chunk2->hasWordsInside === false) {
            // squash block without words in it
            return true;
        }

        return false;
    }

    private static function mergeChunks(PendingChunk $chunk1, PendingChunk $chunk2): PendingChunk
    {
        $block1   = $chunk1->block;
        $block2   = $chunk2->block;
        $newBlock = new WordBlock(
            new Range($block1->words->start1, $block2->words->end1, $block1->words->start2, $block2->words->end2),
            new Range($block1->offsets->start1, $block2->offsets->end1, $block1->offsets->start2, $block2->offsets->end2)
        );

        return new PendingChunk(
            $newBlock,
            $chunk1->hasEqualWords || $chunk2->hasEqualWords,
            $chunk1->hasWordsInside || $chunk2->hasWordsInside,
            $chunk1->isEqualIgnoreWhitespaces || $chunk2->isEqualIgnoreWhitespaces
        );
    }

    private function isEqualsIgnoreWhitespace(WordBlock $block): bool
    {
        return Strings::equalsIgnoreWhitespaces(
            $this->text1,
            $this->text2,
            $block->offsets->start1,
            $block->offsets->end1,
            $block->offsets->start2,
            $block->offsets->end2
        );
    }

    private function hasWordsInside(WordBlock $block): bool
    {
        for ($i = $block->words->start1; $i < $block->words->end1; $i++) {
            if ($this->words1[$i] instanceof NewLineChunk === false) {
                return true;
            }
        }
        for ($i = $block->words->start2; $i < $block->words->end2; $i++) {
            if ($this->words2[$i] instanceof NewLineChunk === false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param InlineChunk[] $words
     */
    private static function getOffset(array $words, CharSequenceInterface $text, int $index): int
    {
        if ($index === -1) {
            return 0;
        }
        if ($index === count($words)) {
            return $text->length();
        }

        $chunk = $words[$index];
        assert($chunk instanceof NewLineChunk);

        return $chunk->getOffset2();
    }

    /**
     * @param InlineChunk[] $words
     */
    private static function isNewline(array $words, int $index): bool
    {
        return ($words[$index] ?? null) instanceof NewLineChunk;
    }

    /**
     * @param InlineChunk[] $words
     */
    private static function isFirstInLine(array $words, int $index): bool
    {
        if ($index === 0) {
            return true;
        }

        return ($words[$index - 1] ?? null) instanceof NewLineChunk;
    }
}
