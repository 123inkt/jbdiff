<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison\Iterables;

use DR\JBDiff\Entity\Range;
use Traversable;

/**
 * @implements CursorIteratorInterface<Range>
 */
class ChangedIterator implements CursorIteratorInterface
{
    public function __construct(private readonly ChangeIterableInterface $iterable)
    {
    }

    public function hasNext(): bool
    {
        return $this->iterable->valid();
    }

    public function next(): Range
    {
        $range = new Range($this->iterable->getStart1(), $this->iterable->getEnd1(), $this->iterable->getStart2(), $this->iterable->getEnd2());
        $this->iterable->next();

        return $range;
    }

    /**
     * @return Traversable<Range>
     */
    public function getIterator(): Traversable
    {
        while ($this->hasNext()) {
            yield $this->next();
        }
    }
}
