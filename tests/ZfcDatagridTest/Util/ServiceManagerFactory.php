<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Util;

use Laminas\Mvc\Service\ServiceListenerFactory;
use Laminas\ServiceManager\ServiceManager;

class ServiceManagerFactory
{
    /** @var array */
    protected static $config = [];

    /**
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        static::$config = $config;
    }

    /**
     * @return ServiceManager
     */
    public static function getServiceManager()
    {
        $serviceManager = new ServiceManager(
            static::$config['service_manager'] ?? []
        );
        $serviceManager->setService('Applicationconfig', static::$config);
        $serviceManager->setFactory('ServiceListener', ServiceListenerFactory::class);

        $serviceManager->setService('config', self::$config);

        return $serviceManager;
    }
}
