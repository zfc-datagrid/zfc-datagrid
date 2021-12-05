<?php

declare(strict_types=1);

namespace ZfcDatagrid\Service;

use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LaminasDbAdapterFactory implements FactoryInterface
{
    /**
     * @param string             $requestedName
     * @param array|null         $options
     * @return Adapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');

        return new Adapter($config['zfcDatagrid_dbAdapter']);
    }
}
