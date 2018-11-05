<?php
namespace ZfcDatagrid\Column\DataPopulation;

interface DataPopulationInterface
{
    /**
     * Return the result.
     *
     * @return string
     */
    public function toString(): string;

    /**
     * Directy set a parameter for the object.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setObjectParameter(string $name, $value);

    /**
     * @return array
     */
    public function getObjectParametersColumn(): array;
}
