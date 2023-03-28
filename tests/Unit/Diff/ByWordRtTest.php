<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff;

use DR\JBDiff\ComparisonPolicy;
use DR\JBDiff\Diff\ByWordRt;
use DR\JBDiff\Diff\Comparison\Iterables\DiffChangeDiffIterable;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Change\Change;
use DR\JBDiff\Entity\Character\CharSequence as CS;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Entity\LineFragmentSplitter\DiffFragment;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ByWordRt::class)]
class ByWordRtTest extends TestCase
{
    /**
     * @throws DiffToBigException
     */
    public function testCompareAndSplitDefaultPolicy(): void
    {
        $text1 = "unchanged old1 unchanged old2 unchanged";
        $text2 = "unchanged new1 unchanged new2 unchanged";

        $lineBlocks = ByWordRt::compareAndSplit(CS::fromString($text1), CS::fromString($text2), ComparisonPolicy::DEFAULT);
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
    public function testCompareAndSplitTrimWhitespace(): void
    {
        $text1 = "unchanged old1 unchanged old2 unchanged";
        $text2 = "  unchanged new1 unchanged new2 unchanged  ";

        $lineBlocks = ByWordRt::compareAndSplit(CS::fromString($text1), CS::fromString($text2), ComparisonPolicy::TRIM_WHITESPACES);
        static::assertCount(1, $lineBlocks);

        $expected = [
            new DiffFragment(10, 14, 12, 16),
            new DiffFragment(25, 29, 27, 31)
        ];
        static::assertEquals($expected, $lineBlocks[0]->fragments);
    }

    /**
     * @throws DiffToBigException
     */
    public function testCompareAndSplitIgnoreWhitespace(): void
    {
        $text1 = "unchanged old1 unchanged old2 unchanged";
        $text2 = "  unchanged new1   unchanged   new2 unchanged  ";

        $lineBlocks = ByWordRt::compareAndSplit(CS::fromString($text1), CS::fromString($text2), ComparisonPolicy::IGNORE_WHITESPACES);
        static::assertCount(1, $lineBlocks);

        $expected = [
            new DiffFragment(10, 14, 12, 16),
            new DiffFragment(25, 29, 31, 35)
        ];
        static::assertEquals($expected, $lineBlocks[0]->fragments);
    }

    public function testGetInlineChunksTwoWords(): void
    {
        $text     = CS::fromString("public int");
        $expected = [
            new WordChunk($text, 0, 6),
            new WordChunk($text, 7, 10),
        ];

        $chunks = ByWordRt::getInlineChunks($text);
        static::assertEquals($expected, $chunks);
    }

    public function testGetInlineChunksSpecialCharacter(): void
    {
        $text     = CS::fromString("public int codë() {");
        $expected = [
            new WordChunk($text, 0, 6),
            new WordChunk($text, 7, 10),
            new WordChunk($text, 11, 15),
        ];

        $chunks = ByWordRt::getInlineChunks($text);
        static::assertEquals($expected, $chunks);

        /** @var WordChunk $chunk */
        $chunk = $chunks[2];
        static::assertSame("codë", $chunk->getContent());
    }

    public function testGetInlineChunksNewLinesA(): void
    {
        $text     = CS::fromString("public {\ntest");
        $expected = [
            new WordChunk($text, 0, 6),
            new NewLineChunk(8),
            new WordChunk($text, 9, 13),
        ];

        $chunks = ByWordRt::getInlineChunks($text);
        static::assertEquals($expected, $chunks);
    }

    public function testGetInlineChunksNewLines(): void
    {
        $text     = CS::fromString("public int codë() {\ntest\n}\n");
        $expected = [
            new WordChunk($text, 0, 6),
            new WordChunk($text, 7, 10),
            new WordChunk($text, 11, 15),
            new NewLineChunk(19),
            new WordChunk($text, 20, 24),
            new NewLineChunk(24),
            new NewLineChunk(26),
        ];

        $chunks = ByWordRt::getInlineChunks($text);
        static::assertEquals($expected, $chunks);
    }

    /**
     * @throws DiffToBigException
     */
    public function testComparePunctuation2Side(): void
    {
        $text1  = CS::fromString("foo,bar(test)");
        $text21 = CS::fromString("foo,bar");
        $text22 = CS::fromString("(test)");

        [$iterable1, $iterable2] = ByWordRt::comparePunctuation2Side($text1, $text21, $text22);

        $left  = iterator_to_array($iterable1->changes());
        $right = iterator_to_array($iterable2->changes());

        static::assertEquals([new Range(0, 3, 0, 3), new Range(4, 13, 4, 7)], $left);
        static::assertEquals([new Range(0, 7, 0, 0), new Range(8, 12, 1, 5)], $right);
    }

    public function testConvertIntoDiffFragments(): void
    {
        $changeA       = new Change(1, 1, 2, 4);
        $changeB       = new Change(3, 5, 2, 1);
        $changeA->link = $changeB;

        $changeIterator = new DiffChangeDiffIterable($changeA, 10, 20);
        $fragments      = ByWordRt::convertIntoDiffFragments($changeIterator);

        $expected = [
            new DiffFragment(1, 3, 1, 5),
            new DiffFragment(3, 5, 5, 6)
        ];
        static::assertEquals($expected, $fragments);
    }

    public function testCountLines(): void
    {
        static::assertSame(0, ByWordRt::countNewlines([]));

        $text   = CS::fromString('text');
        $chunks = [
            new WordChunk($text, 5, 6),
            new WordChunk($text, 5, 6),
            new NewLineChunk(35),
            new WordChunk($text, 5, 6),
            new NewLineChunk(35),
        ];

        static::assertSame(2, ByWordRt::countNewlines($chunks));
    }
}
