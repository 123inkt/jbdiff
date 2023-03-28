<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity;

use Stringable;

class Range implements EquatableInterface, Stringable
{
    public function __construct(public readonly int $start1, public readonly int $end1, public readonly int $start2, public readonly int $end2)
    {
    }

    public function isEmpty(): bool
    {
        return $this->start1 === $this->end1 && $this->start2 === $this->end2;
    }

    public function equals(EquatableInterface $object): bool
    {
        if ($object instanceof self === false) {
            return false;
        }

        if ($this === $object) {
            return true;
        }

        return $this->start1 === $object->start1
            && $this->end1 === $object->end1
            && $this->start2 === $object->start2
            && $this->end2 === $object->end2;
    }

    public function __toString(): string
    {
        return "[" . $this->start1 . ", " . $this->end1 . "] - [" . $this->start2 . ", " . $this->end2 . "]";
    }
}
