<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison;

use DR\JBDiff\Diff\ByWordRt;
use DR\JBDiff\Diff\Comparison\AbstractChunkOptimizer;
use DR\JBDiff\Diff\Comparison\WordChunkOptimizer;
use DR\JBDiff\Diff\DiffIterableUtil;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordChunkOptimizer::class)]
#[CoversClass(AbstractChunkOptimizer::class)]
class WordChunkOptimizerTest extends TestCase
{
    public function testOptimizer(): void
    {
        $text1 = self::getText1();
        $text2 = self::getText2();

        $words1 = ByWordRt::getInlineChunks($text1);
        $words2 = ByWordRt::getInlineChunks($text2);

        $wordChanges = DiffIterableUtil::diff($words1, $words2);
        $wordChanges = (new WordChunkOptimizer($words1, $words2, $text1, $text2, $wordChanges))->build();

        static::assertSame(38, $wordChanges->getLength1());
        static::assertSame(30, $wordChanges->getLength2());

        $expected = [
            new Range(1, 3, 2, 4),
            new Range(4, 6, 4, 6),
            new Range(8, 15, 6, 13),
            new Range(16, 18, 13, 15),
            new Range(20, 28, 15, 23),
            new Range(29, 35, 23, 29),
            new Range(37, 38, 29, 30),
        ];

        $unchangedChanges = iterator_to_array($wordChanges->unchanged());
        static::assertEquals($expected, $unchangedChanges);
    }

    private static function getText1(): CharSequence
    {
        return CharSequence::fromString(
            "        switch (\$strategy) {
            case RateLimiterConfig::FIXED_WINDOW:
                return new FixedWindow(\$this->redisService->getConnection(), \$config);
            case RateLimiterConfig::SLIDING_WINDOW:
                return new SlidingWindow(\$this->redisService->getConnection(), \$config);
            default:
                throw new RuntimeException('Invalid Strategy name.', RuntimeException::UNKNOWN);
        }"
        );
    }

    private static function getText2(): CharSequence
    {
        return CharSequence::fromString(
            "        return match (\$strategy) {
            RateLimiterConfig::FIXED_WINDOW   => new FixedWindow(\$this->redisService->getConnection(), \$config),
            RateLimiterConfig::SLIDING_WINDOW => new SlidingWindow(\$this->redisService->getConnection(), \$config),
            default                           => throw new RuntimeException('Invalid Strategy name.'),
        };"
        );
    }
}
