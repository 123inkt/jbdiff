<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\RangesDiffIterable;
use DR\JBDiff\Diff\Comparison\Iterables\SubiterableChangeIterable;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SubiterableChangeIterable::class)]
class SubiterableChangeIterableTest extends TestCase
{
    public function testIteratorShouldSkipFirstRange(): void
    {
        $rangeA = new Range(0, 5, 0, 5);
        $rangeB = new Range(6, 10, 6, 11);

        $rangeIterable = new RangesDiffIterable([$rangeA, $rangeB], 20, 20);
        $subiterable   = new SubiterableChangeIterable($rangeIterable, 6, 10, 6, 10);

        static::assertTrue($subiterable->valid());
        static::assertSubiterableRange($subiterable, 0, 4, 0, 4);

        $subiterable->next();
        static::assertFalse($subiterable->valid());
    }

    public function testIteratorShouldSkipLastRange(): void
    {
        $rangeB = new Range(6, 10, 6, 9);
        $rangeC = new Range(15, 20, 15, 20);

        $rangeIterable = new RangesDiffIterable([$rangeB, $rangeC], 20, 20);
        $subiterable   = new SubiterableChangeIterable($rangeIterable, 6, 10, 6, 10);

        static::assertTrue($subiterable->valid());
        static::assertSubiterableRange($subiterable, 0, 4, 0, 3);

        $subiterable->next();
        static::assertFalse($subiterable->valid());
    }

    public function testIteratorShouldSkipEmptyRange(): void
    {
        $rangeB = new Range(6, 10, 6, 9);
        $rangeC = new Range(15, 20, 15, 20);

        $rangeIterable = new RangesDiffIterable([$rangeB, $rangeC], 20, 20);
        $subiterable   = new SubiterableChangeIterable($rangeIterable, 6, 15, 6, 15);

        static::assertTrue($subiterable->valid());
        static::assertSubiterableRange($subiterable, 0, 4, 0, 3);

        $subiterable->next();
        static::assertFalse($subiterable->valid());
    }

    private static function assertSubiterableRange(SubiterableChangeIterable $subiterable, int $start1, int $end1, int $start2, int $end2): void
    {
        static::assertSame($start1, $subiterable->getStart1());
        static::assertSame($end1, $subiterable->getEnd1());
        static::assertSame($start2, $subiterable->getStart2());
        static::assertSame($end2, $subiterable->getEnd2());
    }
}
