<?php

declare(strict_types=1);

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

        $this->assertIsArray($module->getConfig());
        $this->assertCount(4, $module->getConfig());
        $this->assertArrayHasKey('ZfcDatagrid', $module->getConfig());
    }
}
