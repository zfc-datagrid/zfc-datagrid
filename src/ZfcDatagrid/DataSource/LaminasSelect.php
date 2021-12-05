<?php

declare(strict_types=1);

namespace ZfcDatagrid\DataSource;

use Exception;
use InvalidArgumentException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Paginator\Adapter\DbSelect as PaginatorAdapter;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Filter;

use function sprintf;

class LaminasSelect extends AbstractDataSource
{
    /** @var Sql\Select */
    private $select;

    /** @var Sql\Sql|null*/
    private $sqlObject;

    /**
     * Data source.
     */
    public function __construct(Sql\Select $data)
    {
        $this->select = $data;
    }

    public function getData(): Sql\Select
    {
        return $this->select;
    }

    /**
     * @param $adapterOrSqlObject
     * @throws InvalidArgumentException
     */
    public function setAdapter($adapterOrSqlObject)
    {
        if ($adapterOrSqlObject instanceof Sql\Sql) {
            $this->sqlObject = $adapterOrSqlObject;
        } elseif ($adapterOrSqlObject instanceof Adapter) {
            $this->sqlObject = new Sql\Sql($adapterOrSqlObject);
        } else {
            throw new InvalidArgumentException('Object of "Laminas\Db\Sql\Sql" or "Laminas\Db\Adapter\Adapter" needed.');
        }
    }

    public function getAdapter(): ?Sql\Sql
    {
        return $this->sqlObject;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        if ($this->getAdapter() === null || ! $this->getAdapter() instanceof \Laminas\Db\Sql\Sql) {
            throw new Exception('Object "Laminas\Db\Sql\Sql" is missing, please call setAdapter() first!');
        }

        $platform = $this->getAdapter()
            ->getAdapter()
            ->getPlatform();

        $select = $this->getData();

        /*
         * Step 1) Apply needed columns
         */
        $selectColumns = [];
        foreach ($this->getColumns() as $col) {
            if (! $col instanceof Column\Select) {
                continue;
            }

            $colString = $col->getSelectPart1();
            if ($col->getSelectPart2() != '') {
                $colString = new Expression(
                    sprintf(
                        '%s%s%s',
                        $platform->quoteIdentifier($colString),
                        $platform->getIdentifierSeparator(),
                        $platform->quoteIdentifier($col->getSelectPart2())
                    )
                );
            }

            $selectColumns[$col->getUniqueId()] = $colString;
        }
        $select->columns($selectColumns, false);

        $joins = $select->getRawState('joins');
        $select->reset('joins');
        foreach ($joins as $join) {
            $select->join($join['name'], $join['on'], [], $join['type']);
        }

        /*
         * Step 2) Apply sorting
         */
        if (! empty($this->getSortConditions())) {
            // Minimum one sort condition given -> so reset the default orderBy
            $select->reset(Sql\Select::ORDER);

            foreach ($this->getSortConditions() as $sortCondition) {
                /** @var AbstractColumn $col */
                $col = $sortCondition['column'];
                $select->order($col->getUniqueId() . ' ' . $sortCondition['sortDirection']);
            }
        }

        /*
         * Step 3) Apply filters
         */
        $filterColumn = new LaminasSelect\Filter($this->getAdapter(), $select);
        foreach ($this->getFilters() as $filter) {
            /** @var Filter $filter */
            if ($filter->isColumnFilter() === true) {
                $filterColumn->applyFilter($filter);
            }
        }

        /*
         * Step 4) Pagination
         */
        $this->setPaginatorAdapter(new PaginatorAdapter($select, $this->getAdapter()));
    }
}
