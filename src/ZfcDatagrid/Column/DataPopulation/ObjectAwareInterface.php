<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\DataPopulation;

interface ObjectAwareInterface
{
    /**
     * Set a parameter based on the row column value.
     *
     * @param mixed  $value
     * @return $this
     */
    public function setParameterFromColumn(string $name, $value): self;

    /**
     * Return the result based on the parameters.
     */
    public function toString(): string;
}
