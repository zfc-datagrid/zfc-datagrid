<?php

namespace ZfcDatagrid;

class ConfigProvider
{
    public function __invoke()
    {
        $config = include __DIR__.'/../../config/module.config.php';
        if ($config['ZfcDatagrid']['renderer']['bootstrapTable']['daterange']['enabled'] === true) {
            $configNoCache = include __DIR__.'/../../config/daterange.config.php';

            $config = array_merge_recursive($config, $configNoCache);
        }
        $config['dependencies'] = $config['service_manager'];
        unset($config['service_manager']);
        //unset($config['controller_plugins']);

        return $config;
    }
}