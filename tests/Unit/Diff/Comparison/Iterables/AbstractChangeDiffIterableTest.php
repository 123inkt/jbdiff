<?php
declare(strict_types=1);

namespace DR\JBDiff\Tests\Unit\Diff\Comparison\Iterables;

use DR\JBDiff\Diff\Comparison\Iterables\AbstractChangeDiffIterable;
use DR\JBDiff\Diff\Comparison\Iterables\ChangeIterableInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractChangeDiffIterable::class)]
class AbstractChangeDiffIterableTest extends TestCase
{
    private AbstractChangeDiffIterable         $iterable;
    private ChangeIterableInterface&MockObject $changeIterable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->changeIterable = $this->createMock(ChangeIterableInterface::class);
        $this->iterable       = new class ($this->changeIterable, 10, 20) extends AbstractChangeDiffIterable {
            public function __construct(private readonly ChangeIterableInterface $iterable, int $length1, int $length2)
            {
                parent::__construct($length1, $length2);
            }

            protected function createChangeIterable(): ChangeIterableInterface
            {
                return $this->iterable;
            }
        };
    }

    public function testGetLength(): void
    {
        static::assertSame(10, $this->iterable->getLength1());
        static::assertSame(20, $this->iterable->getLength2());
    }

    public function testChanges(): void
    {
        $this->changeIterable->expects(self::once())->method('valid')->willReturn(false);

        $changeIterator = $this->iterable->changes();
        static::assertFalse($changeIterator->hasNext());
    }

    public function testUnchanged(): void
    {
        $this->changeIterable->expects(self::exactly(2))->method('valid')->willReturn(false);

        $changeIterator = $this->iterable->unchanged();
        static::assertTrue($changeIterator->hasNext());
    }
}
