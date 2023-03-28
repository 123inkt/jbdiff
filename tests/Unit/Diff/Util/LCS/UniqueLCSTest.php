<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Util\LCS;

use DR\JBDiff\Diff\Util\LCS\UniqueLCS;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UniqueLCS::class)]
class UniqueLCSTest extends TestCase
{
    /**
     * @param int[]   $first
     * @param int[]   $second
     * @param int[][] $expected
     */
    #[DataProvider('dataProvider')]
    public function testExecute(array $first, array $second, ?array $expected): void
    {
        $uniqueLCS = new UniqueLCS($first, $second);
        $changes   = $uniqueLCS->execute();

        if ($changes !== null) {
            ksort($changes[0]);
            ksort($changes[1]);
        }

        static::assertSame($expected, $changes);
    }

    /**
     * @return Generator<array{0: int[], 1: int[], 2: int[][]}>
     */
    public static function dataProvider(): Generator
    {
        yield "only additions" => [
            [],
            [4],
            null
        ];
        yield "only removals" => [
            [4],
            [],
            null
        ];

        yield "more removals than additions" => [
            [1, 2, 3, 5, 5, 5, 8, 10, 11],
            [1, 2, 5, 5, 6, 8],
            [[0, 1, 6], [0, 1, 5]]
        ];

        yield "more additions than removals" => [
            [1, 2, 5, 5, 6, 8],
            [1, 2, 3, 5, 5, 5, 8, 10, 11],
            [[0, 1, 5], [0, 1, 6]]
        ];

        yield "significant changes" => [
            [4, 5, 5, 6, 7, 8, 9, 10, 11, 12],
            [4, 5, 5, 5, 5, 5, 5, 6, 7, 8, 9, 10, 11, 12],
            [[0, 3, 4, 5, 6, 7, 8, 9], [0, 7, 8, 9, 10, 11, 12, 13]]
        ];
    }
}
