<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\CursorIteratorInterface;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\InvertedDiffIterableWrapper;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InvertedDiffIterableWrapper::class)]
class InvertedDiffIterableWrapperTest extends TestCase
{
    public function testGetLength1(): void
    {
        $innerIterable = $this->createMock(DiffIterableInterface::class);
        $innerIterable->expects($this->once())
            ->method('getLength1')
            ->willReturn(10);

        $iterable = new InvertedDiffIterableWrapper($innerIterable);

        static::assertSame(10, $iterable->getLength1());
    }

    public function testGetLength2(): void
    {
        $innerIterable = $this->createMock(DiffIterableInterface::class);
        $innerIterable->expects($this->once())
            ->method('getLength2')
            ->willReturn(5);

        $iterable = new InvertedDiffIterableWrapper($innerIterable);

        static::assertSame(5, $iterable->getLength2());
    }

    public function testChanges(): void
    {
        $innerIterator = $this->createMock(CursorIteratorInterface::class);

        $innerIterable = $this->createMock(DiffIterableInterface::class);
        $innerIterable->expects($this->once())
            ->method('unchanged')
            ->willReturn($innerIterator);

        $iterable = new InvertedDiffIterableWrapper($innerIterable);

        static::assertSame($innerIterator, $iterable->changes());
    }

    public function testUnchanged(): void
    {
        $innerIterator = $this->createMock(CursorIteratorInterface::class);

        $innerIterable = $this->createMock(DiffIterableInterface::class);
        $innerIterable->expects($this->once())
            ->method('changes')
            ->willReturn($innerIterator);

        $iterable = new InvertedDiffIterableWrapper($innerIterable);

        static::assertSame($innerIterator, $iterable->unchanged());
    }
}
