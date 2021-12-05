<?php

declare(strict_types=1);

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
     * @return $this
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param mixed $value
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

    public function toString(): string
    {
        return (string) $this->getValue();
    }
}
