<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison;

use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableWrapper;
use DR\JBDiff\Diff\Comparison\Iterables\InvertedDiffIterableWrapper;
use DR\JBDiff\Diff\Comparison\Iterables\RangesDiffIterable;
use DR\JBDiff\Diff\Comparison\LineFragmentSplitter;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Entity\LineFragmentSplitter\WordBlock;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LineFragmentSplitter::class)]
class LineFragmentSplitterTest extends TestCase
{
    public function testRun(): void
    {
        // prepare data
        $text1 = CharSequence::fromString("        switch (\$strategy) {\n            case RateLimiterConfig::FIXED_WINDOW:\n");
        $text2 = CharSequence::fromString("        return match (\$strategy) {\n            RateLimiterConfig::FIXED_WINDOW\n");

        $words1 = [
            new WordChunk($text1, 8, 14),
            new WordChunk($text1, 17, 25),
            new NewLineChunk(28),
            new WordChunk($text1, 41, 45),
            new WordChunk($text1, 46, 63),
            new WordChunk($text1, 65, 77),
            new NewLineChunk(78),
        ];

        $words2 = [
            new WordChunk($text1, 8, 14),
            new WordChunk($text1, 15, 20),
            new WordChunk($text1, 23, 31),
            new NewLineChunk(34),
            new WordChunk($text1, 47, 64),
            new WordChunk($text1, 66, 78),
            new NewLineChunk(78),
        ];

        $iterable = new FairDiffIterableWrapper(
            new InvertedDiffIterableWrapper(new RangesDiffIterable([new Range(1, 3, 2, 4), new Range(4, 7, 4, 7)], 7, 7))
        );

        // expected
        $expected = [
            new WordBlock(new Range(0, 3, 0, 4), new Range(0, 29, 0, 35)),
            new WordBlock(new Range(3, 7, 4, 7), new Range(29, 79, 35, 79))
        ];

        // run test
        $wordBlocks = (new LineFragmentSplitter($text1, $text2, $words1, $words2, $iterable))->run();

        // assert
        static::assertEquals($expected, $wordBlocks);
    }
}
