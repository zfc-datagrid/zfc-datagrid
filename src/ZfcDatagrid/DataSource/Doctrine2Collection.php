<?php

declare(strict_types=1);

namespace ZfcDatagrid\DataSource;

use Doctrine\Common\Collections\Collection;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Doctrine\ORM\EntityManagerInterface;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Select;
use ZfcDatagrid\DataSource\PhpArray as SourceArray;

class Doctrine2Collection extends AbstractDataSource
{
    /** @var Collection */
    protected $data;

    /** @var EntityManagerInterface|null */
    protected $em;

    /**
     * Data source.
     */
    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function getData(): Collection
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    public function setEntityManager(EntityManagerInterface $em): self
    {
        $this->em = $em;

        return $this;
    }

    public function getEntityManager(): ?EntityManagerInterface
    {
        return $this->em;
    }

    public function execute()
    {
        $hydrator = new DoctrineHydrator($this->getEntityManager());

        $dataPrepared = [];
        foreach ($this->getData() as $row) {
            $dataExtracted = $hydrator->extract($row);

            $rowExtracted = [];
            foreach ($this->getColumns() as $col) {
                /** @var Select $col */
                if (! $col instanceof Column\Select) {
                    continue;
                }

                $part1 = $col->getSelectPart1();
                $part2 = $col->getSelectPart2();

                if (null === $part2) {
                    if (isset($dataExtracted[$part1])) {
                        $rowExtracted[$col->getUniqueId()] = $dataExtracted[$part1];
                    }
                } else {
                    // NESTED
                    if (isset($dataExtracted[$part1])) {
                        $dataExtractedNested = $hydrator->extract($dataExtracted[$part1]);
                        if (isset($dataExtractedNested[$part2])) {
                            $rowExtracted[$col->getUniqueId()] = $dataExtractedNested[$part2];
                        }
                    }
                }
            }

            $dataPrepared[] = $rowExtracted;
        }

        $source = new SourceArray($dataPrepared);
        $source->setColumns($this->getColumns());
        $source->setSortConditions($this->getSortConditions());
        $source->setFilters($this->getFilters());
        $source->execute();

        $this->setPaginatorAdapter($source->getPaginatorAdapter());
    }
}
