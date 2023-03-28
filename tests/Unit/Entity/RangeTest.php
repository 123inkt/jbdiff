<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity;

use DR\JBDiff\Entity\EquatableInterface;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Range::class)]
class RangeTest extends TestCase
{
    public function testIsEmpty(): void
    {
        static::assertTrue((new Range(10, 10, 20, 20))->isEmpty());
        static::assertFalse((new Range(10, 10, 20, 25))->isEmpty());
        static::assertFalse((new Range(10, 15, 20, 20))->isEmpty());
        static::assertFalse((new Range(10, 15, 20, 25))->isEmpty());
    }

    public function testToString(): void
    {
        $range = new Range(10, 20, 30, 40);
        static::assertSame('[10, 20] - [30, 40]', (string)$range);
    }

    public function testEqualsDifferentObjectType(): void
    {
        $range = new Range(10, 20, 30, 40);
        static::assertFalse($range->equals($this->createMock(EquatableInterface::class)));
    }

    public function testEqualsMatchSameObject(): void
    {
        $range = new Range(10, 20, 30, 40);
        static::assertTrue($range->equals($range));
    }

    public function testEqualsMatchSameValues(): void
    {
        $rangeA = new Range(10, 20, 30, 40);
        $rangeB = new Range(10, 20, 30, 40);
        static::assertTrue($rangeA->equals($rangeB));
    }

    public function testEqualsNotMatchDifferentValues(): void
    {
        $rangeA = new Range(10, 20, 30, 40);
        $rangeB = new Range(15, 20, 30, 40);
        $rangeC = new Range(10, 25, 30, 40);
        $rangeD = new Range(10, 20, 35, 40);
        $rangeE = new Range(10, 20, 30, 45);
        static::assertFalse($rangeA->equals($rangeB));
        static::assertFalse($rangeA->equals($rangeC));
        static::assertFalse($rangeA->equals($rangeD));
        static::assertFalse($rangeA->equals($rangeE));
    }
}
