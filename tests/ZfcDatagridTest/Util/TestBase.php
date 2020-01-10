<?php

namespace ZfcDatagridTest\Util;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class TestBase extends TestCase
{
    /** @var  string */
    protected $className = '';
    /** @var  array|null */
    protected $mockedMethodList = null;
    /** @var  MockObject|null */
    protected $class;
    /** @var array */
    protected $mockedConstructorArgList = [];

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @param string $methodName
     * @return ReflectionMethod
     */
    protected function getMethod(string $methodName): ReflectionMethod
    {
        $reflection = new ReflectionClass($this->getClass());
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param string $name
     * @param null|object $class
     * @return mixed
     */
    protected function getProperty(string $name, $class = null)
    {
        $class = $class ?: $this->getClass();
        $reflection = new ReflectionProperty($class, $name);
        $reflection->setAccessible(true);

        return $reflection->getValue($class);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param null|object $class
     */
    protected function setProperty(string $name, $value, $class = null)
    {
        $class = $class ?: $this->getClass();
        $reflection = new ReflectionProperty($class, $name);
        $reflection->setAccessible(true);
        $reflection->setValue($class, $value);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getClass(): \PHPUnit\Framework\MockObject\MockObject
    {
        if (!$this->class) {
            $class = $this->getMockBuilder($this->className);
            if ($this->mockedConstructorArgList) {
                $class->setConstructorArgs($this->mockedConstructorArgList);
            } else {
                $class->disableOriginalConstructor();
            }
            $this->class = $class->setMethods($this->mockedMethodList)
                ->getMock();
        }

        return $this->class;
    }

}