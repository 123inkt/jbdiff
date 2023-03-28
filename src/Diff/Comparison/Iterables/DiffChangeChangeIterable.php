<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison\Iterables;

use DR\JBDiff\Entity\Change\Change;

class DiffChangeChangeIterable implements ChangeIterableInterface
{
    public function __construct(private ?Change $change)
    {
    }

    public function valid(): bool
    {
        return $this->change !== null;
    }

    public function next(): void
    {
        assert($this->change !== null);
        $this->change = $this->change->link;
    }

    public function getStart1(): int
    {
        assert($this->change !== null);
        return $this->change->line0;
    }

    public function getStart2(): int
    {
        assert($this->change !== null);
        return $this->change->line1;
    }

    public function getEnd1(): int
    {
        assert($this->change !== null);
        return $this->change->line0 + $this->change->deleted;
    }

    public function getEnd2(): int
    {
        assert($this->change !== null);
        return $this->change->line1 + $this->change->inserted;
    }
}
