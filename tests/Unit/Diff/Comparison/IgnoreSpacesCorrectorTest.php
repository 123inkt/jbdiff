<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\IgnoreSpacesCorrector;
use DR\JBDiff\Diff\Comparison\Iterables\ChangedIterator;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\RangesChangeIterable;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IgnoreSpacesCorrector::class)]
class IgnoreSpacesCorrectorTest extends TestCase
{
    public function testBuild(): void
    {
        $text1 = CharSequence::fromString("  foobar  ");
        $text2 = CharSequence::fromString("barfoo");

        $range               = new Range(0, $text1->length(), 0, $text2->length());
        $rangeChangeIterator = new RangesChangeIterable([$range]);
        $changeIterator      = new ChangedIterator($rangeChangeIterator);

        $iterable = $this->createMock(DiffIterableInterface::class);
        $iterable->method('changes')->willReturn($changeIterator);

        $ranges = iterator_to_array((new IgnoreSpacesCorrector($iterable, $text1, $text2))->build()->changes());
        static::assertEquals([new Range(2, 8, 0, 6)], $ranges);
    }

    public function testBuildIgnoreEqualWhenTrimmed(): void
    {
        $text1 = CharSequence::fromString("  foobar  ");
        $text2 = CharSequence::fromString(" foobar ");

        $range               = new Range(0, $text1->length(), 0, $text2->length());
        $rangeChangeIterator = new RangesChangeIterable([$range]);
        $changeIterator      = new ChangedIterator($rangeChangeIterator);

        $iterable = $this->createMock(DiffIterableInterface::class);
        $iterable->method('changes')->willReturn($changeIterator);

        $ranges = iterator_to_array((new IgnoreSpacesCorrector($iterable, $text1, $text2))->build()->changes());
        static::assertSame([], $ranges);
    }
}
