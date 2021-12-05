<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Type;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\Type;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Type\Image
 */
class ImageTest extends TestCase
{
    public function testTypeName(): void
    {
        $type = new Type\Image();

        $this->assertEquals('image', $type->getTypeName());
    }

    public function testSetResizeTypeException(): void
    {
        $type = new Type\Image();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only dynamic or fixed is allowed as Type');
        $type->setResizeType('foobar');
    }

    public function testResizeType(): void
    {
        $type = new Type\Image();
        $this->assertSame('fixed', $type->getResizeType());
        $type->setResizeType('dynamic');
        $this->assertSame('dynamic', $type->getResizeType());
    }

    public function testResizeHeight(): void
    {
        $type = new Type\Image();
        $this->assertSame(20.5, $type->getResizeHeight());
        $type->setResizeHeight(1337.5);
        $this->assertSame(1337.5, $type->getResizeHeight());
    }
}
