<?php
namespace ZfcDatagrid\DataSource;

use Zend\Paginator\Adapter\AdapterInterface;
use ZfcDatagrid\Column;
use ZfcDatagrid\Filter;

interface DataSourceInterface
{
    /**
     * Get the data back from construct.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Execute the query and set the paginator
     * - with sort statements
     * - with filters statements.
     */
    public function execute();

    /**
     * Set the columns.
     *
     * @param array $columns
     */
    public function setColumns(array $columns);

    /**
     * Set sort conditions.
     *
     * @param Column\AbstractColumn $column
     * @param string                $sortDirection
     */
    public function addSortCondition(Column\AbstractColumn $column, string $sortDirection = 'ASC');

    /**
     * @param Filter $filters
     */
    public function addFilter(Filter $filter);

    /**
     * @return AdapterInterface
     */
    public function getPaginatorAdapter(): ?AdapterInterface;
}
