<?php
namespace ZfcDatagrid\Renderer;

use Zend\Http\Response;
use Zend\View\Model\ViewModel;

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
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Determine if the renderer is for export.
     *
     * @return bool
     */
    public function isExport(): bool;

    /**
     * Determin if the renderer is HTML
     * It can be export + html -> f.x.
     * printing for HTML.
     *
     * @return bool
     */
    public function isHtml(): bool;

    /**
     * Execute all...
     *
     * @return ViewModel|Response\Stream
     */
    public function execute();
}
