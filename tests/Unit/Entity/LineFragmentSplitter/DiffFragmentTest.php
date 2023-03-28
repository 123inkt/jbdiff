<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity\LineFragmentSplitter;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\JBDiff\Entity\LineFragmentSplitter\DiffFragment;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DiffFragment::class)]
class DiffFragmentTest extends TestCase
{
    use AccessorPairAsserter;

    public function testAccessorPairs(): void
    {
        $fragment = new DiffFragment(1, 2, 3, 4);
        static::assertSame(1, $fragment->getStartOffset1());
        static::assertSame(2, $fragment->getEndOffset1());
        static::assertSame(3, $fragment->getStartOffset2());
        static::assertSame(4, $fragment->getEndOffset2());
    }
}
