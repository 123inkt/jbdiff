<?php
declare(strict_types=1);

namespace DR\JBDiff;

use DR\JBDiff\Diff\ByWordRt;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Character\CharSequence;
use DR\JBDiff\Entity\LineFragmentSplitter\LineBlock;

class JBDiff
{
    /**
     * @return LineBlock[]
     * @throws DiffToBigException
     */
    public function compare(string $text1, string $text2, ComparisonPolicy $policy = ComparisonPolicy::DEFAULT): array
    {
        return ByWordRt::compareAndSplit(CharSequence::fromString($text1), CharSequence::fromString($text2), $policy);
    }

    /**
     * @throws DiffToBigException
     */
    public function compareToIterator(
        string $text1,
        string $text2,
        ComparisonPolicy $policy = ComparisonPolicy::DEFAULT,
        bool $splitOnNewLines = false
    ): LineBlockTextIterator {
        $blocks = ByWordRt::compareAndSplit(CharSequence::fromString($text1), CharSequence::fromString($text2), $policy);

        return new LineBlockTextIterator($text1, $text2, $blocks, $splitOnNewLines);
    }
}
