<?php

namespace ZfcDatagrid\Column\Action;

use ZfcDatagrid\Column\AbstractColumn;

/**
 * Class Button
 *
 * @package ZfcDatagrid\Column\Action
 */
class Button extends AbstractAction
{
    /**
     * @var string
     */
    protected $label = '';

    /**
     * Button constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addClass('btn');
        $this->addClass('btn-default');
    }

    /**
     * @param string|AbstractColumn $name
     *
     * @return $this
     */
    public function setLabel($name)
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
     * @return string
     *
     * @throws \Exception
     */
    protected function getHtmlType()
    {
        throw new \Exception('not needed...since we have toHtml() here directly!');
    }

    /**
     * @param array $row
     *
     * @return string
     */
    public function toHtml(array $row)
    {
        if ($this->getLabel() == '') {
            throw new \InvalidArgumentException(
                'A label is required for this action type, please call $action->setLabel()!'
            );
        }

        $label = $this->getLabel();
        if ($label instanceof AbstractColumn) {
            $label = $row[$label->getUniqueId()];
        }

        return '<a '.$this->getAttributesString($row).'>'.$label.'</a>';
    }
}
