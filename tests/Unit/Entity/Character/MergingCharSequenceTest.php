<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity\Character;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Character\MergingCharSequence;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MergingCharSequence::class)]
class MergingCharSequenceTest extends TestCase
{
    public function testLength(): void
    {
        $string1    = CharSequence::fromString('Lorem ipsum dolor sit amet');
        $string2    = CharSequence::fromString('consectetur adipiscing elit');
        $mergingSeq = new MergingCharSequence($string1, $string2);

        static::assertSame(strlen('Lorem ipsum dolor sit amet') + strlen('consectetur adipiscing elit'), $mergingSeq->length());
    }

    public function testIsEmpty(): void
    {
        $string1    = CharSequence::fromString("Hello");
        $string2    = CharSequence::fromString('World');
        $mergingSeq = new MergingCharSequence($string1, $string2);

        static::assertFalse($mergingSeq->isEmpty());

        $string3    = CharSequence::fromString('');
        $string4    = CharSequence::fromString('');
        $mergingSeq = new MergingCharSequence($string3, $string4);

        static::assertTrue($mergingSeq->isEmpty());
    }

    public function testCharAt(): void
    {
        $string1    = CharSequence::fromString('Lorem ipsum dolor sit amet');
        $string2    = CharSequence::fromString('consectetur adipiscing elit');
        $mergingSeq = new MergingCharSequence($string1, $string2);

        for ($i = 0; $i < $string1->length(); $i++) {
            static::assertSame($string1->charAt($i), $mergingSeq->charAt($i));
        }

        for ($i = 0; $i < $string2->length(); $i++) {
            static::assertSame($string2->charAt($i), $mergingSeq->charAt($string1->length() + $i));
        }
    }

    public function testChars(): void
    {
        $mergingSeq = static::createSeq('Hello ', 'World');

        static::assertSame(['H', 'e', 'l', 'l', 'o', ' ', 'W', 'o', 'r', 'l', 'd'], $mergingSeq->chars());
    }

    public function testSubSequenceReturnsCorrectSubsequence()
    {
        // Create two CharSequences to merge
        $mergingChars = static::createSeq('abc', 'defg');

        // Test subSequence is from start to finish returns itself
        static::assertSame($mergingChars, $mergingChars->subSequence(0, 7));

        // Test subSequence with start and end within first CharSequence
        $subChars1 = $mergingChars->subSequence(0, 2);
        static::assertInstanceOf(CharSequence::class, $subChars1);
        static::assertSame('ab', (string)$subChars1);

        // Test subSequence with start and end within second CharSequence
        $subChars2 = $mergingChars->subSequence(4, 7);
        static::assertInstanceOf(CharSequence::class, $subChars2);
        static::assertSame('efg', (string)$subChars2);

        // Test subSequence with start before first and end after second CharSequence
        $subChars3 = $mergingChars->subSequence(1, 6);
        static::assertInstanceOf(MergingCharSequence::class, $subChars3);
        static::assertSame('bcdef', (string)$subChars3);

        // Test subSequence with start within first and end after second CharSequence
        $subChars4 = $mergingChars->subSequence(1, 7);
        static::assertInstanceOf(MergingCharSequence::class, $subChars4);
        static::assertSame('bcdefg', (string)$subChars4);
    }

    public function testEquals(): void
    {
        $mergingSeq1 = static::createSeq('Foo', 'Bar');
        $mergingSeq2 = static::createSeq('Foo', 'Bar');
        $mergingSeq3 = static::createSeq('Bar', 'Foo');

        static::assertTrue($mergingSeq1->equals($mergingSeq1));
        static::assertTrue($mergingSeq1->equals($mergingSeq2));
        static::asserTFalse($mergingSeq1->equals($mergingSeq3));
        static::asserTFalse($mergingSeq1->equals(new Range(1, 2, 3, 4)));
    }

    private static function createSeq(string $first, string $second): MergingCharSequence
    {
        return new MergingCharSequence(CharSequence::fromString($first), CharSequence::fromString($second));
    }
}
