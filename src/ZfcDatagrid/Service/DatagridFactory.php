<?php
namespace ZfcDatagrid\Service;

use InvalidArgumentException;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
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

        /* @var $application \Laminas\Mvc\Application */
        $application = $container->get('application');

        $grid = new Datagrid();
        $grid->setOptions($config['ZfcDatagrid']);
        $grid->setMvcEvent($application->getMvcEvent());
        $grid->setRouter($container->get('Router'));

        if (true === $container->has('translator')) {
            $grid->setTranslator($container->get('translator'));
        }

        $grid->setRendererService($container->get('zfcDatagrid.renderer.' . $grid->getRendererName()));
        $grid->init();

        return $grid;
    }
}
