<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\DiffChangeDiffIterable;
use DR\JBDiff\Diff\Comparison\Iterables\DiffIterableInterface;
use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableWrapper;
use DR\JBDiff\Entity\Change\Change;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FairDiffIterableWrapper::class)]
class FairDiffIterableWrapperTest extends TestCase
{
    public function testChanges(): void
    {
        $iterable = $this->createMock(DiffIterableInterface::class);
        $wrapper  = new FairDiffIterableWrapper($iterable);

        $iterable->expects(self::once())->method('changes');

        $wrapper->changes();
    }

    public function testUnchanged(): void
    {
        $iterable = $this->createMock(DiffIterableInterface::class);
        $wrapper  = new FairDiffIterableWrapper($iterable);

        $iterable->expects(self::once())->method('unchanged');

        $wrapper->unchanged();
    }

    public function testGetLength(): void
    {
        $change  = new Change(10, 20, 30, 40);
        $wrapper = new FairDiffIterableWrapper(new DiffChangeDiffIterable($change, 50, 60));
        static::assertSame(50, $wrapper->getLength1());
        static::assertSame(60, $wrapper->getLength2());
    }
}
