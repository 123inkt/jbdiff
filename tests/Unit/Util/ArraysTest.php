<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use DR\JBDiff\Util\Arrays;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Arrays::class)]
class ArraysTest extends TestCase
{
    #[DataProvider('dataProvider')]
    public function testBinarySearch(array $data, int $fromIndex, int $toIndex, int $search, int $expected): void
    {
        static::assertSame($expected, Arrays::binarySearch($data, $fromIndex, $toIndex, $search));
    }

    public static function dataProvider(): Generator
    {
        yield "default infix search" => [[10, 20, 30, 40], 0, 4, 20, 1];
        yield "find at the start" => [[10, 20, 30, 40], 0, 4, 10, 0];
        yield "find at the end" => [[10, 20, 30, 40], 0, 4, 40, 3];
        yield "no find" => [[10, 20, 30, 40], 0, 4, 50, -5];
        yield "find with from index" => [[10, 20, 30, 40], 1, 4, 30, 2];
        yield "not find with from index" => [[10, 20, 30, 40], 1, 4, 10, -2];
    }
}
