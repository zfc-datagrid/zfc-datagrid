<?php
namespace ZfcDatagridTest;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Module;

/**
 * @covers \ZfcDatagrid\Module
 */
class ModuleTest extends TestCase
{
    public function testGetConfig()
    {
        $module = new Module();

        $this->assertInternalType('array', $module->getConfig());
        $this->assertCount(5, $module->getConfig());
        $this->assertArrayHasKey('ZfcDatagrid', $module->getConfig());
    }
}
