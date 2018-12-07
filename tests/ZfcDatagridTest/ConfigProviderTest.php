<?php

namespace ZfcDatagridTest;

use ZfcDatagrid\ConfigProvider;
use ZfcDatagrid\Module;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testConfigProviderGetConfig()
    {
        $configProvider = new ConfigProvider();
        $config         = $configProvider();

        $this->assertNotEmpty($config);
    }

    public function testConfigEqualsToModuleConfig()
    {
        $module         = new Module();
        $moduleConfig   = $module->getConfig();
        $configProvider = new ConfigProvider();
        $config         = $configProvider();

        $this->assertEquals($moduleConfig['service_manager'], $config['dependencies']);
        $this->assertEquals($moduleConfig['ZfcDatagrid'], $config['ZfcDatagrid']);
    }
}
