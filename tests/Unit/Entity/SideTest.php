<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity;

use DR\JBDiff\Entity\Side;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Side::class)]
class SideTest extends TestCase
{
    public function testGetIndex(): void
    {
        static::assertSame(0, Side::fromIndex(0)->getIndex());
        static::assertSame(1, Side::fromIndex(1)->getIndex());
    }

    public function testFromIndexShouldThrowExceptionOnInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid index: -1');
        Side::fromIndex(-1);
    }

    public function testIsLeft(): void
    {
        static::assertTrue(Side::fromIndex(0)->isLeft());
        static::assertFalse(Side::fromIndex(1)->isLeft());
    }

    public function testFromLeft(): void
    {
        static::assertTrue(Side::fromLeft(true)->isLeft());
        static::assertFalse(Side::fromLeft(false)->isLeft());
    }

    public function testFromRight(): void
    {
        static::assertFalse(Side::fromRight(true)->isLeft());
        static::assertTrue(Side::fromRight(false)->isLeft());
    }

    public function testOther(): void
    {
        $left  = Side::fromLeft(true);
        $right = Side::fromRight(true);

        static::assertSame($right, $left->other());
        static::assertSame($left, $left->other(false));
        static::assertSame($left, $right->other());
        static::assertSame($right, $right->other(false));
    }

    public function testSelect(): void
    {
        $left  = Side::fromLeft(true);
        $right = Side::fromRight(true);
        $stringA = 'left';
        $stringB = 'right';

        static::assertSame($stringA, $left->select($stringA, $stringB));
        static::assertSame($stringB, $right->select($stringA, $stringB));
    }
}
