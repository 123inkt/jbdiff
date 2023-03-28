<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity\Chunk;

use DigitalRevolution\AccessorPairConstraint\AccessorPairAsserter;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordChunk::class)]
class WordChunkTest extends TestCase
{
    use AccessorPairAsserter;

    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(WordChunk::class);
    }

    public function testGetContent(): void
    {
        $chunk = new WordChunk(CharSequence::fromString("foobar"), 1, 4);
        static::assertSame("oob", $chunk->getContent());
    }

    public function testEquals(): void
    {
        $textA = new WordChunk(CharSequence::fromString("foobar"), 1, 4);
        $textB = new WordChunk(CharSequence::fromString("ooba"), 0, 3);
        $textC = new WordChunk(CharSequence::fromString("other"), 0, 3);

        static::assertfalse($textA->equals(new Range(1, 2, 3, 4)));
        static::assertTrue($textA->equals($textA));
        static::assertTrue($textA->equals($textB));
        static::assertFalse($textA->equals($textC));
    }
}
