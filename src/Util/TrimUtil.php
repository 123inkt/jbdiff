<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Util;

use DR\JBDiff\Entity\Character\CharSequenceInterface as CharSequence;
use DR\JBDiff\Entity\Range;

class TrimUtil
{
    /**
     * @template T
     *
     * @param array<int, T> $data1
     * @param array<int, T> $data2
     */
    public static function expandForward(array $data1, array $data2, int $start1, int $start2, int $end1, int $end2): int
    {
        return self::expandForwardCallback($start1, $start2, $end1, $end2, fn($index1, $index2) => $data1[$index1] === $data2[$index2]);
    }

    /**
     * @template T
     *
     * @param array<int, T> $data1
     * @param array<int, T> $data2
     */
    public static function expandBackward(array $data1, array $data2, int $start1, int $start2, int $end1, int $end2): int
    {
        return self::expandBackwardCallback($start1, $start2, $end1, $end2, fn($index1, $index2) => $data1[$index1] === $data2[$index2]);
    }

    public static function expandWhitespaces(CharSequence $text1, CharSequence $text2, Range $range): Range
    {
        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        return self::expandIgnored(
            $range->start1,
            $range->start2,
            $range->end1,
            $range->end2,
            static fn($index1, $index2) => $chars1[$index1] === $chars2[$index2],
            static fn($index) => Character::isWhiteSpace($chars1[$index])
        );
    }

    public static function expandWhitespacesForward(CharSequence $text1, CharSequence $text2, int $start1, int $start2, int $end1, int $end2): int
    {
        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        return self::expandIgnoredForward(
            $start1,
            $start2,
            $end1,
            $end2,
            static fn($index1, $index2) => $chars1[$index1] === $chars2[$index2],
            static fn($index) => Character::isWhiteSpace($chars1[$index])
        );
    }

    public static function expandWhitespacesBackward(CharSequence $text1, CharSequence $text2, int $start1, int $start2, int $end1, int $end2): int
    {
        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        return self::expandIgnoredBackward(
            $start1,
            $start2,
            $end1,
            $end2,
            static fn($index1, $index2) => $chars1[$index1] === $chars2[$index2],
            static fn($index) => Character::isWhiteSpace($chars1[$index])
        );
    }

    public static function trimWhitespacesRange(CharSequence $text1, CharSequence $text2, Range $range): Range
    {
        $chars1 = $text1->chars();
        $chars2 = $text2->chars();

        return self::trimRangeCallback(
            $range->start1,
            $range->start2,
            $range->end1,
            $range->end2,
            static fn($index) => Character::isWhiteSpace($chars1[$index]),
            static fn($index) => Character::isWhiteSpace($chars2[$index])
        );
    }

    public static function trimWhitespaceStart(CharSequence $text, int $start, int $end): int
    {
        $chars = $text->chars();

        return self::trimStartCallback($start, $end, static fn($index) => Character::isWhiteSpace($chars[$index]));
    }

    public static function trimWhitespaceEnd(CharSequence $text, int $start, int $end): int
    {
        $chars = $text->chars();

        return self::trimEndCallback($start, $end, static fn($index) => Character::isWhiteSpace($chars[$index]));
    }

    public static function isEqualsIgnoreWhitespacesRange(CharSequence $text1, CharSequence $text2, Range $range): bool
    {
        return Strings::equalsIgnoreWhitespaces(
            $text1,
            $text2,
            $range->start1,
            $range->end1,
            $range->start2,
            $range->end2
        );
    }

    /**
     * @param callable(int, int): bool $equals
     */
    private static function expandForwardCallback(int $start1, int $start2, int $end1, int $end2, callable $equals): int
    {
        $oldStart1 = $start1;
        while ($start1 < $end1 && $start2 < $end2) {
            if ($equals($start1, $start2) === false) {
                break;
            }
            ++$start1;
            ++$start2;
        }

        return $start1 - $oldStart1;
    }

    /**
     * @param callable(int, int): bool $equals
     */
    private static function expandBackwardCallback(int $start1, int $start2, int $end1, int $end2, callable $equals): int
    {
        $oldEnd1 = $end1;
        while ($start1 < $end1 && $start2 < $end2) {
            if ($equals($end1 - 1, $end2 - 1) === false) {
                break;
            }
            --$end1;
            --$end2;
        }

        return $oldEnd1 - $end1;
    }

    /**
     * @param callable(int, int): bool $equals
     * @param callable(int): bool      $ignored1
     */
    private static function expandIgnored(int $start1, int $start2, int $end1, int $end2, callable $equals, callable $ignored1): Range
    {
        $count1 = self::expandIgnoredForward($start1, $start2, $end1, $end2, $equals, $ignored1);
        $start1 += $count1;
        $start2 += $count1;

        $count2 = self::expandIgnoredBackward($start1, $start2, $end1, $end2, $equals, $ignored1);
        $end1   -= $count2;
        $end2   -= $count2;

        return new Range($start1, $end1, $start2, $end2);
    }

    /**
     * @param callable(int, int): bool $equals
     * @param callable(int): bool      $ignored1
     */
    private static function expandIgnoredForward(int $start1, int $start2, int $end1, int $end2, callable $equals, callable $ignored1): int
    {
        $oldStart1 = $start1;
        while ($start1 < $end1 && $start2 < $end2) {
            if (($equals)($start1, $start2) === false) {
                break;
            }
            if (($ignored1)($start1) === false) {
                break;
            }
            ++$start1;
            ++$start2;
        }

        return $start1 - $oldStart1;
    }

    /**
     * @param callable(int, int): bool $equals
     * @param callable(int): bool      $ignored1
     */
    private static function expandIgnoredBackward(int $start1, int $start2, int $end1, int $end2, callable $equals, callable $ignored1): int
    {
        $oldEnd1 = $end1;
        while ($start1 < $end1 && $start2 < $end2) {
            if (($equals)($end1 - 1, $end2 - 1) === false) {
                break;
            }
            if (($ignored1)($end1 - 1) === false) {
                break;
            }
            --$end1;
            --$end2;
        }

        return $oldEnd1 - $end1;
    }

    /**
     * @param callable(int): bool $ignored1
     * @param callable(int): bool $ignored2
     */
    private static function trimRangeCallback(int $start1, int $start2, int $end1, int $end2, callable $ignored1, callable $ignored2): Range
    {
        $start1 = self::trimStartCallback($start1, $end1, $ignored1);
        $end1   = self::trimEndCallback($start1, $end1, $ignored1);

        $start2 = self::trimStartCallback($start2, $end2, $ignored2);
        $end2   = self::trimEndCallback($start2, $end2, $ignored2);

        return new Range($start1, $end1, $start2, $end2);
    }

    /**
     * @param callable(int): bool $ignored
     */
    private static function trimStartCallback(int $start, int $end, callable $ignored): int
    {
        while ($start < $end) {
            if (($ignored)($start) === false) {
                break;
            }
            ++$start;
        }

        return $start;
    }

    /**
     * @param callable(int): bool $ignored
     */
    private static function trimEndCallback(int $start, int $end, callable $ignored): int
    {
        while ($start < $end) {
            if (($ignored)($end - 1) === false) {
                break;
            }
            --$end;
        }

        return $end;
    }
}
