<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\RangesDiffIterable;
use DR\JBDiff\Diff\Comparison\Iterables\SubiterableDiffIterable;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubiterableDiffIterable::class)]
class SubiterableDiffIterableTest extends TestCase
{
    public function testConstruct(): void
    {
        $range1 = new Range(1, 2, 3, 4);
        $range2 = new Range(2, 3, 4, 5);

        $rangeIterable = new RangesDiffIterable([$range1, $range2], 5, 6);
        $subiterable   = new SubiterableDiffIterable($rangeIterable, 0, 5, 0, 5);

        static::assertEquals([$range1, $range2], iterator_to_array($subiterable->changes()));
    }
}
