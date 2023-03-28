<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Util\Strings;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Strings::class)]
class StringsTest extends TestCase
{
    public function testEqualsTrimWhitespaces(): void
    {
        $textA = CharSequence::fromString("  foobar  ");
        $textB = CharSequence::fromString("foobar");
        $textC = CharSequence::fromString("FoobaR");

        static::assertTrue(Strings::equalsTrimWhitespaces($textA, $textB));
        static::assertTrue(Strings::equalsTrimWhitespaces($textB, $textA));
        static::assertFalse(Strings::equalsTrimWhitespaces($textA, $textC));
        static::assertFalse(Strings::equalsTrimWhitespaces($textC, $textA));
        static::assertTrue(Strings::equalsTrimWhitespaces($textB, $textC, 1, 5, 1, 5));
    }

    public function testEqualsIgnoreWhitespaces(): void
    {
        $textA = CharSequence::fromString(" f o o b a r ");
        $textB = CharSequence::fromString("foobar");
        $textC = CharSequence::fromString("FoobaR");

        static::assertTrue(Strings::equalsIgnoreWhitespaces(null, null));
        static::assertFalse(Strings::equalsIgnoreWhitespaces(null, $textA));
        static::assertTrue(Strings::equalsIgnoreWhitespaces($textA, $textB));
        static::assertTrue(Strings::equalsIgnoreWhitespaces($textB, $textA));
        static::assertFalse(Strings::equalsIgnoreWhitespaces($textA, $textC));
        static::assertTrue(Strings::equalsIgnoreWhitespaces($textA, $textC, 2, 11, 1, 5));
    }

    public function testEqualsIgnoreWhitespacesEqualStart(): void
    {
        $textA = CharSequence::fromString("foobar   bar");
        $textB = CharSequence::fromString("foobar");
        $textC = CharSequence::fromString("foobar   ");

        static::assertFalse(Strings::equalsIgnoreWhitespaces($textA, $textB));
        static::assertFalse(Strings::equalsIgnoreWhitespaces($textB, $textA));
        static::assertTrue(Strings::equalsIgnoreWhitespaces($textB, $textC));
    }

    public function testEqualsCaseSensitive(): void
    {
        $textA = CharSequence::fromString("foobar");
        $textB = CharSequence::fromString("foobar");
        $textC = CharSequence::fromString("FoobaR");

        static::assertTrue(Strings::equalsCaseSensitive($textA, $textA));
        static::assertFalse(Strings::equalsCaseSensitive(null, $textA));
        static::assertTrue(Strings::equalsCaseSensitive($textA, $textB));
        static::assertTrue(Strings::equalsCaseSensitive($textB, $textA));
        static::assertFalse(Strings::equalsCaseSensitive($textA, $textB, 1));
        static::assertFalse(Strings::equalsCaseSensitive($textA, $textC));
    }

    public function testEqualsCaseSensitiveSubstring(): void
    {
        $text1 = CharSequence::fromString("  foo  trim");
        $text2 = CharSequence::fromString("  bar trim");

        static::assertTrue(Strings::equalsCaseSensitive($text1, $text2, 7, 11, 6, 10));
    }
}
