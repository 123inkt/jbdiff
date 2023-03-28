<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Util\Character;
use IntlChar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Character::class)]
class CharacterTest extends TestCase
{
    public function testCharCount(): void
    {
        static::assertSame(2, Character::charCount(65536));
        static::assertSame(2, Character::charCount(65537));
        static::assertSame(1, Character::charCount(65535));
    }

    public function testAlpha(): void
    {
        static::assertTrue(Character::isAlpha(IntlChar::ord("a")));
        static::assertTrue(Character::isAlpha(IntlChar::ord("_")));
        static::assertFalse(Character::isAlpha(IntlChar::ord(" ")));
        static::assertFalse(Character::isAlpha(IntlChar::ord("\n")));
        static::assertFalse(Character::isAlpha(IntlChar::ord("$")));
    }

    public function testIsContinuousScript(): void
    {
        // ascii character
        static::assertFalse(Character::isContinuousScript(127));

        // non continuous script
        static::assertFalse(Character::isContinuousScript(170));
        static::assertFalse(Character::isContinuousScript(181));

        // continuous script
        static::assertTrue(Character::isContinuousScript(65600));
    }

    #[DataProvider('punctuationDataProvider')]
    public function testIsPunctuation(string $char, bool $expected): void
    {
        $codepoint = IntlChar::ord($char);
        static::assertSame($expected, Character::IS_PUNCTUATION_CODE_POINT[$codepoint] ?? false);
    }

    public function testIsLeadingTrailingSpace(): void
    {
        static::assertFalse(Character::isLeadingTrailingSpace(CharSequence::fromString("a"), -1));
        static::assertTrue(Character::isLeadingTrailingSpace(CharSequence::fromString(" "), 0));

        static::assertTrue(Character::isLeadingTrailingSpace(CharSequence::fromString("a \n b"), 1));
        static::assertFalse(Character::isLeadingTrailingSpace(CharSequence::fromString("a"), 1));
    }

    public function testIsLeadingSpace(): void
    {
        static::assertFalse(Character::isLeadingSpace(CharSequence::fromString("a"), -1));
        static::assertFalse(Character::isLeadingSpace(CharSequence::fromString("a"), 0));
        static::assertFalse(Character::isLeadingSpace(CharSequence::fromString("a"), 1));

        static::assertTrue(Character::isLeadingSpace(CharSequence::fromString(" "), 0));
        static::assertTrue(Character::isLeadingSpace(CharSequence::fromString("   a"), 2));
        static::assertTrue(Character::isLeadingSpace(CharSequence::fromString("a\n b"), 2));
        static::assertFalse(Character::isLeadingSpace(CharSequence::fromString("a b"), 1));
    }

    public function testIsTrailingSpace(): void
    {
        static::assertFalse(Character::isTrailingSpace(CharSequence::fromString("a"), -1));
        static::assertFalse(Character::isTrailingSpace(CharSequence::fromString("a"), 0));
        static::assertFalse(Character::isTrailingSpace(CharSequence::fromString("a"), 1));

        static::assertTrue(Character::isTrailingSpace(CharSequence::fromString(" "), 0));
        static::assertTrue(Character::isTrailingSpace(CharSequence::fromString("a   "), 1));
        static::assertTrue(Character::isTrailingSpace(CharSequence::fromString("a \nb"), 1));
        static::assertFalse(Character::isTrailingSpace(CharSequence::fromString("a b"), 1));
    }

    /**
     * @return array<array<int|string>>
     */
    public static function punctuationDataProvider(): array
    {
        return [
            "0"  => ["0", false],
            "a"  => ["a", false],
            " "  => [" ", false],
            "!"  => ["!", true],
            "\"" => ["\"", true],
            "#"  => ["#", true],
            "$"  => ["$", true],
            "%"  => ["%", true],
            "&"  => ["&", true],
            "'"  => ["'", true],
            "("  => ["(", true],
            ")"  => [")", true],
            "*"  => ["*", true],
            "+"  => ["+", true],
            ","  => [",", true],
            "-"  => ["-", true],
            "."  => [".", true],
            "/"  => ["/", true],
            ":"  => [":", true],
            ";"  => [";", true],
            "<"  => ["<", true],
            "="  => ["=", true],
            ">"  => [">", true],
            "?"  => ["?", true],
            "@"  => ["@", true],
            "["  => ["[", true],
            "\\" => ["\\", true],
            "]"  => ["]", true],
            "^"  => ["^", true],
            "_"  => ["_", false],
            "`"  => ["`", true],
            "{"  => ["{", true],
            "|"  => ["|", true],
            "}"  => ["}", true],
            "~"  => ["~", true],
        ];
    }
}
