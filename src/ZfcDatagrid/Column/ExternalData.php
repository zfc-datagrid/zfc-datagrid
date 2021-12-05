<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column;

use Exception;
use InvalidArgumentException;

class ExternalData extends AbstractColumn
{
    /** @var DataPopulation\DataPopulationInterface */
    protected $dataPopulation;

    public function __construct(string $uniqueId = 'external')
    {
        $this->setUniqueId($uniqueId);

        $this->setUserSortDisabled(true);
        $this->setUserFilterDisabled(true);
    }

    /**
     * @throws Exception
     * @return $this
     */
    public function setDataPopulation(DataPopulation\DataPopulationInterface $dataPopulation): self
    {
        if ($dataPopulation instanceof DataPopulation\DataObject && $dataPopulation->getObject() === null) {
            throw new Exception('object is missing in DataPopulation\DataObject!');
        }

        $this->dataPopulation = $dataPopulation;

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getDataPopulation(): DataPopulation\DataPopulationInterface
    {
        if (null === $this->dataPopulation) {
            throw new InvalidArgumentException('no data population set for Column\ExternalData');
        }

        return $this->dataPopulation;
    }

    public function hasDataPopulation(): bool
    {
        return null !== $this->dataPopulation;
    }
}
