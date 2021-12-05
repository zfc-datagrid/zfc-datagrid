<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Action;

use Exception;
use InvalidArgumentException;
use ZfcDatagrid\Column\AbstractColumn;

class Button extends AbstractAction
{
    /** @var string|AbstractColumn */
    protected $label = '';

    public function __construct()
    {
        parent::__construct();

        $this->addClass('btn');
        $this->addClass('btn-default');
    }

    /**
     * @param string|AbstractColumn $name
     * @return $this
     */
    public function setLabel($name): self
    {
        $this->label = $name;

        return $this;
    }

    /**
     * @return string|AbstractColumn
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @throws Exception
     */
    protected function getHtmlType(): string
    {
        throw new Exception('not needed...since we have toHtml() here directly!');
    }

    /**
     * @param array $row
     */
    public function toHtml(array $row): string
    {
        if ('' === $this->getLabel()) {
            throw new InvalidArgumentException(
                'A label is required for this action type, please call $action->setLabel()!'
            );
        }

        $label = $this->getLabel();
        if ($label instanceof AbstractColumn) {
            $label = $row[$label->getUniqueId()];
        }

        return '<a ' . $this->getAttributesString($row) . '>' . $label . '</a>';
    }
}
