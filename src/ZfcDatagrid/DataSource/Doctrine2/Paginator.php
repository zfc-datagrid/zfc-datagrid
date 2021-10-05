<?php
/**
 * This is just a proxy to detect if we can use the "fast" Pagination
 * or if we use the "safe" variant by Doctrine2.
 */

namespace ZfcDatagrid\DataSource\Doctrine2;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as Doctrine2Paginator;
use Laminas\Paginator\Adapter\AdapterInterface;
use ZfcDatagrid\DataSource\Doctrine2\PaginatorFast as ZfcDatagridPaginator;

class Paginator implements AdapterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /**
     * Total item count.
     *
     * @var int|null
     */
    protected $rowCount;

    /** @var ZfcDatagridPaginator|Doctrine2Paginator|null */
    protected $paginator;

    /**
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->qb;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param int $offset
     * @param int $itemCountPerPage
     *
     * @return array
     */
    public function getItems($offset, $itemCountPerPage): array
    {
        $paginator = $this->getPaginator();
        if ($paginator instanceof Doctrine2Paginator) {
            $this->getQueryBuilder()
                ->setFirstResult($offset)
                ->setMaxResults($itemCountPerPage);

            return $paginator->getIterator()->getArrayCopy();
        }

        return $paginator->getItems($offset, $itemCountPerPage);
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->getPaginator()->count();
    }

    /**
     * Test which pagination solution to use.
     *
     * @return bool
     */
    protected function useCustomPaginator(): bool
    {
        $qb    = $this->getQueryBuilder();
        $parts = $qb->getDQLParts();

        if (null !== $parts['having'] || true === $parts['distinct']) {
            // never tried having in such queries...
            return false;
        }

        // @todo maybe more detection needed :-/
        return true;
    }

    /**
     * @return Doctrine2Paginator|ZfcDatagridPaginator
     */
    protected function getPaginator()
    {
        if (null !== $this->paginator) {
            return $this->paginator;
        }

        if ($this->useCustomPaginator() === true) {
            $this->paginator = new ZfcDatagridPaginator($this->getQueryBuilder());
        } else {
            // Doctrine2Paginator as fallback...they are using 3 queries
            $this->paginator = new Doctrine2Paginator($this->getQueryBuilder());
        }

        return $this->paginator;
    }
}
