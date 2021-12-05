<?php

declare(strict_types=1);

namespace ZfcDatagrid\DataSource;

use Doctrine\ORM;
use Doctrine\ORM\Query\Expr;
use Exception;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Select;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\DataSource\Doctrine2\Paginator as PaginatorAdapter;
use ZfcDatagrid\Filter;

class Doctrine2 extends AbstractDataSource
{
    /** @var ORM\QueryBuilder */
    private $qb;

    /**
     * Data source.
     */
    public function __construct(ORM\QueryBuilder $data)
    {
        $this->qb = $data;
    }

    public function getData(): ORM\QueryBuilder
    {
        return $this->qb;
    }

    /**
     * @throws Exception
     */
    public function execute()
    {
        $qb = $this->getData();

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
                $colString .= '.' . $col->getSelectPart2();
            }
            $colString .= ' ' . $col->getUniqueId();

            $selectColumns[] = $colString;
        }
        $qb->resetDQLPart('select');
        $qb->select($selectColumns);

        /*
         * Step 2) Apply sorting
         */
        if (! empty($this->getSortConditions())) {
            // Minimum one sort condition given -> so reset the default orderBy
            $qb->resetDQLPart('orderBy');

            foreach ($this->getSortConditions() as $key => $sortCondition) {
                /** @var AbstractColumn $col */
                $col = $sortCondition['column'];

                if (! $col instanceof Column\Select) {
                    throw new Exception('This column cannot be sorted: ' . $col->getUniqueId());
                }

                /** @var Select $col */
                $colString = $col->getSelectPart1();
                if ($col->getSelectPart2() != '') {
                    $colString .= '.' . $col->getSelectPart2();
                }

                if ($col->getType() instanceof Type\Number) {
                    $qb->addSelect('ABS(' . $colString . ') sortColumn' . $key);
                    $qb->add('orderBy', new Expr\OrderBy('sortColumn' . $key, $sortCondition['sortDirection']), true);
                } else {
                    $qb->add('orderBy', new Expr\OrderBy($col->getUniqueId(), $sortCondition['sortDirection']), true);
                }
            }
        }

        /*
         * Step 3) Apply filters
         */
        $filterColumn = new Doctrine2\Filter($qb);
        foreach ($this->getFilters() as $filter) {
            /** @var Filter $filter */
            if ($filter->isColumnFilter() === true) {
                $filterColumn->applyFilter($filter);
            }
        }

        /*
         * Step 4) Pagination
         */
        $this->setPaginatorAdapter(new PaginatorAdapter($qb));
    }
}
