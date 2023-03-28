<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity\Character;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CharSequence::class)]
class CharSequenceTest extends TestCase
{
    public function testLengthReturnsNumberOfCharacters(): void
    {
        $charSequence = CharSequence::fromString('hello');
        static::assertEquals(5, $charSequence->length());
    }

    public function testCharAtReturnsCharacterAtIndex(): void
    {
        $charSequence = CharSequence::fromString('hello');
        static::assertEquals('h', $charSequence->charAt(0));
        static::assertEquals('o', $charSequence->charAt(4));
    }

    public function testCharsReturnsArrayOfCharacters(): void
    {
        $charSequence = CharSequence::fromString('hello');
        static::assertEquals(['h', 'e', 'l', 'l', 'o'], $charSequence->chars());
    }

    public function testIsEmptyReturnsTrueForEmptySequence(): void
    {
        $charSequence = CharSequence::fromString('');
        static::assertTrue($charSequence->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptySequence(): void
    {
        $charSequence = CharSequence::fromString('hello');
        static::assertFalse($charSequence->isEmpty());
    }

    public function testSubSequenceReturnsSubstring(): void
    {
        $charSequence = CharSequence::fromString('hello');
        $subSequence  = $charSequence->subSequence(1, 4);
        static::assertEquals('ell', $subSequence->__toString());
    }

    public function testToStringReturnsStringRepresentation(): void
    {
        $charSequence = CharSequence::fromString('hello');
        static::assertEquals('hello', $charSequence->__toString());
    }

    public function testFromStringReturnsNewCharSequence(): void
    {
        $charSequence = CharSequence::fromString('hello');
        static::assertInstanceOf(CharSequence::class, $charSequence);
        static::assertEquals('hello', $charSequence->__toString());
    }

    public function testEqualsReturnsTrueForEqualCharSequences(): void
    {
        $charSequence1 = CharSequence::fromString('hello');
        $charSequence2 = CharSequence::fromString('hello');
        static::assertTrue($charSequence1->equals($charSequence2));
    }

    public function testEqualsReturnsFalseForDifferentCharSequences(): void
    {
        $charSequence1 = CharSequence::fromString('hello');
        $charSequence2 = CharSequence::fromString('world');
        static::assertFalse($charSequence1->equals($charSequence2));
    }

    public function testEqualsReturnsFalseForDifferentObjects(): void
    {
        $charSequence1 = CharSequence::fromString('hello');
        $charSequence2 = new Range(1, 2, 3, 4);
        static::assertFalse($charSequence1->equals($charSequence2));
    }
}
