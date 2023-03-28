<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Entity;

use DR\JBDiff\Entity\Change\NullChange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullChange::class)]
class NullChangeTest extends TestCase
{
    public function testIsNull(): void
    {
        $change = new NullChange();
        static::assertTrue($change->isNull());
    }
}
