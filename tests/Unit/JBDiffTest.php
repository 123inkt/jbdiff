<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit;

use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\LineFragmentSplitter\DiffFragment;
use DR\JBDiff\JBDiff;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JBDiff::class)]
class JBDiffTest extends TestCase
{
    /**
     * @throws DiffToBigException
     */
    public function testCompare(): void
    {
        $text1 = "unchanged old1 unchanged old2 unchanged";
        $text2 = "unchanged new1 unchanged new2 unchanged";

        $lineBlocks = (new JBDiff())->compare($text1, $text2);
        static::assertCount(1, $lineBlocks);

        $expected = [
            new DiffFragment(10, 14, 10, 14),
            new DiffFragment(25, 29, 25, 29)
        ];
        static::assertEquals($expected, $lineBlocks[0]->fragments);
    }

    /**
     * @throws DiffToBigException
     */
    public function testCompareToIterator(): void
    {
        $text1 = "unchanged old1 unchanged old2 unchanged";
        $text2 = "unchanged new1 unchanged new2 unchanged";

        $iterator = (new JBDiff())->compareToIterator($text1, $text2);
        $results  = iterator_to_array($iterator, false);
        static::assertCount(10, $results);
    }
}
