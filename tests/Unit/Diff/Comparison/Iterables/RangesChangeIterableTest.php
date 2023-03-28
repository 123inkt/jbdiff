<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\RangesChangeIterable;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RangesChangeIterable::class)]
class RangesChangeIterableTest extends TestCase
{
    public function testValid()
    {
        // Test valid() when there are ranges
        $ranges = [new Range(0, 0, 1, 1)];
        $iterable = new RangesChangeIterable($ranges);
        static::assertTrue($iterable->valid());

        // Test valid() when there are no ranges
        $ranges = [];
        $iterable = new RangesChangeIterable($ranges);
        static::assertFalse($iterable->valid());
    }

    public function testNext()
    {
        // Test next() when there are ranges left
        $ranges = [new Range(0, 0, 1, 1), new Range(2, 2, 3, 3)];
        $iterable = new RangesChangeIterable($ranges);
        $iterable->next();
        static::assertSame(2, $iterable->getStart1());
        static::assertSame(3, $iterable->getStart2());
        static::assertSame(2, $iterable->getEnd1());
        static::assertSame(3, $iterable->getEnd2());

        // Test next() when there are no ranges left
        $ranges = [new Range(0, 0, 1, 1)];
        $iterable = new RangesChangeIterable($ranges);
        $iterable->next();
        static::assertFalse($iterable->valid());
    }

    public function testGetStart1()
    {
        $ranges = [new Range(0, 0, 1, 1)];
        $iterable = new RangesChangeIterable($ranges);
        static::assertSame(0, $iterable->getStart1());
    }

    public function testGetStart2()
    {
        $ranges = [new Range(0, 0, 1, 1)];
        $iterable = new RangesChangeIterable($ranges);
        static::assertSame(1, $iterable->getStart2());
    }

    public function testGetEnd1()
    {
        $ranges = [new Range(0, 0, 1, 1)];
        $iterable = new RangesChangeIterable($ranges);
        static::assertSame(0, $iterable->getEnd1());
    }

    public function testGetEnd2()
    {
        $ranges = [new Range(0, 0, 1, 1)];
        $iterable = new RangesChangeIterable($ranges);
        static::assertSame(1, $iterable->getEnd2());
    }
}
