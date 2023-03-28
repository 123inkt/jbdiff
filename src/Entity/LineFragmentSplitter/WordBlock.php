<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity\LineFragmentSplitter;

use DR\JBDiff\Entity\Range;

class WordBlock
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly Range $words, public readonly Range $offsets)
    {
    }
}
