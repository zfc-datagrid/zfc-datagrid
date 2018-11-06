<?php
namespace ZfcDatagrid;

use function array_merge_recursive;

class Module
{
    /**
     * @return array
     */
    public function getConfig()
    {
        $config = include __DIR__ . '/../../config/module.config.php';
        if ($config['ZfcDatagrid']['renderer']['bootstrapTable']['daterange']['enabled'] === true) {
            $configNoCache = include __DIR__ . '/../../config/daterange.config.php';

            $config = array_merge_recursive($config, $configNoCache);
        }

        return $config;
    }
}
