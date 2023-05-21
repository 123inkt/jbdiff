<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use AssertionError;
use DR\JBDiff\Util\BitSet;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BitSet::class)]
class BitSetTest extends TestCase
{
    public function testGetSetWithinWordBoundary(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(64, 126);

        static::assertFalse($bitSet->has(63));
        static::assertTrue($bitSet->has(64));
        static::assertTrue($bitSet->has(125));
        static::assertFalse($bitSet->has(126));
    }

    public function testOutsideWordBoundary(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(2, 129);

        static::assertFalse($bitSet->has(1));
        static::assertTrue($bitSet->has(2));
        static::assertTrue($bitSet->has(64));
        static::assertTrue($bitSet->has(128));
        static::assertFalse($bitSet->has(129));
    }

    public function testGetSetWithSingleArgumentOnEndingBoundary(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(63);

        static::assertFalse($bitSet->has(62));
        static::assertTrue($bitSet->has(63));
        static::assertFalse($bitSet->has(64));
    }

    public function testGetSetWithSingleArgumentOnStartingBoundary(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(64);

        static::assertFalse($bitSet->has(63));
        static::assertTrue($bitSet->has(64));
        static::assertFalse($bitSet->has(65));
    }

    public function testGetSetShouldSkipOnZeroRange(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(5, 5);
        static::assertFalse($bitSet->has(4));
        static::assertFalse($bitSet->has(5));
        static::assertFalse($bitSet->has(6));
    }

    public function testGetSetShouldDisallowNegativeStartIndex(): void
    {
        $bitSet = new BitSet();

        $this->expectException(AssertionError::class);
        $bitSet->set(-1);
    }

    public function testGetSetShouldDisallowSecondArgumentBeforeFirst(): void
    {
        $bitSet = new BitSet();

        $this->expectException(AssertionError::class);
        $bitSet->set(5, 4);
    }

    public function testClearSingleValue(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(63, 65);

        static::assertFalse($bitSet->has(62));
        static::assertTrue($bitSet->has(63));
        static::assertTrue($bitSet->has(64));

        $bitSet->clear(63);
        static::assertFalse($bitSet->has(62));
        static::assertFalse($bitSet->has(63));
        static::assertTrue($bitSet->has(64));
    }

    public function testClearOutOfBoundRange(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(0, 63);
        $bitSet->clear(60, 130);

        static::assertTrue($bitSet->has(59));
        static::assertFalse($bitSet->has(60));
    }

    public function testToString(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(5, 24);
        $bitSet->set(64, 126);

        $string = (string)$bitSet;

        $expected = "0: 0000000000000000000000000000000000000000111111111111111111100000\n";
        $expected .= "1: 0011111111111111111111111111111111111111111111111111111111111111\n";

        static::assertSame($expected, $string);
    }

    public function testSerialize(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(5, 6);
        $bitSet->set(200, 201);

        /** @var BitSet $newBitSet */
        $newBitSet = unserialize(serialize($bitSet));

        static::assertEquals($bitSet, $newBitSet);
    }

    public function testSerializeBinaryString(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(0, 500);

        $binaryString = $bitSet->toBinaryString();
        $newBitSet    = BitSet::fromBinaryString($binaryString);

        static::assertEquals($bitSet, $newBitSet);
    }

    public function testSerializeBinaryStringEmptyBitSet(): void
    {
        $bitSet = new BitSet();

        $binaryString = $bitSet->toBinaryString();
        $newBitSet    = BitSet::fromBinaryString($binaryString);

        static::assertEquals($bitSet, $newBitSet);
    }

    public function testSerializeBase64String(): void
    {
        $bitSet = new BitSet();
        $bitSet->set(5, 6);
        $bitSet->set(200, 201);

        $base64String = $bitSet->toBase64String();
        $newBitSet    = BitSet::fromBase64String($base64String);

        static::assertEquals($bitSet, $newBitSet);
    }

    public function testInvalidBase64String(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to decode base64 string: ##');
        BitSet::fromBase64String('##');
    }
}
