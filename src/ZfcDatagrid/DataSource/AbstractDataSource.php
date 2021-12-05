<?php

declare(strict_types=1);

namespace ZfcDatagrid\DataSource;

use Laminas\Paginator\Adapter\AdapterInterface as PaginatorAdapterInterface;
use ZfcDatagrid\Column;
use ZfcDatagrid\Filter;

abstract class AbstractDataSource implements DataSourceInterface
{
    /** @var Column\AbstractColumn[] */
    protected $columns = [];

    /** @var array */
    protected $sortConditions = [];

    /** @var Filter[] */
    protected $filters = [];

    /**
     * The data result.
     *
     * @var PaginatorAdapterInterface|null
     */
    protected $paginatorAdapter;

    /**
     * Set the columns.
     *
     * @param Column\AbstractColumn[] $columns
     * @return $this
     */
    public function setColumns(array $columns): DataSourceInterface
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return Column\AbstractColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Set sort conditions.
     *
     * @return $this
     */
    public function addSortCondition(Column\AbstractColumn $column, string $sortDirection = 'ASC'): DataSourceInterface
    {
        $this->sortConditions[] = [
            'column'        => $column,
            'sortDirection' => $sortDirection,
        ];

        return $this;
    }

    /**
     * @param array $sortConditions
     * @return $this
     */
    public function setSortConditions(array $sortConditions): self
    {
        $this->sortConditions = $sortConditions;

        return $this;
    }

    /**
     * @return array
     */
    public function getSortConditions(): array
    {
        return $this->sortConditions;
    }

    /**
     * Add a filter rule.
     *
     * @return $this
     */
    public function addFilter(Filter $filter): DataSourceInterface
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @param Filter[] $filters
     * @return $this
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return Filter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return $this
     */
    public function setPaginatorAdapter(?PaginatorAdapterInterface $paginator): self
    {
        $this->paginatorAdapter = $paginator;

        return $this;
    }

    public function getPaginatorAdapter(): ?PaginatorAdapterInterface
    {
        return $this->paginatorAdapter;
    }

    /**
     * Get the data back from construct.
     *
     * @return mixed
     */
    abstract public function getData();

    /**
     * Execute the query and set the paginator
     * - with sort statements
     * - with filters statements.
     */
    abstract public function execute();
}
