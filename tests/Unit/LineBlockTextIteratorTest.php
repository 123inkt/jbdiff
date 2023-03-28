<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit;

use DR\JBDiff\Entity\LineFragmentSplitter\DiffFragment;
use DR\JBDiff\Entity\LineFragmentSplitter\LineBlock;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\LineBlockTextIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LineBlockTextIterator::class)]
class LineBlockTextIteratorTest extends TestCase
{
    public function testGetIterator(): void
    {
        $text1 = "unchanged old1\nunchanged old2 unchanged";
        $text2 = "unchanged new1 unchanged\nnew2 unchanged";

        $fragments  = [new DiffFragment(10, 15, 10, 15), new DiffFragment(24, 29, 24, 29)];
        $lineBlocks = [new LineBlock($fragments, new Range(0, 39, 0, 39), 1, 1)];

        $texts = iterator_to_array(new LineBlockTextIterator($text1, $text2, $lineBlocks), false);

        $expected = [
            [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, "unchanged "],
            [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, "unchanged "],
            [LineBlockTextIterator::TEXT_REMOVED, "old1\n"],
            [LineBlockTextIterator::TEXT_ADDED, "new1 "],
            [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, "unchanged"],
            [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, "unchanged"],
            [LineBlockTextIterator::TEXT_REMOVED, " old2"],
            [LineBlockTextIterator::TEXT_ADDED, "\nnew2"],
            [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, " unchanged"],
            [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, " unchanged"],
        ];

        static::assertSame($expected, $texts);
    }

    public function testGetIteratorSplitOnNewline(): void
    {
        $text1 = "unchanged old1\nunchanged old2 unchanged";
        $text2 = "unchanged new1 unchanged\nnew2 unchanged";

        $fragments  = [new DiffFragment(10, 15, 10, 15), new DiffFragment(24, 29, 24, 29)];
        $lineBlocks = [new LineBlock($fragments, new Range(0, 39, 0, 39), 1, 1)];

        $texts = iterator_to_array(new LineBlockTextIterator($text1, $text2, $lineBlocks, true), false);

        $expected = [
            [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, "unchanged "],
            [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, "unchanged "],
            [LineBlockTextIterator::TEXT_REMOVED, "old1"],
            [LineBlockTextIterator::TEXT_REMOVED, "\n"],
            [LineBlockTextIterator::TEXT_ADDED, "new1 "],
            [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, "unchanged"],
            [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, "unchanged"],
            [LineBlockTextIterator::TEXT_REMOVED, " old2"],
            [LineBlockTextIterator::TEXT_ADDED, "\n"],
            [LineBlockTextIterator::TEXT_ADDED, "new2"],
            [LineBlockTextIterator::TEXT_UNCHANGED_BEFORE, " unchanged"],
            [LineBlockTextIterator::TEXT_UNCHANGED_AFTER, " unchanged"],
        ];

        static::assertSame($expected, $texts);
    }
}
