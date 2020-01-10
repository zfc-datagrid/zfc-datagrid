<?php
namespace ZfcDatagrid\Column\Formatter;

use Laminas\Router\RouteStackInterface;

/**
 * Interface RouterInterface
 * @package ZfcDatagrid\Column\Formatter
 */
interface RouterInterface
{
    /**
     * @param \Laminas\Router\RouteStackInterface $router
     *
     * @return $this
     */
    public function setRouter(RouteStackInterface $router): self;

    /**
     * @return null|RouteStackInterface
     */
    public function getRouter(): ?RouteStackInterface;
}
