<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity;

use DR\JBDiff\Util\Comparing;
use Stringable;

/**
 * @template A
 * @template B
 */
class Pair implements EquatableInterface, Stringable
{
    /**
     * @param A $first
     * @param B $second
     */
    public function __construct(public readonly mixed $first, public readonly mixed $second)
    {
    }

    /**
     * @return A
     */
    final public function getFirst(): mixed
    {
        return $this->first;
    }

    /**
     * @return B
     */
    final public function getSecond(): mixed
    {
        return $this->second;
    }

    public function equals(EquatableInterface $object): bool
    {
        if ($object instanceof self === false) {
            return false;
        }

        return Comparing::equal($this->first, $object->first) && Comparing::equal($this->second, $object->second);
    }

    public function __toString(): string
    {
        $first  = '?';
        $second = '?';

        if (is_scalar($this->first) || $this->first instanceof Stringable) {
            $first = (string)$this->first;
        }

        if (is_scalar($this->second) || $this->second instanceof Stringable) {
            $second = (string)$this->second;
        }

        return '<' . $first . ',' . $second . '>';
    }

    /**
     * @param A $first
     * @param B $second
     *
     * @return Pair<A, B>
     */
    public static function create(mixed $first, mixed $second): Pair
    {
        return new Pair($first, $second);
    }

    /**
     * @return Pair<null, null>
     */
    public static function empty(): Pair
    {
        static $pair;
        $pair ??= new Pair(null, null);

        return $pair;
    }
}
