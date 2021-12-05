<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Style;

use function implode;
use function is_array;

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

    public function getClass(): string
    {
        return is_array($this->class) ? implode(' ', $this->class) : $this->class;
    }

    /**
     * @param string|array $class
     * @return $this
     */
    public function setClass($class): self
    {
        $this->class = $class;

        return $this;
    }
}
