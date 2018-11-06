<?php
namespace ZfcDatagrid\Column\Style;

use function is_array;
use function implode;

/**
 * Css class for the row/cell.
 */
class CSSClass extends AbstractStyle
{
    /** @var array|string */
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
    public function getClass(): string
    {
        return is_array($this->class) ? implode(' ', $this->class) : $this->class;
    }

    /**
     * @param string|array $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }
}
