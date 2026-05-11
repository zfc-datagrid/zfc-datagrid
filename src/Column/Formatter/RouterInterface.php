<?php
namespace ZfcDatagrid\Column\Formatter;

use Mezzio\Router\RouterInterface as RouterInterfaceMezzio;

/**
 * Interface RouterInterface
 * @package ZfcDatagrid\Column\Formatter
 */
interface RouterInterface
{
    /**
     * @param RouterInterfaceMezzio $router
     *
     * @return $this
     */
    public function setRouter(RouterInterfaceMezzio $router): self;

    /**
     * @return null|RouterInterfaceMezzio
     */
    public function getRouter(): ?RouterInterfaceMezzio;
}
