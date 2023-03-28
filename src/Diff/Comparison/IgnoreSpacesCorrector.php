<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Entity\Character\CharSequenceInterface;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\Util\TrimUtil;

class IgnoreSpacesCorrector
{
    /** @var Range[] */
    private array $changes = [];

    public function __construct(
        private readonly DiffIterableInterface $iterable,
        private readonly CharSequenceInterface $text1,
        private readonly CharSequenceInterface $text2
    ) {
    }

    public function build(): DiffIterableInterface
    {
        foreach ($this->iterable->changes() as $range) {
            $expanded = TrimUtil::expandWhitespaces($this->text1, $this->text2, $range);
            $trimmed = TrimUtil::trimWhitespacesRange($this->text1, $this->text2, $expanded);

            if ($trimmed->isEmpty() || TrimUtil::isEqualsIgnoreWhitespacesRange($this->text1, $this->text2, $trimmed)) {
                continue;
            }

            $this->changes[] = $trimmed;
        }

        return DiffIterableUtil::createFromRanges($this->changes, $this->text1->length(), $this->text2->length());
    }
}
