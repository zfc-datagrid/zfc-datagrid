<?php

namespace ZfcDatagridTest\Column\Action;

use ZfcDatagrid\Column\Action\Checkbox;
use ZfcDatagridTest\Util\TestBase;

class CheckboxTest extends TestBase
{
    /** @var string */
    protected $className = Checkbox::class;

    public function testConstruct(): void
    {
        $this->assertSame('rowSelections', $this->getProperty('name'));
    }

    public function testConstructWithParam(): void
    {
        $this->mockedConstructorArgList = [
            'foobar',
        ];

        $this->assertSame('foobar', $this->getProperty('name'));
    }

    public function testGetHtmlType(): void
    {
        $this->assertSame('', $this->getMethod('getHtmlType')->invoke($this->getClass()));
    }

    public function testToHtml(): void
    {
        $this->mockedMethodList = [
            'removeAttribute',
            'getAttributesString',
        ];

        $class = $this->getClass();
        $class->expects($this->exactly(2))
            ->method('removeAttribute')
            ->withConsecutive(['name'], ['value']);
        $class->expects($this->exactly(1))
            ->method('getAttributesString')
            ->withConsecutive([['idConcated' => 'foobar']])
            ->willReturn('title="foobar"');

        $this->assertSame(
            '<input type="checkbox" name="rowSelections" value="foobar" title="foobar" />',
            $this->getMethod('toHtml')->invokeArgs($this->getClass(), [['idConcated' => 'foobar']])
        );
    }
}
