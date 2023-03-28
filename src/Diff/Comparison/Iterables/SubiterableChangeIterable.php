<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison\Iterables;

use DR\JBDiff\Entity\Range;

class SubiterableChangeIterable implements ChangeIterableInterface
{
    /** @var CursorIteratorInterface<Range> */
    private readonly CursorIteratorInterface $iterator;

    private ?Range $last = null;

    public function __construct(
        DiffIterableInterface $iterable,
        private readonly int $start1,
        private readonly int $end1,
        private readonly int $start2,
        private readonly int $end2
    ) {
        $this->iterator = $iterable->changes();
        $this->next();
    }

    public function valid(): bool
    {
        return $this->last !== null;
    }

    public function next(): void
    {
        $this->last = null;
        foreach ($this->iterator as $range) {
            if ($range->end1 < $this->start1 || $range->end2 < $this->start2) {
                continue;
            }
            if ($range->start1 > $this->end1 || $range->start2 > $this->end2) {
                break;
            }

            $newRange = new Range(
                max($this->start1, $range->start1) - $this->start1,
                min($this->end1, $range->end1) - $this->start1,
                max($this->start2, $range->start2) - $this->start2,
                min($this->end2, $range->end2) - $this->start2
            );
            if ($newRange->isEmpty()) {
                continue;
            }
            $this->last = $newRange;
            break;
        }
    }

    public function getStart1(): int
    {
        assert($this->last !== null);

        return $this->last->start1;
    }

    public function getStart2(): int
    {
        assert($this->last !== null);

        return $this->last->start2;
    }

    public function getEnd1(): int
    {
        assert($this->last !== null);

        return $this->last->end1;
    }

    public function getEnd2(): int
    {
        assert($this->last !== null);

        return $this->last->end2;
    }
}
