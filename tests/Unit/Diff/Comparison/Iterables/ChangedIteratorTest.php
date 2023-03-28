<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\ChangedIterator;
use DR\JBDiff\Diff\Comparison\Iterables\DiffChangeChangeIterable;
use DR\JBDiff\Entity\Change\Change;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChangedIterator::class)]
class ChangedIteratorTest extends TestCase
{
    public function testIteration(): void
    {
        $changeA       = new Change(10, 20, 30, 40);
        $changeB       = new Change(20, 30, 40, 50);
        $changeA->link = $changeB;

        $iterator = new ChangedIterator(new DiffChangeChangeIterable($changeA));
        $ranges   = iterator_to_array($iterator);

        $expected = [
            new Range(10, 40, 20, 60),
            new Range(20, 60, 30, 80)
        ];
        static::assertEquals($expected, $ranges);
    }
}
