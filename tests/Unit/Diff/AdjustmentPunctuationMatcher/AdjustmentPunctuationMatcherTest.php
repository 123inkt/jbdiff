<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\AdjustmentPunctuationMatcher;

use DR\JBDiff\Diff\AdjustmentPunctuationMatcher\AdjustmentPunctuationMatcher;
use DR\JBDiff\Diff\Comparison\Iterables\SubiterableDiffIterable;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdjustmentPunctuationMatcher::class)]
class AdjustmentPunctuationMatcherTest extends TestCase
{
    /**
     * @throws DiffToBigException
     */
    public function testBuild(): void
    {
        $text1 = CharSequence::fromString("        switch (\$strategy) {\n");
        $text2 = CharSequence::fromString("        return match (\$strategy) {\n");

        $words1 = [
            new WordChunk($text1, 8, 14),
            new WordChunk($text1, 17, 25),
            new NewLineChunk(28),
        ];
        $words2 = [
            new WordChunk($text2, 8, 14),
            new WordChunk($text2, 15, 20),
            new WordChunk($text2, 23, 31),
            new NewLineChunk(34),
        ];

        $startShift1 = 0;
        $startShift2 = 0;

        $ranges = [
            new Range(1, 3, 2, 4),
            new Range(4, 6, 4, 6),
            new Range(8, 15, 6, 13),
            new Range(16, 18, 13, 15),
            new Range(20, 28, 15, 23),
            new Range(29, 35, 23, 29),
            new Range(37, 38, 29, 30)
        ];

        $unchangedIterable = DiffIterableUtil::fair(DiffIterableUtil::createUnchanged($ranges, 38, 30));
        $changeIterator    = DiffIterableUtil::fair(new SubiterableDiffIterable($unchangedIterable, 0, 3, 0, 4));

        $matcher            = new AdjustmentPunctuationMatcher($text1, $text2, $words1, $words2, $startShift1, $startShift2, $changeIterator);
        $delimitersIterable = $matcher->build();

        static::assertSame(29, $delimitersIterable->getLength1());
        static::assertSame(35, $delimitersIterable->getLength2());

        $expected = [
            new Range(0, 15, 0, 21),
            new Range(26, 27, 32, 33)
        ];

        $changes = iterator_to_array($delimitersIterable->changes());
        static::assertEquals($expected, $changes);
    }

    /**
     * @throws DiffToBigException
     */
    public function testBuildComplexRange(): void
    {
        $text1 = CharSequence::fromString(
            "        switch (\$strategy) {
            case RateLimiterConfig::FIXED_WINDOW:
                return new FixedWindow(\$this->redisService->getConnection(), \$config);
            case RateLimiterConfig::SLIDING_WINDOW:
                return new SlidingWindow(\$this->redisService->getConnection(), \$config);
            default:
                throw new RuntimeException('Invalid Strategy name.', RuntimeException::UNKNOWN);
        }"
        );
        $text2 = CharSequence::fromString(
            "        return match (\$strategy) {
            RateLimiterConfig::FIXED_WINDOW   => new FixedWindow(\$this->redisService->getConnection(), \$config),
            RateLimiterConfig::SLIDING_WINDOW => new SlidingWindow(\$this->redisService->getConnection(), \$config),
            default                           => throw new RuntimeException('Invalid Strategy name.'),
        };"
        );

        $words1 = [
            new WordChunk($text1, 41, 45),
            new WordChunk($text1, 46, 63),
            new WordChunk($text1, 65, 77),
            new NewLineChunk(78),
            new WordChunk($text1, 95, 101),
            new WordChunk($text1, 102, 105),
            new WordChunk($text1, 106, 117),
            new WordChunk($text1, 119, 123),
            new WordChunk($text1, 125, 137),
            new WordChunk($text1, 139, 152),
            new WordChunk($text1, 157, 163),
            new NewLineChunk(165),
        ];
        $words2 = [
            new WordChunk($text2, 47, 64),
            new WordChunk($text2, 66, 78),
            new WordChunk($text2, 84, 87),
            new WordChunk($text2, 88, 99),
            new WordChunk($text2, 101, 105),
            new WordChunk($text2, 107, 119),
            new WordChunk($text2, 121, 134),
            new WordChunk($text2, 139, 145),
            new NewLineChunk(147),
        ];

        $subtext1    = "            case RateLimiterConfig::FIXED_WINDOW:
                return new FixedWindow(\$this->redisService->getConnection(), \$config);
";
        $subtext2    = "            RateLimiterConfig::FIXED_WINDOW   => new FixedWindow(\$this->redisService->getConnection(), \$config),
";
        $startShift1 = 29;
        $startShift2 = 35;

        $ranges = [
            new Range(1, 3, 2, 4),
            new Range(4, 6, 4, 6),
            new Range(8, 15, 6, 13),
            new Range(16, 18, 13, 15),
            new Range(20, 28, 15, 23),
            new Range(29, 35, 23, 29),
            new Range(37, 38, 29, 30)
        ];

        $unchangedIterable = DiffIterableUtil::fair(DiffIterableUtil::createUnchanged($ranges, 38, 30));
        $changeIterator    = DiffIterableUtil::fair(new SubiterableDiffIterable($unchangedIterable, 3, 15, 4, 13));

        $matcher            = new AdjustmentPunctuationMatcher(
            CharSequence::fromString($subtext1),
            CharSequence::fromString($subtext2),
            $words1,
            $words2,
            $startShift1,
            $startShift2,
            $changeIterator
        );
        $delimitersIterable = $matcher->build();

        static::assertSame(137, $delimitersIterable->getLength1());
        static::assertSame(113, $delimitersIterable->getLength2());

        $expected = [
            new Range(0, 17, 0, 12),
            new Range(48, 73, 43, 49),
            new Range(76, 77, 52, 53),
            new Range(126, 127, 102, 103),
            new Range(135, 136, 111, 112),
        ];

        $changes = iterator_to_array($delimitersIterable->changes());
        static::assertEquals($expected, $changes);
    }
}
