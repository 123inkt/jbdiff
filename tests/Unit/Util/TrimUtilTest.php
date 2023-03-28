<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Util;

use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Range;
use DR\JBDiff\Util\TrimUtil;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TrimUtil::class)]
class TrimUtilTest extends TestCase
{
    public function testExpandForward(): void
    {
        // match [AB] from the start
        $data1 = ['a', 'b', 'c', 'd'];
        $data2 = ['d', 'a', 'b', 'f', 'g'];

        static::assertSame(2, TrimUtil::expandForward($data1, $data2, 0, 1, count($data1), count($data2) - 1));
    }

    public function testExpandBackward(): void
    {
        // match [AB] from the end
        $data1 = ['d', 'c', 'b', 'a'];
        $data2 = ['f', 'b', 'a', 'd'];

        static::assertSame(2, TrimUtil::expandBackward($data1, $data2, 0, 0, count($data1), count($data2) - 1));
    }

    public function testExpandWhitespaces(): void
    {
        // ignoring first and last char, able to trim 2 whitespaces from start and end
        $text1 = CharSequence::fromString("y\t  foobar  \ny");
        $text2 = CharSequence::fromString("x\t foobar \nx");
        $range = new Range(1, $text1->length() - 1, 1, $text2->length() - 1);

        $result = TrimUtil::expandWhitespaces($text1, $text2, $range);
        static::assertEquals(new Range(3, 11, 3, 9), $result);
    }

    public function testExpandWhitespacesForward(): void
    {
        // able to trim 2 whitespaces from the start
        $text1 = CharSequence::fromString("\t  foobar");
        $text2 = CharSequence::fromString("\t foobar");

        $result = TrimUtil::expandWhitespacesForward($text1, $text2, 0, 0, $text1->length(), $text2->length());
        static::assertSame(2, $result);
    }

    public function testExpandWhitespacesForwardEqualString(): void
    {
        // able to trim 1 whitespace from the start
        $text1 = CharSequence::fromString(" foobar");
        $text2 = CharSequence::fromString(" foobar");

        $result = TrimUtil::expandWhitespacesForward($text1, $text2, 0, 0, $text1->length(), $text2->length());
        static::assertSame(1, $result);
    }

    public function testExpandWhitespacesBackward(): void
    {
        // able to trim 2 whitespaces from the end
        $text1 = CharSequence::fromString("foobar  \n");
        $text2 = CharSequence::fromString("foobar \n");

        $result = TrimUtil::expandWhitespacesBackward($text1, $text2, 0, 0, $text1->length(), $text2->length());
        static::assertSame(2, $result);
    }

    public function testExpandWhitespacesBackwardEqualString(): void
    {
        // able to trim 1 whitespace from the end
        $text1 = CharSequence::fromString("foobar ");
        $text2 = CharSequence::fromString("foobar ");

        $result = TrimUtil::expandWhitespacesBackward($text1, $text2, 0, 0, $text1->length(), $text2->length());
        static::assertSame(1, $result);
    }

    public function testTrimWhitespaceRange(): void
    {
        $text1 = CharSequence::fromString("x\t foobar \ny");
        $text2 = CharSequence::fromString("x\t foo \ny");
        $range = new Range(1, $text1->length() - 1, 1, $text2->length() - 1);

        $result = TrimUtil::trimWhitespacesRange($text1, $text2, $range);
        static::assertEquals(new Range(3, 9, 3, 6), $result);
    }

    public function testTrimWhitespaceStart(): void
    {
        $text = CharSequence::fromString("x\t foobar");

        static::assertSame(3, TrimUtil::trimWhitespaceStart($text, 1, $text->length()));
    }

    public function testTrimWhitespaceEnd(): void
    {
        $text = CharSequence::fromString("foobar \ny");

        static::assertSame(6, TrimUtil::trimWhitespaceEnd($text, 0, $text->length() - 1));
    }

    public function testIsEqualsIgnoreWhitespacesRange(): void
    {
        $text1 = CharSequence::fromString("x\tf o o b a r\ny");
        $text2 = CharSequence::fromString("foobar");
        $range = new Range(1, $text1->length() - 1, 0, $text2->length());

        static::assertTrue(TrimUtil::isEqualsIgnoreWhitespacesRange($text1, $text2, $range));
    }
}
