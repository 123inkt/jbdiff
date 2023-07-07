<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Util;

use DR\JBDiff\Entity\Character\CharSequenceInterface;
use IntlChar;
use function count;
use function dirname;

class Character
{
    public const  MIN_SUPPLEMENTARY_CODE_POINT = 0x010000;
    public const  IS_WHITESPACE                = ["\n" => true, "\t" => true, " " => true];
    // !"#$%&'()*+,-./:;<=>?@[\]^`{|}~
    public const  IS_PUNCTUATION_CODE_POINT = [
        33  => true,
        34  => true,
        35  => true,
        36  => true,
        37  => true,
        38  => true,
        39  => true,
        40  => true,
        41  => true,
        42  => true,
        43  => true,
        44  => true,
        45  => true,
        46  => true,
        47  => true,
        58  => true,
        59  => true,
        60  => true,
        61  => true,
        62  => true,
        63  => true,
        64  => true,
        91  => true,
        92  => true,
        93  => true,
        94  => true,
        96  => true,
        123 => true,
        124 => true,
        125 => true,
        126 => true,
    ];
    private const IS_WHITESPACE_CODE_POINT  = [9 => true, 10 => true, 32 => true];

    public static function charCount(int $codePoint): int
    {
        return $codePoint >= self::MIN_SUPPLEMENTARY_CODE_POINT ? 2 : 1;
    }

    public static function isAlpha(int $codePoint): bool
    {
        return (self::IS_WHITESPACE_CODE_POINT[$codePoint] ?? false) === false
            && (self::IS_PUNCTUATION_CODE_POINT[$codePoint] ?? false) === false;
    }

    public static function isContinuousScript(int $codePoint): bool
    {
        if ($codePoint < 128 || IntlChar::isdigit($codePoint)) {
            return false;
        }

        static $table = null;
        $table ??= require dirname(__DIR__, 2) . '/resources/NonContinuousScriptLookupTable.php';

        return $table[$codePoint] ?? true;
    }

    public static function isWhiteSpace(string $char): bool
    {
        return self::IS_WHITESPACE[$char] ?? false;
    }

    public static function isLeadingTrailingSpace(CharSequenceInterface $text, int $start): bool
    {
        return self::isLeadingSpace($text, $start) || self::isTrailingSpace($text, $start);
    }

    public static function isLeadingSpace(CharSequenceInterface $text, int $start): bool
    {
        $chars = $text->chars();
        if ($start < 0 || $start >= count($chars) || self::isWhiteSpace($chars[$start]) === false) {
            return false;
        }

        --$start;
        while ($start >= 0) {
            $char = $chars[$start];
            if ($char === "\n") {
                return true;
            }
            if (self::isWhiteSpace($char) === false) {
                return false;
            }
            --$start;
        }

        return true;
    }

    public static function isTrailingSpace(CharSequenceInterface $text, int $end): bool
    {
        $chars = $text->chars();
        $len   = count($chars);
        if ($end < 0 || $end >= $len || self::isWhiteSpace($chars[$end]) === false) {
            return false;
        }

        while ($end < $len) {
            $char = $chars[$end];
            if ($char === "\n") {
                return true;
            }
            if (self::isWhiteSpace($char) === false) {
                return false;
            }
            ++$end;
        }

        return true;
    }
}
