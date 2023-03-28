<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Util;

use DR\JBDiff\Entity\EquatableInterface;
use RuntimeException;

class Comparing
{
    public static function equal(mixed $arg1, mixed $arg2): bool
    {
        if ($arg1 === $arg2) {
            return true;
        }
        if ($arg1 === null || $arg2 === null) {
            return false;
        }

        // @codeCoverageIgnoreStart
        if (is_array($arg1) && is_array($arg2)) {
            // @see https://github.com/JetBrains/intellij-community/blob/master/platform/util-rt/src/com/intellij/openapi/util/Comparing.java
            throw new RuntimeException('Not implemented');
        }
        // @codeCoverageIgnoreEnd

        if ($arg1 instanceof EquatableInterface && $arg2 instanceof EquatableInterface) {
            return $arg1->equals($arg2);
        }

        return false;
    }
}
