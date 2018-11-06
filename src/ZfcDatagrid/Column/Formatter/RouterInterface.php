<?php
namespace ZfcDatagrid\Column\Formatter;

use Zend\Router\RouteStackInterface;

/**
 * Interface RouterInterface
 * @package ZfcDatagrid\Column\Formatter
 */
interface RouterInterface
{
    /**
     * @param \Zend\Router\RouteStackInterface $router
     *
     * @return void
     */
    public function setRouter(RouteStackInterface $router);

    /**
     * @return null|RouteStackInterface
     */
    public function getRouter(): ?RouteStackInterface;
}
