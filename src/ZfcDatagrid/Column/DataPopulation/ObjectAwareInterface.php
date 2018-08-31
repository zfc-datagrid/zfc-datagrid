<?php

namespace ZfcDatagrid\Column\DataPopulation;

/**
 * Interface ObjectAwareInterface
 *
 * @package ZfcDatagrid\Column\DataPopulation
 */
interface ObjectAwareInterface
{
    /**
     * Set a parameter based on the row column value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setParameterFromColumn($name, $value);

    /**
     * Return the result based on the parameters.
     *
     * @return string
     */
    public function toString();
}
