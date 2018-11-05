<?php
namespace ZfcDatagrid\Column\DataPopulation;

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
     * @param null|ObjectAwareInterface $object
     *
     * @throws \Exception
     */
    public function setObject(?ObjectAwareInterface $object)
    {
        $this->object = $object;
    }

    /**
     * @return null|ObjectAwareInterface
     */
    public function getObject(): ?ObjectAwareInterface
    {
        return $this->object;
    }

    /**
     * Apply a dynamic parameter based on row/column value.
     *
     * @param string                $objectParameterName
     * @param Column\AbstractColumn $column
     */
    public function addObjectParameterColumn(string $objectParameterName, Column\AbstractColumn $column)
    {
        $this->objectParameters[] = [
            'objectParameterName' => $objectParameterName,
            'column'              => $column,
        ];
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
     * @param string $name
     * @param mixed  $value
     */
    public function setObjectParameter(string $name, $value)
    {
        if ($this->getObject()) {
            $this->getObject()->setParameterFromColumn($name, $value);   
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $return = '';
        if ($this->getObject()) {
            $return = $this->getObject()->toString();
        }

        return $return;
    }
}
