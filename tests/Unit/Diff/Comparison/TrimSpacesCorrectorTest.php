<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\Iterables\ChangedIterator;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\RangesChangeIterable;
use DR\JBDiff\Diff\Comparison\TrimSpacesCorrector;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TrimSpacesCorrector::class)]
class TrimSpacesCorrectorTest extends TestCase
{
    public function testBuild()
    {
        $text1 = CharSequence::fromString("  foo trim ");
        $text2 = CharSequence::fromString("   bar trim");

        $rangeIterable  = new RangesChangeIterable(
            [
                new Range(0, 2, 0, 2),   // all whitespace, after trim is empty range
                new Range(2, 5, 2, 6),   // difference between foo and bar
                new Range(6, 11, 7, 11), // both trim with and without space is equal after trim
            ]
        );
        $changeIterable = new ChangedIterator($rangeIterable);
        $iterable       = $this->createMock(DiffIterableInterface::class);
        $iterable->expects(self::once())->method('changes')->willReturn($changeIterable);

        $corrector = new TrimSpacesCorrector($iterable, $text1, $text2);
        $result    = $corrector->build();

        $expected = [new Range(2, 5, 3, 6)];
        static::assertEquals($expected, iterator_to_array($result->changes()));
    }
}
