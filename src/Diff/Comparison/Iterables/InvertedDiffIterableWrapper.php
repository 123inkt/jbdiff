<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison\Iterables;

class InvertedDiffIterableWrapper implements DiffIterableInterface
{
    public function __construct(private readonly DiffIterableInterface $iterable)
    {
    }

    public function getLength1(): int
    {
        return $this->iterable->getLength1();
    }

    public function getLength2(): int
    {
        return $this->iterable->getLength2();
    }

    public function changes(): CursorIteratorInterface
    {
        return $this->iterable->unchanged();
    }

    public function unchanged(): CursorIteratorInterface
    {
        return $this->iterable->changes();
    }
}
