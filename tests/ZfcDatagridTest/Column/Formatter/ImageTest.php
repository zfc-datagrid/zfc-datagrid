<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Formatter;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Formatter\Image
 */
class ImageTest extends TestBase
{
    /** @var string */
    protected $className = Formatter\Image::class;

    public function testGetValidRendererNames(): void
    {
        $formatter = new Formatter\Image();

        $this->assertEquals([
            'jqGrid',
            'bootstrapTable',
            'printHtml',
        ], $formatter->getValidRendererNames());
    }

    public function testSetAttribute(): void
    {
        $this->getMethod('setAttribute')->invokeArgs($this->getClass(), ['foobar', 'barfoo']);
        $this->assertSame([
            'foobar' => 'barfoo',
        ], $this->getProperty('attributes'));
        $this->assertSame([
            'foobar' => 'barfoo',
        ], $this->getMethod('getAttributes')->invoke($this->getClass()));
    }

    public function testGetAttributesDefault(): void
    {
        $this->assertSame([], $this->getMethod('getAttributes')->invoke($this->getClass()));
    }

    public function testSetLinkAttribute(): void
    {
        $this->getMethod('setLinkAttribute')->invokeArgs($this->getClass(), ['foobar', 'barfoo']);
        $this->assertSame([
            'foobar' => 'barfoo',
        ], $this->getProperty('linkAttributes'));
        $this->assertSame([
            'foobar' => 'barfoo',
        ], $this->getMethod('getLinkAttributes')->invoke($this->getClass()));
    }

    public function testGetLinkAttributesDefault(): void
    {
        $this->assertSame([], $this->getMethod('getLinkAttributes')->invoke($this->getClass()));
    }

    public function testGetPrefix(): void
    {
        $this->assertSame('', $this->getMethod('getPrefix')->invoke($this->getClass()));
    }

    public function testSetPrefix(): void
    {
        $this->getMethod('setPrefix')->invokeArgs($this->getClass(), ['foobar']);
        $this->assertSame('foobar', $this->getMethod('getPrefix')->invoke($this->getClass()));
    }

    public function testGetFormattedValueEmptyValue(): void
    {
        $this->mockedMethodList = [
            'getRowData',
        ];
        $class                  = $this->getClass();
        $class->expects($this->exactly(1))
            ->method('getRowData')
            ->willReturn(['id' => '']);

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        $this->assertSame('', $this->getMethod('getFormattedValue')->invokeArgs($this->getClass(), [$col]));
    }

    public function testGetFormattedValueWithString(): void
    {
        $this->mockedMethodList = [
            'getRowData',
            'getLinkAttributes',
            'getAttributes',
        ];
        $class                  = $this->getClass();
        $class->expects($this->exactly(1))
            ->method('getRowData')
            ->willReturn(['id' => 'foobar']);
        $class->expects($this->exactly(1))
            ->method('getLinkAttributes')
            ->willReturn(['bar' => 'foobar']);
        $class->expects($this->exactly(1))
            ->method('getAttributes')
            ->willReturn(['title' => 'foobar']);

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        $this->assertSame(
            '<a href="foobar" bar="foobar"><img src="foobar" title="foobar"/></a>',
            $this->getMethod('getFormattedValue')->invokeArgs($this->getClass(), [$col])
        );
    }

    public function testGetFormattedValueWithArray(): void
    {
        $this->mockedMethodList = [
            'getRowData',
        ];
        $class                  = $this->getClass();
        $class->expects($this->exactly(1))
            ->method('getRowData')
            ->willReturn(['id' => ['value', 'original']]);

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        $this->assertSame(
            '<a href="original" ><img src="value" /></a>',
            $this->getMethod('getFormattedValue')->invokeArgs($this->getClass(), [$col])
        );
    }
}
