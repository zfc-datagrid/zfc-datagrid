<?php

namespace ZfcDatagrid\Service;

use Zend\Form\FormElementManager;
use Zend\Mvc\Service\AbstractPluginManagerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DatagridManagerFactory
 *
 * @package ZfcDatagrid\Service
 */
class DatagridManagerFactory extends AbstractPluginManagerFactory
{
    /**
     * Classname of the plugin manager.
     */
    const PLUGIN_MANAGER_CLASS = DatagridManager::class;

    /**
     * Create and return the MVC controller plugin manager.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return FormElementManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $plugins = parent::createService($serviceLocator);
        $plugins->addPeeringServiceManager($serviceLocator);
        $plugins->setRetrieveFromPeeringManagerFirst(true);

        return $plugins;
    }
}
