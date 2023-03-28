<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\DefaultCorrector;
use DR\JBDiff\Diff\Comparison\Iterables\ChangedIterator;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\RangesChangeIterable;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultCorrector::class)]
class DefaultCorrectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testBuild(): void
    {
        $text1 = CharSequence::fromString("  foobar  ");
        $text2 = CharSequence::fromString(" foobar ");

        $range               = new Range(0, $text1->length(), 0, $text2->length());
        $rangeChangeIterator = new RangesChangeIterable([$range]);
        $changeIterator      = new ChangedIterator($rangeChangeIterator);

        $iterable = $this->createMock(DiffIterableInterface::class);
        $iterable->method('changes')->willReturn($changeIterator);

        $ranges = iterator_to_array((new DefaultCorrector($iterable, $text1, $text2))->build()->changes());
        static::assertEquals([new Range(1, 9, 1, 7)], $ranges);
    }

    public function testBuildSkipEmptyRange(): void
    {
        $text1 = CharSequence::fromString("");
        $text2 = CharSequence::fromString("");

        $range               = new Range(0, $text1->length(), 0, $text2->length());
        $rangeChangeIterator = new RangesChangeIterable([$range]);
        $changeIterator      = new ChangedIterator($rangeChangeIterator);

        $iterable = $this->createMock(DiffIterableInterface::class);
        $iterable->method('changes')->willReturn($changeIterator);

        $ranges = iterator_to_array((new DefaultCorrector($iterable, $text1, $text2))->build()->changes());
        static::assertSame([], $ranges);
    }
}
