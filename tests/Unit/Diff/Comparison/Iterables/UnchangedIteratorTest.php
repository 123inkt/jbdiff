<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\RangesChangeIterable;
use DR\JBDiff\Diff\Comparison\Iterables\UnchangedIterator;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnchangedIterator::class)]
class UnchangedIteratorTest extends TestCase
{
    public function testUnchangedIterator(): void
    {
        // string1: this simply is the best match
        // string2: this really is not a match
        // the ranges for the differences
        $range1 = new Range(6, 12, 6, 12);
        $range2 = new Range(16, 24, 16, 21);

        $ranges = iterator_to_array(new UnchangedIterator(new RangesChangeIterable([$range1, $range2]), 29, 26));

        // the ranges for all the similarities
        $expected = [
            new Range(0, 6, 0, 6),
            new Range(12, 16, 12, 16),
            new Range(24, 29, 21, 26)
        ];

        static::assertEquals($expected, $ranges);
    }

    public function testUnchangedIteratorWithoutLeadingSimilarities(): void
    {
        // string1: that simply is the best match
        // string2: this really is not a match
        // the ranges for the differences
        $range1 = new Range(0, 12, 0, 12);
        $range2 = new Range(16, 24, 16, 21);

        $ranges = iterator_to_array(new UnchangedIterator(new RangesChangeIterable([$range1, $range2]), 29, 26));

        // the ranges for all the similarities
        $expected = [
            new Range(12, 16, 12, 16),
            new Range(24, 29, 21, 26)
        ];

        static::assertEquals($expected, $ranges);
    }
}
