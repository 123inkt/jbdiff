<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Util;

use DR\JBDiff\Diff\Util\Diff;
use DR\JBDiff\Diff\Util\DiffToBigException;
use DR\JBDiff\Entity\Change\NullChange;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Diff::class)]
class DiffTest extends TestCase
{
    /**
     * @throws DiffToBigException
     */
    public function testBuildChangesNullChange(): void
    {
        $left  = [1, 2, 3, 4];
        $right = [1, 2, 3, 4];

        $diff = new Diff();
        static::assertInstanceOf(NullChange::class, $diff->buildChanges($left, $right));
    }

    /**
     * @throws DiffToBigException
     */
    public function testBuildChanges(): void
    {
        $left  = [1, 2, 4, 5];
        $right = [1, 3, 5];

        $diff = new Diff();
        $result = $diff->buildChanges($left, $right);

        static::assertSame(1, $result->line0);
        static::assertSame(1, $result->line1);
        static::assertSame(2, $result->deleted);
        static::assertSame(1, $result->inserted);
    }
}
