<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity\Change;

class NullChange extends Change
{
    public function __construct()
    {
        parent::__construct(0, 0, 0, 0);
    }

    public function isNull(): bool
    {
        return true;
    }
}
