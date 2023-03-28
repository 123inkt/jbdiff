<?php
// Copyright 2023 Digital Revolution BV (123inkt.nl). Use of this source code is governed by the Apache 2.0 license.
// Copyright 2000-2021 JetBrains s.r.o. Use of this source code is governed by the Apache 2.0 license that can be found in the LICENSE file.
declare(strict_types=1);

namespace DR\JBDiff\Diff;

use DR\JBDiff\Diff\Comparison\Iterables\FairDiffIterableInterface;
use DR\JBDiff\Diff\AdjustmentPunctuationMatcher\ChangeBuilder;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Character\CharSequenceInterface;
use DR\JBDiff\Entity\Character\CodePointsOffsets;
use DR\JBDiff\Util\Character;
use IntlChar;

class ByCharRt
{
    /**
     * @throws DiffToBigException
     */
    public static function comparePunctuation(CharSequenceInterface $text1, CharSequenceInterface $text2): FairDiffIterableInterface
    {
        $chars1 = self::getPunctuationChars($text1);
        $chars2 = self::getPunctuationChars($text2);

        $nonSpaceChanges = DiffIterableUtil::diff($chars1->codePoints, $chars2->codePoints);

        return self::transferPunctuation($chars1, $chars2, $text1, $text2, $nonSpaceChanges);
    }

    public static function getPunctuationChars(CharSequenceInterface $text): CodePointsOffsets
    {
        $codePoints = [];
        $offsets    = [];

        foreach ($text->chars() as $i => $char) {
            $codePoint = IntlChar::ord($char);
            if (Character::IS_PUNCTUATION_CODE_POINT[$codePoint] ?? false) {
                $codePoints[] = $codePoint;
                $offsets[]    = $i;
            }
        }

        return new CodePointsOffsets($codePoints, $offsets);
    }

    private static function transferPunctuation(
        CodePointsOffsets $chars1,
        CodePointsOffsets $chars2,
        CharSequenceInterface $text1,
        CharSequenceInterface $text2,
        FairDiffIterableInterface $changes
    ): FairDiffIterableInterface {
        $builder = new ChangeBuilder($text1->length(), $text2->length());

        foreach ($changes->unchanged() as $range) {
            $count = $range->end1 - $range->start1;
            for ($i = 0; $i < $count; $i++) {
                // Punctuation code points are always 1 char
                $offset1 = $chars1->offsets[$range->start1 + $i];
                $offset2 = $chars2->offsets[$range->start2 + $i];
                $builder->markEqualCount($offset1, $offset2);
            }
        }

        return DiffIterableUtil::fair($builder->finish());
    }
}
