<?php
namespace ZfcDatagrid\Service;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfcDatagrid\Datagrid;

abstract class AbstractDatagrid extends Datagrid implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return $this
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');

        if (! isset($config['ZfcDatagrid'])) {
            throw new InvalidArgumentException('Config key "ZfcDatagrid" is missing');
        }

        /* @var $application \Zend\Mvc\Application */
        $application = $container->get('application');

        $this->setOptions($config['ZfcDatagrid']);
        $this->setMvcEvent($application->getMvcEvent());

        if ($container->has('translator') === true) {
            $this->setTranslator($container->get('translator'));
        }

        $this->setRendererService($container->get('zfcDatagrid.renderer.' . $this->getRendererName()));
        $this->init();

        return $this;
    }

    /**
     * Call initGrid on rendering.
     */
    public function render()
    {
        $this->initGrid();

        parent::render();
    }

    abstract public function initGrid();
}
