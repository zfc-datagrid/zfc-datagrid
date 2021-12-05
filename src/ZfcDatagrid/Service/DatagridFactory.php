<?php

declare(strict_types=1);

namespace ZfcDatagrid\Service;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use Laminas\Mvc\Application;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ZfcDatagrid\Datagrid;

class DatagridFactory implements FactoryInterface
{
    /**
     * @param string             $requestedName
     * @param array|null         $options
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): Datagrid
    {
        $config = $container->get('config');

        if (! isset($config['ZfcDatagrid'])) {
            throw new InvalidArgumentException('Config key "ZfcDatagrid" is missing');
        }

        /** @var Application $application */
        $application = $container->get('application');

        $grid = new Datagrid();
        $grid->setOptions($config['ZfcDatagrid']);
        $grid->setMvcEvent($application->getMvcEvent());
        $grid->setRouter($container->get('Router'));

        /** @var StorageAdapterFactoryInterface $storageFactory */
        $storageFactory = $container->get(StorageAdapterFactoryInterface::class);

        $cacheOptions = $config['ZfcDatagrid']['cache'];

        $grid->setCache($storageFactory->create(
            $cacheOptions['adapter']['name'],
            $cacheOptions['options'] ?? [],
            $cacheOptions['plugins'] ?? []
        ));

        if (true === $container->has('translator')) {
            $grid->setTranslator($container->get('translator'));
        }

        $grid->setRendererService($container->get('zfcDatagrid.renderer.' . $grid->getRendererName()));
        $grid->init();

        return $grid;
    }
}
