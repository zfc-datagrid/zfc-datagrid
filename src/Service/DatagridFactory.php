<?php
namespace ZfcDatagrid\Service;

use InvalidArgumentException;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Translator\TranslatorInterface;
use Mezzio\Router\RouterInterface;
use ZfcDatagrid\Datagrid;

class DatagridFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return Datagrid
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Datagrid
    {
        $config = $container->get('config');

        if (! isset($config['ZfcDatagrid'])) {
            throw new InvalidArgumentException('Config key "ZfcDatagrid" is missing');
        }

        $grid = new Datagrid();
        $grid->setOptions($config['ZfcDatagrid']);
        $grid->setRouter($container->get(RouterInterface::class));

/*
        $storageFactory = $container->get(StorageAdapterFactoryInterface::class);

        $cacheOptions = $config['ZfcDatagrid']['cache'];

        $grid->setCache($storageFactory->create(
            $cacheOptions['adapter']['name'],
            $cacheOptions['options'] ?? [],
            $cacheOptions['plugins'] ?? []
        ));
*/

        if (true === $container->has(TranslatorInterface::class)) {
            $grid->setTranslator($container->get(TranslatorInterface::class));
        }

        $grid->setRendererService($container->get('zfcDatagrid.renderer.' . $grid->getRendererName()));
        $grid->init();

        return $grid;
    }
}
