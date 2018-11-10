<?php
namespace ZfcDatagrid\Column\DataPopulation;

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
    public function setParameterFromColumn(string $name, $value): self;

    /**
     * Return the result based on the parameters.
     *
     * @return string
     */
    public function toString(): string;
}
