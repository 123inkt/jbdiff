<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Util;

use InvalidArgumentException;
use Stringable;

class BitSet implements Stringable
{
    /* set consts based on 32 or 64 bit architecture */
    private const ADDRESS_BITS_PER_WORD = PHP_INT_SIZE === 4 ? 5 : 6;
    private const WORD_MASK             = PHP_INT_SIZE === 4 ? 0x1f : 0x3f;
    private const MAX_WORD_SLOT         = PHP_INT_SIZE === 4 ? 32 : 64;
    private const MASK_ALL              = -1;

    /** @var array<int, int> */
    private array $words = [];

    /**
     * Sets the bits from the specified $fromIndex (inclusive) to the
     * specified `toIndex` (exclusive) to `true`.
     *
     * @param int  $fromIndex index of the first bit to be set
     * @param ?int $toIndex   index after the last bit to be set
     */
    public function set(int $fromIndex, ?int $toIndex = null): self
    {
        foreach ($this->getWords($fromIndex, $toIndex) as $wordIdx => $value) {
            $this->words[$wordIdx] ??= 0;
            $this->words[$wordIdx] |= $value;
        }

        return $this;
    }

    public function clear(int $fromIndex, ?int $toIndex = null): void
    {
        foreach ($this->getWords($fromIndex, $toIndex) as $wordIdx => $value) {
            if (isset($this->words[$wordIdx]) === false) {
                continue;
            }

            $this->words[$wordIdx] &= self::MASK_ALL ^ $value;
            if ($this->words[$wordIdx] === 0) {
                unset($this->words[$wordIdx]);
            }
        }
    }

    public function has(int $bitIndex): bool
    {
        $wordIdx = $bitIndex >> self::ADDRESS_BITS_PER_WORD;
        $bitIdx  = $bitIndex & self::WORD_MASK;

        return (($this->words[$wordIdx] ?? 0) & (1 << $bitIdx)) !== 0;
    }

    public function __toString(): string
    {
        $result = '';
        foreach ($this->words as $index => $bits) {
            $result .= $index . ': ' . str_pad(decbin($bits), self::MAX_WORD_SLOT, '0', STR_PAD_LEFT) . "\n";
        }

        return $result;
    }

    /**
     * @return array<int, int>
     */
    public function __serialize(): array
    {
        return $this->words;
    }

    /**
     * @param array<int, int> $data
     */
    public function __unserialize(array $data): void
    {
        $this->words = $data;
    }

    /**
     * Convert the BitSet to binary string, which can be turned into BitSet again via @see BitSet::fromBinaryString. Note: this method
     * is not compatible between 32-bit and 64-bit systems.
     */
    public function toBinaryString(): string
    {
        if (count($this->words) === 0) {
            return '';
        }

        $words = [];

        // ensure all slots between 0 and maxKey have a value
        $maxKey = max(0, ...array_keys($this->words));
        for ($i = 0; $i <= $maxKey; $i++) {
            $words[$i] = $this->words[$i] ?? 0;
        }

        return pack(PHP_INT_SIZE === 4 ? 'N*' : 'J*', ...$words);
    }

    /**
     * Convert the BitSet to base64 encode binary string, which can be turned into BitSet again via @see BitSet::fromBase64String. Note: this method
     * is not compatible between 32-bit and 64-bit systems.
     */
    public function toBase64String(): string
    {
        return base64_encode($this->toBinaryString());
    }

    public static function fromBinaryString(string $data): BitSet
    {
        $bitSet = new BitSet();

        if ($data === '') {
            return $bitSet;
        }

        /** @var list<int>|false $words */
        $words = unpack(PHP_INT_SIZE === 4 ? 'N*' : 'J*', $data);
        if ($words === false || count($words) === 0) {
            throw new InvalidArgumentException('Unable to unpack from binary string: ' . base64_encode($data));
        }

        // cleanup all keys where value = 0;
        $index = 0;
        foreach ($words as $value) {
            if ($value !== 0) {
                $bitSet->words[$index] = $value;
            }
            ++$index;
        }

        return $bitSet;
    }

    public static function fromBase64String(string $data): BitSet
    {
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new InvalidArgumentException('Unable to decode base64 string: ' . $data);
        }

        return self::fromBinaryString($decoded);
    }

    /**
     * @return array<int, int>
     */
    private function getWords(int $fromIndex, ?int $toIndex = null): array
    {
        if ($fromIndex === $toIndex) {
            return [];
        }

        $toIndex = $toIndex === null ? $fromIndex : $toIndex - 1;
        assert($fromIndex >= 0 && $fromIndex <= $toIndex);

        $startWordIdx = $fromIndex >> self::ADDRESS_BITS_PER_WORD;
        $endWordIdx   = $toIndex >> self::ADDRESS_BITS_PER_WORD;

        // calculate the bit mask from the starting index: 111111111100
        $startBitMask = -1 << ($fromIndex % self::MAX_WORD_SLOT);

        // calculate the bit mask till to end index: 001111111111
        $endBitMask = (-1 << (($toIndex % self::MAX_WORD_SLOT) + 1)) ^ -1;

        $words = [];

        // start and end within same word, combine mask and add to words
        if ($startWordIdx === $endWordIdx) {
            $words[$startWordIdx] = ($startBitMask & $endBitMask);

            return $words;
        }

        // loop over the word indices, add the start, all, or end masks to the list
        for ($wordIdx = $startWordIdx; $wordIdx <= $endWordIdx; $wordIdx++) {
            if ($wordIdx === $startWordIdx) {
                $words[$wordIdx] = $startBitMask;
            } elseif ($wordIdx === $endWordIdx) {
                $words[$wordIdx] = $endBitMask;
            } else {
                $words[$wordIdx] = self::MASK_ALL;
            }
        }

        return $words;
    }
}
