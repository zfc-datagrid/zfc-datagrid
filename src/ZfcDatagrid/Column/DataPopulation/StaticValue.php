<?php
namespace ZfcDatagrid\Column\DataPopulation;

class StaticValue implements DataPopulationInterface
{
    /** @var null|string */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->setValue($value);
    }

    /**
     * @param null|string $value
     */
    public function setValue(?string $value)
    {
        $this->value = $value;
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
     * @param mixed  $value
     *
     * @throws \Exception
     */
    public function setObjectParameter(string $name, $value)
    {
        throw new \Exception('setObjectParameter() is not supported by this class');
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
