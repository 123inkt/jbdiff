<?php
declare(strict_types=1);

namespace DR\JBDiff;

use DR\JBDiff\Entity\LineFragmentSplitter\LineBlock;
use IteratorAggregate;
use Traversable;
use function mb_strlen;
use function mb_substr;

/**
 * @implements IteratorAggregate<array{0: self::TEXT_*, 1: string}>
 */
class LineBlockTextIterator implements IteratorAggregate
{
    public const TEXT_REMOVED          = 1;
    public const TEXT_UNCHANGED_BEFORE = 2;  // before, after are different when ignore whitespace is enabled
    public const TEXT_UNCHANGED_AFTER  = 3;
    public const TEXT_ADDED            = 4;

    /**
     * @param LineBlock[] $blocks
     * @param bool        $splitOnNewlines if true, instead of receiving a string with a \n inside, there will be a specific part with `\n` only.
     */
    public function __construct(
        private readonly string $text1,
        private readonly string $text2,
        private readonly array $blocks,
        private readonly bool $splitOnNewlines = false
    ) {
    }

    /**
     * @return Traversable<array{0: self::TEXT_*, 1: string}>
     */
    public function getIterator(): Traversable
    {
        $previousEndOffset1 = 0;
        $previousEndOffset2 = 0;
        foreach ($this->blocks as $block) {
            foreach ($block->fragments as $fragment) {
                $startOffset1 = $block->offsets->start1 + $fragment->getStartOffset1();
                $endOffset1   = $block->offsets->start1 + $fragment->getEndOffset1();
                $startOffset2 = $block->offsets->start2 + $fragment->getStartOffset2();
                $endOffset2   = $block->offsets->start2 + $fragment->getEndOffset2();

                // unchanged text before
                yield from $this->yieldText(self::TEXT_UNCHANGED_BEFORE, $this->text1, $previousEndOffset1, $startOffset1);

                // unchanged text after
                yield from $this->yieldText(self::TEXT_UNCHANGED_AFTER, $this->text2, $previousEndOffset2, $startOffset2);

                // removed text
                yield from $this->yieldText(self::TEXT_REMOVED, $this->text1, $startOffset1, $endOffset1);

                // added text
                yield from $this->yieldText(self::TEXT_ADDED, $this->text2, $startOffset2, $endOffset2);

                // remember the last end offset
                $previousEndOffset1 = $endOffset1;
                $previousEndOffset2 = $endOffset2;
            }
        }

        yield from $this->yieldText(self::TEXT_UNCHANGED_BEFORE, $this->text1, $previousEndOffset1, mb_strlen($this->text1));
        yield from $this->yieldText(self::TEXT_UNCHANGED_AFTER, $this->text2, $previousEndOffset2, mb_strlen($this->text2));
    }

    /**
     * @param self::TEXT_* $type
     *
     * @return Traversable<array{0: self::TEXT_*, 1: string}>
     */
    private function yieldText(int $type, string $text, int $start, int $end): Traversable
    {
        $length = $end - $start;
        if ($length === 0) {
            return;
        }
        $subText = mb_substr($text, $start, $length);
        if ($this->splitOnNewlines === false) {
            yield [$type, $subText];

            return;
        }

        foreach (explode("\n", $subText) as $index => $piece) {
            if ($index > 0) {
                yield [$type, "\n"];
            }

            if ($piece !== '') {
                yield [$type, $piece];
            }
        }
    }
}
