<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Util\LCS;

use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Diff\Util\LCS\PatienceIntLCS;
use DR\JBDiff\Util\BitSet;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PatienceIntLCS::class)]
class PatienceIntLCSTest extends TestCase
{
    /**
     * @param int[] $first
     * @param int[] $second
     *
     * @throws DiffToBigException
     */
    #[DataProvider('dataProvider')]
    public function testExecute(array $first, array $second, BitSet $bitsetA, BitSet $bitsetB): void
    {
        $patience = new PatienceIntLCS($first, $second);
        $patience->execute(true);
        $changes = $patience->getChanges();

        static::assertEquals($bitsetA, $changes[0]);
        static::assertEquals($bitsetB, $changes[1]);
    }

    public static function dataProvider(): Generator
    {
        yield "only additions" => [
            [],
            [4],
            new BitSet(),
            (new BitSet())->set(0)
        ];

        yield "only removals" => [
            [4],
            [],
            (new BitSet())->set(0),
            new BitSet()
        ];

        yield "more deletions than additions" => [
            [4, 5, 5, 5, 5, 5, 5, 6, 7, 8, 9, 10, 11, 12],
            [4, 5, 5, 6, 7, 8, 9, 10, 11, 12],
            (new BitSet())->set(3, 7),
            new BitSet()
        ];

        yield "more additions than deletions" => [
            [4, 5, 5, 6, 7, 8, 9, 10, 11, 12],
            [4, 5, 5, 5, 5, 5, 5, 6, 7, 8, 9, 10, 11, 12],
            new BitSet(),
            (new BitSet())->set(3, 7)
        ];

        yield "changes without newline" => [
            [1, 2, 4, 5, 6, 6],
            [1, 2, 4, 5, 6, 6, 6],
            new BitSet(),
            (new BitSet())->set(6)
        ];
    }
}
