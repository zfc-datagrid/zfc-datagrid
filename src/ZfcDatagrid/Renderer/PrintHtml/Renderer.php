<?php

declare(strict_types=1);

namespace ZfcDatagrid\Renderer\PrintHtml;

use Laminas\View\Model\ViewModel;
use ZfcDatagrid\Renderer\AbstractRenderer;

class Renderer extends AbstractRenderer
{
    public function getName(): string
    {
        return 'printHtml';
    }

    public function isExport(): bool
    {
        return true;
    }

    public function isHtml(): bool
    {
        return true;
    }

    /**
     * @return ViewModel
     */
    public function execute()
    {
        $layout = $this->getViewModel();
        $layout->setTemplate($this->getTemplate());
        $layout->setTerminal(true);

        $table = new ViewModel();
        $table->setTemplate('zfc-datagrid/renderer/printHtml/table');
        $table->setVariables($layout->getVariables());

        $layout->addChild($table, 'table');

        return $layout;
    }
}
