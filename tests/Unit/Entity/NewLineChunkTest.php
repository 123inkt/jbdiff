<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Chunk\NewLineChunk;
use DR\JBDiff\Entity\Chunk\WordChunk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NewLineChunk::class)]
class NewLineChunkTest extends TestCase
{
    public function testGetOffset1(): void
    {
        $chunk = new NewLineChunk(5);
        static::assertSame(5, $chunk->getOffset1());
    }

    public function testGetOffset2(): void
    {
        $chunk = new NewLineChunk(5);
        static::assertSame(6, $chunk->getOffset2());
    }

    public function testEquals(): void
    {
        $chunkA = new NewLineChunk(5);
        $chunkB = new NewLineChunk(6);
        $chunkC = new WordChunk(CharSequence::fromString('foobar'), 1, 2);

        static::assertTrue($chunkA->equals($chunkA));
        static::assertTrue($chunkA->equals($chunkB));
        static::assertFalse($chunkA->equals($chunkC));
    }
}
