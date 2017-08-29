<?php
namespace ZfcDatagrid\Column;

class ExternalData extends AbstractColumn
{
    /**
     * @var DataPopulation\DataPopulationInterface
     */
    protected $dataPopulation;

    public function __construct($uniqueId = 'external')
    {
        $this->setUniqueId($uniqueId);

        $this->setUserSortDisabled(true);
        $this->setUserFilterDisabled(true);
    }

    /**
     * @param DataPopulation\DataPopulationInterface $dataPopulation
     *
     * @throws \Exception
     */
    public function setDataPopulation(DataPopulation\DataPopulationInterface $dataPopulation)
    {
        if ($dataPopulation instanceof DataPopulation\DataObject && $dataPopulation->getObject() === null) {
            throw new \Exception('object is missing in DataPopulation\DataObject!');
        }

        $this->dataPopulation = $dataPopulation;
    }

    /**
     * @return DataPopulation\DataPopulationInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getDataPopulation()
    {
        if (null === $this->dataPopulation) {
            throw new \InvalidArgumentException('no data population set for Column\ExternalData');
        }

        return $this->dataPopulation;
    }

    /**
     * @return bool
     */
    public function hasDataPopulation()
    {
        return null !== $this->dataPopulation;
    }
}
