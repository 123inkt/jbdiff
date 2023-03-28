<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\RangesDiffIterable;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RangesDiffIterable::class)]
class RangesDiffIterableTest extends TestCase
{
    public function testCreateChangeIterable(): void
    {
        $range1 = new Range(0, 2, 3, 5);
        $range2 = new Range(7, 10, 12, 24);

        $iterable = new RangesDiffIterable([$range1, $range2], 100, 200);
        static::assertSame(100, $iterable->getLength1());
        static::assertSame(200, $iterable->getLength2());

        $ranges = iterator_to_array($iterable->changes());
        static::assertEquals([$range1, $range2], $ranges);
    }
}
