<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\AdjustmentPunctuationMatcher;

use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Entity\Change\Change;

class ChangeBuilder extends AbstractChangeBuilder
{
    private ?Change $firstChange = null;
    private ?Change $lastChange  = null;

    protected function addChange(int $start1, int $start2, int $end1, int $end2): void
    {
        $change = new Change($start1, $start2, $end1 - $start1, $end2 - $start2);
        if ($this->lastChange !== null) {
            $this->lastChange->link = $change;
        } else {
            $this->firstChange = $change;
        }
        $this->lastChange = $change;
    }

    public function finish(): DiffIterableInterface
    {
        $this->doFinish();

        return DiffIterableUtil::create($this->firstChange, $this->length1, $this->length2);
    }
}
