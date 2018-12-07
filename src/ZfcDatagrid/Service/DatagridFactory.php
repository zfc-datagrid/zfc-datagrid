<?php
namespace ZfcDatagrid\Service;

use InvalidArgumentException;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Middleware\RequestHelper;

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

        /** @var RequestHelper $requestHelper */
        $requestHelper = $container->get(RequestHelper::class);

        $grid = new Datagrid();
        $grid->setOptions($config['ZfcDatagrid']);
        $grid->setRequest($requestHelper->getRequest());
        $grid->setRouter($container->get('Router'));

        if (true === $container->has('translator')) {
            $grid->setTranslator($container->get('translator'));
        }

        $grid->setRendererService($container->get('zfcDatagrid.renderer.' . $grid->getRendererName()));
        $grid->init();

        return $grid;
    }

}
