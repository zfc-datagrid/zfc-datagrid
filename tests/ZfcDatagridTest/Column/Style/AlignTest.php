<?php

namespace ZfcDatagridTest\Column\Style;

use ZfcDatagrid\Column\Style;
use ZfcDatagridTest\Util\TestBase;

class AlignTest extends TestBase
{
    /** @var string */
    protected $className = Style\Align::class;

    public function testCanCreateInstance(): void
    {
        $this->mockedConstructorArgList = [null];

        $this->assertInstanceOf(Style\AbstractStyle::class, new Style\Align());
        $this->assertInstanceOf(Style\Align::class, new Style\Align());
        $this->assertSame(Style\Align::LEFT, $this->getProperty('alignment'));
    }

    public function testAlignment(): void
    {
        $this->mockedConstructorArgList = [null];

        $this->assertSame(Style\Align::LEFT, $this->getMethod('getAlignment')->invoke($this->getClass()));
        $this->getMethod('setAlignment')->invokeArgs($this->getClass(), ['foobar']);
        $this->assertSame('foobar', $this->getMethod('getAlignment')->invoke($this->getClass()));
    }
}
