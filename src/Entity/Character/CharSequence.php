<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Entity\Character;

use DR\JBDiff\Entity\EquatableInterface;
use function count;
use function assert;

class CharSequence implements CharSequenceInterface
{
    /**
     * @param string[] $chars
     */
    private function __construct(private readonly array $chars)
    {
    }

    public function length(): int
    {
        return count($this->chars);
    }

    public function charAt(int $index): string
    {
        assert(isset($this->chars[$index]));

        return $this->chars[$index];
    }

    /**
     * @return string[]
     */
    public function chars(): array
    {
        return $this->chars;
    }

    public function isEmpty(): bool
    {
        return count($this->chars) === 0;
    }

    public function subSequence(int $start, int $end): CharSequenceInterface
    {
        return new CharSequence(array_slice($this->chars, $start, $end - $start));
    }

    public function __toString(): string
    {
        return implode('', $this->chars);
    }

    public static function fromString(string $string): self
    {
        $chars = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        assert($chars !== false);

        return new CharSequence($chars);
    }

    public function equals(EquatableInterface $object): bool
    {
        if ($object instanceof self === false) {
            return false;
        }

        return $object === $this || $this->chars === $object->chars;
    }
}
