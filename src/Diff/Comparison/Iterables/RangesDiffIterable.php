<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison\Iterables;

use DR\JBDiff\Entity\Range;

class RangesDiffIterable extends AbstractChangeDiffIterable
{
    /**
     * @param Range[] $ranges
     */
    public function __construct(private readonly array $ranges, int $length1, int $length2)
    {
        parent::__construct($length1, $length2);
    }

    protected function createChangeIterable(): ChangeIterableInterface
    {
        return new RangesChangeIterable($this->ranges);
    }
}
