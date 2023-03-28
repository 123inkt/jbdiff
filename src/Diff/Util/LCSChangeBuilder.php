<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff\Util;

use DR\JBDiff\Entity\Change\Change;

class LCSChangeBuilder implements LCSBuilderInterface
{
    private int     $myIndex1      = 0;
    private int     $myIndex2      = 0;
    private ?Change $myFirstChange = null;
    private ?Change $myLastChange  = null;

    public function __construct(int $startShift)
    {
        $this->skip($startShift, $startShift);
    }

    private function skip(int $first, int $second): void
    {
        $this->myIndex1 += $first;
        $this->myIndex2 += $second;
    }

    public function addChange(int $first, int $second): void
    {
        $change = new Change($this->myIndex1, $this->myIndex2, $first, $second, null);
        if ($this->myLastChange !== null) {
            $this->myLastChange->link = $change;
        } else {
            $this->myFirstChange = $change;
        }
        $this->myLastChange = $change;
        $this->skip($first, $second);
    }

    public function addEqual(int $length): void
    {
        $this->skip($length, $length);
    }

    public function getFirstChange(): ?Change
    {
        return $this->myFirstChange;
    }
}
