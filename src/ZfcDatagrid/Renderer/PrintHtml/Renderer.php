<?php

namespace ZfcDatagrid\Renderer\PrintHtml;

use Zend\View\Model\ViewModel;
use ZfcDatagrid\Renderer\AbstractRenderer;

class Renderer extends AbstractRenderer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'printHtml';
    }

    /**
     * @return bool
     */
    public function isExport(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHtml(): bool
    {
        return true;
    }

    /**
     * @return \Zend\View\Model\ViewModel
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
