<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity;

use DR\JBDiff\Entity\Pair;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;
use Stringable;

#[CoversClass(Pair::class)]
class PairTest extends TestCase
{
    public function testCreate(): void
    {
        $pair = Pair::create(42, 'foo');
        static::assertSame(42, $pair->getFirst());
        static::assertSame('foo', $pair->getSecond());
    }

    public function testEmpty(): void
    {
        $pair = Pair::empty();
        static::assertNull($pair->getFirst());
        static::assertNull($pair->getSecond());
    }

    public function testEquals(): void
    {
        $pair1 = new Pair(42, 'foo');
        $pair2 = new Pair(42, 'foo');
        $pair3 = new Pair(42, 'bar');
        $pair4 = new Pair('foo', 42);
        $pair5 = new Pair('foo', 'bar');
        $pair6 = new Pair(null, null);

        static::assertTrue($pair1->equals($pair2));
        static::assertFalse($pair1->equals($pair3));
        static::assertFalse($pair1->equals($pair4));
        static::assertFalse($pair1->equals($pair5));
        static::assertFalse($pair1->equals($pair6));
        static::assertFalse($pair1->equals(new Range(1, 2, 3, 4)));
    }

    public function testToString(): void
    {
        $pair = new Pair(42, 'foo');
        static::assertSame('<42,foo>', (string)$pair);

        $pair = new Pair([], new stdClass());
        static::assertSame('<?,?>', (string)$pair);

        $pair = new Pair(
            new class implements Stringable {
                public function __toString(): string
                {
                    return 'custom';
                }
            },
            42
        );
        static::assertSame('<custom,42>', (string)$pair);
    }
}
