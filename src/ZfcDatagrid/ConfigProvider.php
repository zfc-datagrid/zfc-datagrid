<?php

namespace ZfcDatagrid;

class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        $module = new Module();
        $config = $module->getConfig();

        return [
            'dependencies'  => $config['service_manager'],
            'ZfcDatagrid'      => $config['ZfcDatagrid'],
        ];
    }
}