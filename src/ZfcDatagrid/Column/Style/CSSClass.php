<?php

namespace ZfcDatagrid\Column\Style;

/**
 * Class CSSClass
 *
 * Css class for the row/cell.
 *
 * @package ZfcDatagrid\Column\Style
 */
class CSSClass extends AbstractStyle
{
    /**
     * @var array|string
     */
    private $class;

    /**
     * @param string|array $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        if (is_array($this->class)) {
            return implode(' ', $this->class);
        }

        return $this->class;
    }

    /**
     * @param string|array $class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        
        return $this;
    }
}
