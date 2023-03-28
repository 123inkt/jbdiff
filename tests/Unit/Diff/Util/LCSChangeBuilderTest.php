<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Util;

use DR\JBDiff\Diff\Util\LCSChangeBuilder;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LCSChangeBuilder::class)]
class LCSChangeBuilderTest extends TestCase
{
    public function testChangeBuilder(): void
    {
        $builder = new LCSChangeBuilder(1);

        $builder->addChange(5, 7);
        $builder->addEqual(2);
        $builder->addChange(7, 10);

        $change = $builder->getFirstChange();
        static::assertSame(1, $change->line0);
        static::assertSame(1, $change->line1);
        static::assertSame(5, $change->deleted);
        static::assertSame(7, $change->inserted);

        $change = $change->link;
        static::assertSame(8, $change->line0);
        static::assertSame(10, $change->line1);
        static::assertSame(7, $change->deleted);
        static::assertSame(10, $change->inserted);
        static::assertNull($change->link);
    }
}
