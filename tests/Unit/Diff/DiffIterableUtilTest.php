<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff;

use DR\JBDiff\Diff\Comparison\Iterables\DiffChangeDiffIterable;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableWrapper;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Change\Change;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffIterableUtil::class)]
class DiffIterableUtilTest extends TestCase
{
    /**
     * @throws DiffToBigException
     */
    public function testDiff(): void
    {
        $left  = [1, 3, 4];
        $right = [1, 2, 4];

        $result = DiffIterableUtil::diff($left, $right);

        $ranges   = iterator_to_array($result->changes());
        $expected = [new Range(1, 2, 1, 2)];

        static::assertEquals($expected, $ranges);
    }

    public function testCreate(): void
    {
        $change = new Change(1, 2, 3, 4);

        $iterator = DiffIterableUtil::create($change, 10, 20);
        $ranges   = iterator_to_array($iterator->changes());

        $expected = [new Range(1, 4, 2, 6)];

        static::assertEquals($expected, $ranges);
    }

    public function testCreateFromRanges(): void
    {
        $iterator = DiffIterableUtil::createFromRanges([new Range(1, 2, 3, 4)], 10, 20);
        $ranges   = iterator_to_array($iterator->changes());

        $expected = [new Range(1, 2, 3, 4)];

        static::assertEquals($expected, $ranges);
    }

    public function testCreateUnchanged(): void
    {
        $iterator = DiffIterableUtil::createUnchanged([new Range(1, 2, 3, 4)], 10, 20);
        $ranges   = iterator_to_array($iterator->changes());

        $expected = [new Range(0, 1, 0, 3), new Range(2, 10, 4, 20)];

        static::assertEquals($expected, $ranges);
    }

    public function testFair(): void
    {
        $iterableA = $this->createMock(FairDiffIterableInterface::class);
        $iterableB = $this->createMock(DiffIterableInterface::class);

        static::assertSame($iterableA, DiffIterableUtil::fair($iterableA));
        static::assertInstanceOf(FairDiffIterableWrapper::class, DiffIterableUtil::fair($iterableB));
    }

    /**
     * @throws DiffToBigException
     */
    public function testMatchAdjustmentDelimiters(): void
    {
        $text1 = CharSequence::fromString("text1");
        $text2 = CharSequence::fromString("text2");

        $words1 = [new WordChunk($text1, 0, 2)];
        $words2 = [new WordChunk($text2, 0, 3)];

        $changes = new FairDiffIterableWrapper(new DiffChangeDiffIterable(new Change(1, 1, 1, 1), 1, 2));

        $iterator = DiffIterableUtil::matchAdjustmentDelimiters($text1, $text2, $words1, $words2, $changes, 0, 0);
        $ranges   = iterator_to_array($iterator->changes());

        static::assertEquals([new Range(2, 5, 3, 5)], $ranges);
    }
}
