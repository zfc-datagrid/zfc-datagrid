<?php

namespace ZfcDatagridTest\Column\DataPopulation;

use Exception;
use ZfcDatagrid\Column\DataPopulation\StaticValue;
use ZfcDatagridTest\Util\TestBase;

class StaticValueTest extends TestBase
{
    /** @var string */
    protected $className = StaticValue::class;

    public function testConstruct(): void
    {
        $this->assertNull($this->getProperty('value'));
    }

    public function testConstructWithParam(): void
    {
        $value = rand(99, 1547);
        $this->mockedConstructorArgList = [
            $value,
        ];

        $this->assertSame((string)$value, $this->getProperty('value'));
    }

    public function testValue(): void
    {
        $value = rand(99, 1547);
        $this->assertNull($this->getMethod('getValue')->invoke($this->getClass()));

        $this->getMethod('setValue')->invokeArgs($this->getClass(), [$value]);

        $this->assertSame((string)$value, $this->getMethod('getValue')->invoke($this->getClass()));
        $this->assertSame((string)$value, $this->getProperty('value'));
    }

    public function testSetObjectParameterException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('setObjectParameter() is not supported by this class');

        $this->getMethod('setObjectParameter')->invokeArgs($this->getClass(), ['name', null]);
    }

    public function testGetObjectParameterColumn(): void
    {
        $this->assertSame([], $this->getMethod('getObjectParametersColumn')->invoke($this->getClass()));
    }

    public function testToStringDefault(): void
    {
        $this->assertSame('', $this->getMethod('toString')->invoke($this->getClass()));
    }

    public function testToString(): void
    {
        $this->setProperty('value', 'foobar');
        $this->assertSame('foobar', $this->getMethod('toString')->invoke($this->getClass()));
    }
}
