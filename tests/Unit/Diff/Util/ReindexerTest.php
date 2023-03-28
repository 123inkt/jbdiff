<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Util;

use DR\JBDiff\Diff\Util\Reindexer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Reindexer::class)]
class ReindexerTest extends TestCase
{
    public function testDiscardUnique(): void
    {
        $reindexer = new Reindexer();
        $result    = $reindexer->discardUnique([2, 1, 3, 6, 7], [3, 4, 1]);

        static::assertSame([[1, 3], [3, 1]], $result);
    }
}
