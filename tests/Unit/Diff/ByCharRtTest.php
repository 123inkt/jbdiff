<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff;

use DR\JBDiff\Diff\ByCharRt;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\Character\CodePointsOffsets;
use DR\JBDiff\Entity\Range;
use IntlChar;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ByCharRt::class)]
class ByCharRtTest extends TestCase
{
    /**
     * @throws DiffToBigException
     */
    public function testComparePunctuation(): void
    {
        // strings should match on `.:`
        $text1  = CharSequence::fromString("a;b.:cd");
        $text2  = CharSequence::fromString("ab.:c{d}");
        $result = ByCharRt::comparePunctuation($text1, $text2);

        $changes = iterator_to_array($result->changes());

        $expected = [
            new Range(0, 3, 0, 2),
            new Range(5, 7, 4, 8)
        ];
        static::assertEquals($expected, $changes);
    }

    /**
     * @throws DiffToBigException
     */
    public function testComparePunctuationNoMatches(): void
    {
        $text1  = CharSequence::fromString("a;b.cd");
        $text2  = CharSequence::fromString("ab:c{d}");
        $result = ByCharRt::comparePunctuation($text1, $text2);

        $changes = iterator_to_array($result->changes());

        $expected = [new Range(0, 6, 0, 7)];
        static::assertEquals($expected, $changes);
    }

    public function testGetPunctuationChars(): void
    {
        $text    = CharSequence::fromString("a;b.c_d");
        $offsets = ByCharRt::getPunctuationChars($text);

        $expected = new CodePointsOffsets([IntlChar::ord(';'), IntlChar::ord('.')], [1, 3]);
        static::assertEquals($expected, $offsets);
    }
}
