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
     * @return $this
     */
    public function setRouter(RouteStackInterface $router): self;

    /**
     * @return null|RouteStackInterface
     */
    public function getRouter(): ?RouteStackInterface;
}
