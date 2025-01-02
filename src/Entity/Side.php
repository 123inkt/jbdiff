<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity;

use InvalidArgumentException;

class Side
{
    private const LEFT  = 0;
    private const RIGHT = 1;

    private function __construct(private readonly int $index)
    {
    }

    public static function fromIndex(int $index): Side
    {
        return match ($index) {
            self::LEFT  => self::left(),
            self::RIGHT => self::right(),
            default     => throw new InvalidArgumentException('Invalid index: ' . $index),
        };
    }

    public static function fromLeft(bool $isLeft): Side
    {
        return $isLeft ? self::left() : self::right();
    }

    public static function fromRight(bool $isRight): Side
    {
        return $isRight ? self::right() : self::left();
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function isLeft(): bool
    {
        return $this->index === self::LEFT;
    }

    public function other(bool $other = true): Side
    {
        if ($other === false) {
            return $this;
        }

        return $this->isLeft() ? self::right() : self::left();
    }

    /**
     * @template T
     *
     * @param T $left
     * @param T $right
     *
     * @return T
     */
    public function select(mixed $left, mixed $right): mixed
    {
        return $this->isLeft() ? $left : $right;
    }

    private static function left(): Side
    {
        /** @var ?Side $left */
        static $left = null;

        return $left ??= new Side(self::LEFT);
    }

    private static function right(): Side
    {
        /** @var ?Side $right */
        static $right = null;

        return $right ??= new Side(self::RIGHT);
    }
}
