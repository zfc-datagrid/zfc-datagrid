<?php
declare(strict_types=1);

namespace ZfcDatagrid\Renderer\BootstrapTable;

use Mezzio\LaminasView\LaminasViewRenderer;
use Psr\Container\ContainerInterface;

class RendererFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return (new Renderer())
            ->setTemplateRenderer($container->get(LaminasViewRenderer::class));
    }

}