<?php
namespace ZfcDatagrid\Service;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Service\AbstractPluginManagerFactory;

class DatagridManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = 'ZfcDatagrid\Service\DatagridManager';

    /**
     * Create and return the MVC controller plugin manager.
     *
     * @param ContainerInterface $container
     * @param $name
     * @param array|null $options
     * @return \Zend\ServiceManager\AbstractPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $plugins = parent::__invoke($container, $name, $options);
        $plugins->addPeeringServiceManager($container);
        $plugins->setRetrieveFromPeeringManagerFirst(true);

        return $plugins;
    }

}
