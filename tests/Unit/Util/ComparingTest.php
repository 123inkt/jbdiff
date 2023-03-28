<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use DR\JBDiff\Entity\Range;
use DR\JBDiff\Util\Comparing;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Comparing::class)]
class ComparingTest extends TestCase
{
    public function testEqualValuesShouldReturnTrue(): void
    {
        static::assertTrue(Comparing::equal(null, null));
        static::assertTrue(Comparing::equal(5, 5));
        static::assertFalse(Comparing::equal(5, 6));
        static::assertFalse(Comparing::equal(new stdClass(), new stdClass()));
    }

    public function testEqualSingleNullValue(): void
    {
        static::assertFalse(Comparing::equal(5, null));
        static::assertFalse(Comparing::equal(null, 6));
    }

    public function testEqualEquatableCompare(): void
    {
        $rangeA = new Range(1, 2, 3, 4);
        $rangeB = new Range(1, 2, 3, 4);
        $rangeC = new Range(4, 3, 2, 1);

        static::assertTrue(Comparing::equal($rangeA, $rangeB));
        static::assertTrue(Comparing::equal($rangeB, $rangeA));
        static::assertFalse(Comparing::equal($rangeA, $rangeC));
        static::assertFalse(Comparing::equal($rangeC, $rangeA));
    }
}
