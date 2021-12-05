<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\DataPopulation;

interface DataPopulationInterface
{
    /**
     * Return the result.
     */
    public function toString(): string;

    /**
     * Directy set a parameter for the object.
     *
     * @param mixed  $value
     * @return $this
     */
    public function setObjectParameter(string $name, $value): self;

    /**
     * @return array
     */
    public function getObjectParametersColumn(): array;
}
