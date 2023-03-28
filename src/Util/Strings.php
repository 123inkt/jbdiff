<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Util;

use DR\JBDiff\Entity\Character\CharSequenceInterface as CharSequence;
use function count;

class Strings
{
    public static function equalsCaseSensitive(
        ?CharSequence $text1,
        ?CharSequence $text2,
        ?int $start1 = null,
        ?int $end1 = null,
        ?int $start2 = null,
        ?int $end2 = null
    ): bool {
        if ($text1 === $text2) {
            return true;
        }
        if ($text1 === null || $text2 === null) {
            return false;
        }

        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        $start1 ??= 0;
        $start2 ??= 0;

        $end1 ??= count($chars1);
        $end2 ??= count($chars2);

        // strings not the same length
        if ($end1 - $start1 !== $end2 - $start2) {
            return false;
        }

        for (; $start1 < $end1 && $start2 < $end2; ++$start1, ++$start2) {
            if ($chars1[$start1] !== $chars2[$start2]) {
                return false;
            }
        }

        return true;
    }

    public static function equalsIgnoreWhitespaces(
        ?CharSequence $text1,
        ?CharSequence $text2,
        ?int $start1 = null,
        ?int $end1 = null,
        ?int $start2 = null,
        ?int $end2 = null,
    ): bool {
        if ($text1 === null && $text2 === null) {
            return true;
        }
        if ($text1 === null || $text2 === null) {
            return false;
        }

        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        $len1 = $end1 ?? count($chars1);
        $len2 = $end2 ?? count($chars2);

        $index1 = $start1 ?? 0;
        $index2 = $start2 ?? 0;

        while ($index1 < $len1 && $index2 < $len2) {
            if ($chars1[$index1] === $chars2[$index2]) {
                ++$index1;
                ++$index2;
                continue;
            }

            $skipped = false;
            while ($index1 !== $len1 && (Character::IS_WHITESPACE[$chars1[$index1]] ?? false)) {
                $skipped = true;
                ++$index1;
            }
            while ($index2 !== $len2 && (Character::IS_WHITESPACE[$chars2[$index2]] ?? false)) {
                $skipped = true;
                ++$index2;
            }

            if ($skipped === false) {
                return false;
            }
        }

        for (; $index1 !== $len1; ++$index1) {
            if ((Character::IS_WHITESPACE[$chars1[$index1]] ?? false) === false) {
                return false;
            }
        }
        for (; $index2 !== $len2; ++$index2) {
            if ((Character::IS_WHITESPACE[$chars2[$index2]] ?? false) === false) {
                return false;
            }
        }

        return true;
    }

    public static function equalsTrimWhitespaces(
        CharSequence $text1,
        CharSequence $text2,
        ?int $start1 = null,
        ?int $end1 = null,
        ?int $start2 = null,
        ?int $end2 = null
    ): bool {
        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        $start1 ??= 0;
        $start2 ??= 0;

        $end1 ??= count($chars1);
        $end2 ??= count($chars2);

        while ($start1 < $end1) {
            if ((Character::IS_WHITESPACE[$chars1[$start1]] ?? false) === false) {
                break;
            }
            ++$start1;
        }

        while ($start1 < $end1) {
            if ((Character::IS_WHITESPACE[$chars1[$end1 - 1]] ?? false) === false) {
                break;
            }
            --$end1;
        }

        while ($start2 < $end2) {
            if ((Character::IS_WHITESPACE[$chars2[$start2]] ?? false) === false) {
                break;
            }
            ++$start2;
        }

        while ($start2 < $end2) {
            if ((Character::IS_WHITESPACE[$chars2[$end2 - 1]] ?? false) === false) {
                break;
            }
            --$end2;
        }

        return self::equalsCaseSensitive($text1, $text2, $start1, $end1, $start2, $end2);
    }
}
