<?php

namespace ZfcDatagrid\Service;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Interop\Container\ContainerInterface;
use Popov\ZfcCurrent\CurrentHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        if (! isset($config['ZfcDatagrid'])) {
            throw new InvalidArgumentException('Config key "ZfcDatagrid" is missing');
        }

        /* @var $application \Zend\Mvc\Application */
        //$application = $container->get('application');

        /** @var RequestHelper $requestHelper */
        $requestHelper = $container->get(RequestHelper::class);

        $grid = new Datagrid();
        $grid->setServiceLocator($container);
        $grid->setOptions($config['ZfcDatagrid']);
        //$grid->setMvcEvent($application->getMvcEvent());
        $grid->setRequest($requestHelper->getRequest());

        if ($container->has('translator') === true) {
            $grid->setTranslator($container->get('translator'));
        }

        $grid->setRendererService($container->get('zfcDatagrid.renderer.'.$grid->getRendererName()));
        $grid->init();

        return $grid;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return Datagrid
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Datagrid::class);
    }
}
