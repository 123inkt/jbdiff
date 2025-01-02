<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\Entity\Side;
use DR\JBDiff\Util\TrimUtil;

use function count;

/**
 * @template T
 */
abstract class AbstractChunkOptimizer
{
    /** @var Range[] */
    private array $ranges = [];

    /**
     * @param list<T>                   $data1
     * @param list<T>                   $data2
     * @param FairDiffIterableInterface $iterable
     */
    public function __construct(
        protected readonly array $data1,
        protected readonly array $data2,
        private readonly FairDiffIterableInterface $iterable
    ) {
    }

    public function build(): FairDiffIterableInterface
    {
        foreach ($this->iterable->unchanged() as $range) {
            $this->ranges[] = $range;
            $this->processLastRanges();
        }

        return DiffIterableUtil::fair(DiffIterableUtil::createUnchanged($this->ranges, count($this->data1), count($this->data2)));
    }

    private function processLastRanges(): void
    {
        if (count($this->ranges) < 2) {
            return; // nothing to do
        }

        $range1 = $this->ranges[count($this->ranges) - 2];
        $range2 = $this->ranges[count($this->ranges) - 1];
        if ($range1->end1 !== $range2->start1 && $range1->end2 !== $range2->start2) {
            // if changes do not touch, and we still can perform one of these optimisations,
            // it means that given DiffIterable is not LCS (because we can build a smaller one). This should not happen.
            return;
        }

        $count1 = $range1->end1 - $range1->start1;
        $count2 = $range2->end1 - $range2->start1;

        $equalForward  = TrimUtil::expandForward(
            $this->data1,
            $this->data2,
            $range1->end1,
            $range1->end2,
            $range1->end1 + $count2,
            $range1->end2 + $count2
        );
        $equalBackward = TrimUtil::expandBackward(
            $this->data1,
            $this->data2,
            $range2->start1 - $count1,
            $range2->start2 - $count1,
            $range2->start1,
            $range2->start2
        );

        // nothing to do
        if ($equalForward === 0 && $equalBackward === 0) {
            return;
        }

        // merge chunks left [A]B[B] -> [AB]B
        if ($equalForward === $count2) {
            array_pop($this->ranges);
            array_pop($this->ranges);
            $this->ranges[] = new Range($range1->start1, $range1->end1 + $count2, $range1->start2, $range1->end2 + $count2);
            $this->processLastRanges();

            return;
        }

        // merge chunks right [A]A[B] -> A[AB]
        if ($equalForward === $count1) {
            array_pop($this->ranges);
            array_pop($this->ranges);
            $this->ranges[] = new Range($range2->start1 - $count1, $range2->end1, $range2->start2 - $count1, $range2->end2);
            $this->processLastRanges();

            return;
        }

        $touchSide = Side::fromLeft($range1->end1 === $range2->start1);
        $shift     = $this->getShift($touchSide, $equalForward, $equalBackward, $range1, $range2);
        if ($shift !== 0) {
            array_pop($this->ranges);
            array_pop($this->ranges);
            $this->ranges[] = new Range($range1->start1, $range1->end1 + $shift, $range1->start2, $range1->end2 + $shift);
            $this->ranges[] = new Range($range2->start1 + $shift, $range2->end1, $range2->start2 + $shift, $range2->end2);
        }
    }

    /**
     * 0  - do nothing
     * >0 - shift forward
     * <0 - shift backward
     */
    abstract protected function getShift(Side $touchSide, int $equalForward, int $equalBackward, Range $range1, Range $range2): int;
}
