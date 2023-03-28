<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\DiffChangeChangeIterable;
use DR\JBDiff\Entity\Change\Change;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DiffChangeChangeIterable::class)]
class DiffChangeChangeIterableTest extends TestCase
{
    public function testIterator(): void
    {
        $changeA       = new Change(10, 20, 30, 40);
        $changeB       = new Change(50, 60, 70, 80);
        $changeA->link = $changeB;

        $iterator = new DiffChangeChangeIterable($changeA);

        // first iteration
        static::assertTrue($iterator->valid());
        static::assertSame(10, $iterator->getStart1());
        static::assertSame(20, $iterator->getStart2());
        static::assertSame(10 + 30, $iterator->getEnd1());
        static::assertSame(20 + 40, $iterator->getEnd2());

        // second iteration
        $iterator->next();
        static::assertTrue($iterator->valid());
        static::assertSame(50, $iterator->getStart1());
        static::assertSame(60, $iterator->getStart2());
        static::assertSame(50 + 70, $iterator->getEnd1());
        static::assertSame(60 + 80, $iterator->getEnd2());

        // third iteration
        $iterator->next();
        static::assertFalse($iterator->valid());
    }
}
