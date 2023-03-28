<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\AdjustmentPunctuationMatcher;

use DR\JBDiff\Diff\AdjustmentPunctuationMatcher\AbstractChangeBuilder;
use DR\JBDiff\Diff\AdjustmentPunctuationMatcher\ChangeBuilder;
use DR\JBDiff\Entity\Range;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ChangeBuilder::class)]
#[CoversClass(AbstractChangeBuilder::class)]
class ChangeBuilderTest extends TestCase
{
    public function testMarkEqualShouldSkipZeroRange(): void
    {
        $changeBuilder = new ChangeBuilder(5, 6);
        $changeBuilder->markEqual(1, 2, 1, 2);

        $result = iterator_to_array($changeBuilder->finish()->changes());
        static::assertEquals([new Range(0, 5, 0, 6)], $result);
    }

    public function testMarkEqual(): void
    {
        $changeBuilder = new ChangeBuilder(5, 6);
        $changeBuilder->markEqual(1, 1, 2, 2);

        $result = iterator_to_array($changeBuilder->finish()->changes());
        static::assertEquals([new Range(0, 1, 0, 1), new Range(2, 5, 2, 6)], $result);
    }

    public function testMarkEqualCount(): void
    {
        $changeBuilder = new ChangeBuilder(5, 6);
        $changeBuilder->markEqualCount(1, 1, 2);

        $result = iterator_to_array($changeBuilder->finish()->changes());
        static::assertEquals([new Range(0, 1, 0, 1), new Range(3, 5, 3, 6)], $result);
    }

    public function testGetIndex(): void
    {
        $changeBuilder = new ChangeBuilder(5, 6);
        static::assertSame(0, $changeBuilder->getIndex1());
        static::assertSame(0, $changeBuilder->getIndex2());

        $changeBuilder->markEqual(1, 1, 2, 2);
        static::assertSame(2, $changeBuilder->getIndex1());
        static::assertSame(2, $changeBuilder->getIndex2());
    }
}
