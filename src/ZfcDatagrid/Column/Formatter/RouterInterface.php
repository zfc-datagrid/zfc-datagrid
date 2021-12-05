<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Formatter;

use Laminas\Router\RouteStackInterface;

/**
 * Interface RouterInterface
 */
interface RouterInterface
{
    /**
     * @return $this
     */
    public function setRouter(RouteStackInterface $router): self;

    public function getRouter(): ?RouteStackInterface;
}
