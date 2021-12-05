<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\DataPopulation;

use Exception;
use ZfcDatagrid\Column;

/**
 * Get the data from an external object.
 */
class DataObject implements DataPopulationInterface
{
    /** @var null|ObjectAwareInterface */
    private $object;

    /** @var array */
    private $objectParameters = [];

    /**
     * @return $this
     * @throws Exception
     */
    public function setObject(?ObjectAwareInterface $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getObject(): ?ObjectAwareInterface
    {
        return $this->object;
    }

    /**
     * Apply a dynamic parameter based on row/column value.
     *
     * @return $this
     */
    public function addObjectParameterColumn(string $objectParameterName, Column\AbstractColumn $column): self
    {
        $this->objectParameters[] = [
            'objectParameterName' => $objectParameterName,
            'column'              => $column,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getObjectParametersColumn(): array
    {
        return $this->objectParameters;
    }

    /**
     * Directly apply a "static" parameter.
     *
     * @param mixed  $value
     * @return $this
     */
    public function setObjectParameter(string $name, $value): DataPopulationInterface
    {
        if ($this->getObject()) {
            $this->getObject()->setParameterFromColumn($name, $value);
        }

        return $this;
    }

    public function toString(): string
    {
        $return = '';
        if ($this->getObject()) {
            $return = $this->getObject()->toString();
        }

        return $return;
    }
}
