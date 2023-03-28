<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\AdjustmentPunctuationMatcher;

abstract class AbstractChangeBuilder
{
    private int $index1 = 0;
    private int $index2 = 0;

    public function __construct(protected readonly int $length1, protected readonly int $length2)
    {
    }

    public function getIndex1(): int
    {
        return $this->index1;
    }

    public function getIndex2(): int
    {
        return $this->index2;
    }

    public function markEqualCount(int $index1, int $index2, int $count = 1): void
    {
        $this->markEqual($index1, $index2, $index1 + $count, $index2 + $count);
    }

    public function markEqual(int $index1, int $index2, int $end1, int $end2): void
    {
        if ($index1 === $end1 && $index2 === $end2) {
            return;
        }

        assert($this->index1 <= $index1);
        assert($this->index2 <= $index2);
        assert($this->index1 <= $end1);
        assert($this->index2 <= $end2);

        if ($this->index1 !== $index1 || $this->index2 !== $index2) {
            $this->addChange($this->index1, $this->index2, $index1, $index2);
        }
        $this->index1 = $end1;
        $this->index2 = $end2;
    }

    protected function doFinish(): void
    {
        assert($this->index1 <= $this->length1);
        assert($this->index2 <= $this->length2);

        if ($this->length1 !== $this->index1 || $this->length2 !== $this->index2) {
            $this->addChange($this->index1, $this->index2, $this->length1, $this->length2);
            $this->index1 = $this->length1;
            $this->index2 = $this->length2;
        }
    }

    abstract protected function addChange(int $start1, int $start2, int $end1, int $end2): void;
}
