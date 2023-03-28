<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\AdjustmentPunctuationMatcher;

use DR\JBDiff\Diff\ByCharRt;
use DR\JBDiff\Diff\ByWordRt;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Character\CharSequenceInterface;
use DR\JBDiff\Entity\Chunk\InlineChunk;
use LogicException;
use function count;

/**
 * sample: "[ X { A ! B } Y ]" "( X ... Y )" will lead to comparison of 3 groups of separators
 *         "["  vs "(",
 *         "{" + "}" vs "..."
 *         "]"  vs ")"
 */
class AdjustmentPunctuationMatcher
{
    private readonly int           $len1;
    private readonly int           $len2;
    private readonly ChangeBuilder $builder;

    private int $lastStart1 = -1;
    private int $lastStart2 = -1;
    private int $lastEnd1   = -1;
    private int $lastEnd2   = -1;

    /**
     * @param InlineChunk[] $words1
     * @param InlineChunk[] $words2
     */
    public function __construct(
        private readonly CharSequenceInterface $text1,
        private readonly CharSequenceInterface $text2,
        private readonly array $words1,
        private readonly array $words2,
        private readonly int $startShift1,
        private readonly int $startShift2,
        private readonly FairDiffIterableInterface $changes
    ) {
        $this->len1    = $this->text1->length();
        $this->len2    = $this->text2->length();
        $this->builder = new ChangeBuilder($this->len1, $this->len2);
    }

    /**
     * @throws DiffToBigException
     */
    public function build(): FairDiffIterableInterface
    {
        $this->execute();

        return DiffIterableUtil::fair($this->builder->finish());
    }

    /**
     * @throws DiffToBigException
     */
    private function execute(): void
    {
        $this->clearLastRange();
        $this->matchForward(-1, -1);

        foreach ($this->changes->unchanged() as $ch) {
            $count = $ch->end1 - $ch->start1;
            for ($i = 0; $i < $count; $i++) {
                $index1 = $ch->start1 + $i;
                $index2 = $ch->start2 + $i;

                $start1 = $this->getStartOffset1($index1);
                $start2 = $this->getStartOffset2($index2);
                $end1   = $this->getEndOffset1($index1);
                $end2   = $this->getEndOffset2($index2);

                $this->matchBackward($index1, $index2);

                $this->builder->markEqual($start1, $start2, $end1, $end2);

                $this->matchForward($index1, $index2);
            }
        }

        $this->matchBackward(count($this->words1), count($this->words2));
    }

    private function clearLastRange(): void
    {
        $this->lastStart1 = -1;
        $this->lastStart2 = -1;
        $this->lastEnd1   = -1;
        $this->lastEnd2   = -1;
    }

    /**
     * @throws DiffToBigException
     */
    private function matchBackward(int $index1, int $index2): void
    {
        $start1 = $index1 === 0 ? 0 : $this->getEndOffset1($index1 - 1);
        $start2 = $index2 === 0 ? 0 : $this->getEndOffset2($index2 - 1);
        $end1   = $index1 === count($this->words1) ? $this->len1 : $this->getStartOffset1($index1);
        $end2   = $index2 === count($this->words2) ? $this->len2 : $this->getStartOffset2($index2);

        $this->matchBackwardRange($start1, $start2, $end1, $end2);
        $this->clearLastRange();
    }

    /**
     * @throws DiffToBigException
     */
    private function matchBackwardRange(int $start1, int $start2, int $end1, int $end2): void
    {
        assert($this->lastStart1 !== -1 && $this->lastStart2 !== -1 && $this->lastEnd1 !== -1 && $this->lastEnd2 !== -1);

        if ($this->lastStart1 === $start1 && $this->lastStart2 === $start2) {
            // pair of adjustment matched words, match gap between ("A B" - "A B")
            assert($this->lastEnd1 === $end1 && $this->lastEnd2 === $end2);
            $this->matchRange($start1, $start2, $end1, $end2);

            return;
        }

        if ($this->lastStart1 < $start1 && $this->lastStart2 < $start2) {
            // pair of matched words, with few unmatched ones between ("A X B" - "A Y B")
            assert($this->lastEnd1 <= $start1 && $this->lastEnd2 <= $start2);

            $this->matchRange($this->lastStart1, $this->lastStart2, $this->lastEnd1, $this->lastEnd2);
            $this->matchRange($start1, $start2, $end1, $end2);

            return;
        }

        // one side adjustment, and other has non-matched words between ("A B" - "A Y B")
        $this->matchComplexRange($this->lastStart1, $this->lastStart2, $this->lastEnd1, $this->lastEnd2, $start1, $start2, $end1, $end2);
    }

