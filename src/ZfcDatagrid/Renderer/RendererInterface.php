<?php

declare(strict_types=1);

namespace ZfcDatagrid\Renderer;

use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

interface RendererInterface
{
    /**
     * @return array
     */
    public function getSortConditions(): array;

    /**
     * @return array
     */
    public function getFilters(): array;

    /**
     * Return the name of the renderer.
     */
    public function getName(): string;

    /**
     * Determine if the renderer is for export.
     */
    public function isExport(): bool;

    /**
     * Determin if the renderer is HTML
     * It can be export + html -> f.x.
     * printing for HTML.
     */
    public function isHtml(): bool;

    /**
     * Execute all...
     *
     * @return ViewModel|Response\Stream
     */
    public function execute();
}
