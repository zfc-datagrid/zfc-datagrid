<?php
namespace ZfcDatagrid\Column\DataPopulation;

use Exception;

class StaticValue implements DataPopulationInterface
{
    /** @var null|string */
    protected $value;

    /**
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    /**
     * @param null|string $value
     *
     * @return $this
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return DataPopulationInterface
     * @throws Exception
     */
    public function setObjectParameter(string $name, $value): DataPopulationInterface
    {
        throw new Exception('setObjectParameter() is not supported by this class');
    }

    /**
     * @return array
     */
    public function getObjectParametersColumn(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string)$this->getValue();
    }
}
