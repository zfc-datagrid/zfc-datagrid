<?php

namespace ZfcDatagrid\Column\DataPopulation;

/**
 * Interface DataPopulationInterface
 *
 * @package ZfcDatagrid\Column\DataPopulation
 */
interface DataPopulationInterface
{
    /**
     * Return the result.
     *
     * @return string
     */
    public function toString();

    /**
     * Directy set a parameter for the object.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setObjectParameter($name, $value);

    /**
     * @return array
     */
    public function getObjectParametersColumn();
}
