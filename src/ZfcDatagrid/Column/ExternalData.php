<?php
namespace ZfcDatagrid\Column;

class ExternalData extends AbstractColumn
{
    /**
     * @var DataPopulation\DataPopulationInterface
     */
    protected $dataPopulation;

    /**
     * ExternalData constructor.
     * @param string $uniqueId
     */
    public function __construct(string $uniqueId = 'external')
    {
        $this->setUniqueId($uniqueId);

        $this->setUserSortDisabled(true);
        $this->setUserFilterDisabled(true);
    }

    /**
     * @param DataPopulation\DataPopulationInterface $dataPopulation
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setDataPopulation(DataPopulation\DataPopulationInterface $dataPopulation): self
    {
        if ($dataPopulation instanceof DataPopulation\DataObject && $dataPopulation->getObject() === null) {
            throw new \Exception('object is missing in DataPopulation\DataObject!');
        }

        $this->dataPopulation = $dataPopulation;

        return $this;
    }

    /**
     * @return DataPopulation\DataPopulationInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getDataPopulation(): DataPopulation\DataPopulationInterface
    {
        if (null === $this->dataPopulation) {
            throw new \InvalidArgumentException('no data population set for Column\ExternalData');
        }

        return $this->dataPopulation;
    }

    /**
     * @return bool
     */
    public function hasDataPopulation(): bool
    {
        return null !== $this->dataPopulation;
    }
}
