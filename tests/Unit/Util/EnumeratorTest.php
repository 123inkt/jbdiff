<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Chunk\WordChunk;
use DR\JBDiff\Util\Enumerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Enumerator::class)]
class EnumeratorTest extends TestCase
{
    public function testEnumerateObjects(): void
    {
        $text1 = CharSequence::fromString('public function int');
        $text2 = CharSequence::fromString('public int test');

        $wordA = new WordChunk($text1, 0, 6);
        $wordB = new WordChunk($text1, 7, 15);
        $wordC = new WordChunk($text1, 16, 19);

        $wordD = new WordChunk($text2, 0, 6);
        $wordE = new WordChunk($text2, 7, 10);
        $wordF = new WordChunk($text2, 11, 15);

        $enumerator = new Enumerator();
        $resultA    = $enumerator->enumerate([$wordA, $wordB, $wordC], 1, 0);
        $resultB    = $enumerator->enumerate([$wordD, $wordE, $wordF], 1, 0);

        static::assertSame([1, 2], $resultA);
        static::assertSame([2, 3], $resultB);
    }

    public function testEnumerateInt(): void
    {
        $valueA = 10;
        $valueB = 20;
        $valueC = 30;

        $valueD = 10;
        $valueE = 40;
        $valueF = 60;

        $enumerator = new Enumerator();
        $resultA    = $enumerator->enumerate([$valueA, $valueB, $valueC], 0, 0);
        $resultB    = $enumerator->enumerate([$valueD, $valueE, $valueF], 0, 0);

        static::assertSame([1, 2, 3], $resultA);
        static::assertSame([1, 4, 5], $resultB);
    }
}