    private function matchForward(int $index1, int $index2): void
    {
        $start1 = $index1 === -1 ? 0 : $this->getEndOffset1($index1);
        $start2 = $index2 === -1 ? 0 : $this->getEndOffset2($index2);
        $end1   = $index1 + 1 === count($this->words1) ? $this->len1 : $this->getStartOffset1($index1 + 1);
        $end2   = $index2 + 1 === count($this->words2) ? $this->len2 : $this->getStartOffset2($index2 + 1);

        $this->matchForwardRange($start1, $start2, $end1, $end2);
    }

    private function matchForwardRange(int $start1, int $start2, int $end1, int $end2): void
    {
        assert($this->lastStart1 === -1 && $this->lastStart2 === -1 && $this->lastEnd1 === -1 && $this->lastEnd2 === -1);

        $this->lastStart1 = $start1;
        $this->lastStart2 = $start2;
        $this->lastEnd1   = $end1;
        $this->lastEnd2   = $end2;
    }

    /**
     * @throws DiffToBigException
     */
    private function matchRange(int $start1, int $start2, int $end1, int $end2): void
    {
        if ($start1 === $end1 && $start2 === $end2) {
            return;
        }

        $sequence1 = $this->text1->subSequence($start1, $end1);
        $sequence2 = $this->text2->subSequence($start2, $end2);

        $changes = ByCharRt::comparePunctuation($sequence1, $sequence2);

        foreach ($changes->unchanged() as $ch) {
            $this->builder->markEqual($start1 + $ch->start1, $start2 + $ch->start2, $start1 + $ch->end1, $start2 + $ch->end2);
        }
    }

    /**
     * @throws DiffToBigException
     */
    private function matchComplexRange(int $start11, int $start12, int $end11, int $end12, int $start21, int $start22, int $end21, int $end22): void
    {
        if ($start11 === $start21 && $end11 === $end21) {
            $this->matchComplexRangeLeft($start11, $end11, $start12, $end12, $start22, $end22);
        } elseif ($start12 === $start22 && $end12 === $end22) {
            $this->matchComplexRangeRight($start12, $end12, $start11, $end11, $start21, $end21);
            // @codeCoverageIgnoreStart
        } else {
            throw new LogicException('Unable to calculate match complex range');
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @throws DiffToBigException
     */
    private function matchComplexRangeLeft(int $start1, int $end1, int $start12, int $end12, int $start22, int $end22): void
    {
        $sequence1  = $this->text1->subSequence($start1, $end1);
        $sequence21 = $this->text2->subSequence($start12, $end12);
        $sequence22 = $this->text2->subSequence($start22, $end22);

        [$first, $second] = ByWordRt::comparePunctuation2Side($sequence1, $sequence21, $sequence22);
        foreach ($first->unchanged() as $ch) {
            $this->builder->markEqual($start1 + $ch->start1, $start12 + $ch->start2, $start1 + $ch->end1, $start12 + $ch->end2);
        }
        foreach ($second->unchanged() as $ch) {
            $this->builder->markEqual($start1 + $ch->start1, $start22 + $ch->start2, $start1 + $ch->end1, $start22 + $ch->end2);
        }
    }

    /**
     * @throws DiffToBigException
     */
    private function matchComplexRangeRight(int $start2, int $end2, int $start11, int $end11, int $start21, int $end21): void
    {
        $sequence11 = $this->text1->subSequence($start11, $end11);
        $sequence12 = $this->text1->subSequence($start21, $end21);
        $sequence2  = $this->text2->subSequence($start2, $end2);

        [$first, $second] = ByWordRt::comparePunctuation2Side($sequence2, $sequence11, $sequence12);

        // Mirrored ch.*1 and ch.*2 as we use "compare2Side" that works with 2 right side, while we have 2 left here
        foreach ($first->unchanged() as $ch) {
            $this->builder->markEqual($start11 + $ch->start2, $start2 + $ch->start1, $start11 + $ch->end2, $start2 + $ch->end1);
        }
        foreach ($second->unchanged() as $ch) {
            $this->builder->markEqual($start21 + $ch->start2, $start2 + $ch->start1, $start21 + $ch->end2, $start2 + $ch->end1);
        }
    }

    private function getStartOffset1(int $index): int
    {
        return $this->words1[$index]->getOffset1() - $this->startShift1;
    }

    private function getStartOffset2(int $index): int
    {
        return $this->words2[$index]->getOffset1() - $this->startShift2;
    }

    private function getEndOffset1(int $index): int
    {
        return $this->words1[$index]->getOffset2() - $this->startShift1;
    }

    private function getEndOffset2(int $index): int
    {
        return $this->words2[$index]->getOffset2() - $this->startShift2;
    }
}
