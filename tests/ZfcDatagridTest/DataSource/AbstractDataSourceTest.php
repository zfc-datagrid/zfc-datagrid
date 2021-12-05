<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource;

use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\DataSource\AbstractDataSource;
use ZfcDatagrid\Filter;

/**
 * @covers \ZfcDatagrid\DataSource\AbstractDataSource
 */
class AbstractDataSourceTest extends TestCase
{
    /** @var AbstractDataSource */
    private $dsMock;

    public function setUp(): void
    {
        $this->dsMock = $this->getMockForAbstractClass(AbstractDataSource::class, [
            [],
        ], '', false);
    }

    public function testDefaults()
    {
        $ds = clone $this->dsMock;

        $this->assertEquals([], $ds->getColumns());
        $this->assertEquals([], $ds->getSortConditions());
        $this->assertEquals([], $ds->getFilters());
        $this->assertNull($ds->getPaginatorAdapter());
    }

    public function testColumn()
    {
        $ds = clone $this->dsMock;

        $col1 = $this->getMockForAbstractClass(AbstractColumn::class);
        $col1->setUniqueId('test');
        $col2 = $this->getMockForAbstractClass(AbstractColumn::class);
        $col2->setUniqueId('test2');
        $columns = [
            $col1->getUniqueId() => $col1,
            $col2->getUniqueId() => $col2,
        ];
        $ds->setColumns($columns);

        $this->assertArrayHasKey($col1->getUniqueId(), $ds->getColumns());
        $this->assertArrayHasKey($col2->getUniqueId(), $ds->getColumns());
        $this->assertCount(2, $ds->getColumns());
    }

    public function testSortCondition()
    {
        $ds = clone $this->dsMock;

        $col1 = $this->getMockForAbstractClass(AbstractColumn::class);
        $col2 = $this->getMockForAbstractClass(AbstractColumn::class);

        $ds->addSortCondition($col1, 'ASC');

        $this->assertEquals([
            [
                'column'        => $col1,
                'sortDirection' => 'ASC',
            ],
        ], $ds->getSortConditions());

        $ds->addSortCondition($col2, 'DESC');

        $this->assertEquals([
            [
                'column'        => $col1,
                'sortDirection' => 'ASC',
            ],
            [
                'column'        => $col2,
                'sortDirection' => 'DESC',
            ],
        ], $ds->getSortConditions());

        $ds->setSortConditions([]);
        $this->assertEquals([], $ds->getSortConditions());
    }

    public function testFilter()
    {
        $ds = clone $this->dsMock;

        $filter = $this->getMockBuilder(Filter::class)
            ->getMock();
        $ds->addFilter($filter);

        $this->assertEquals([
            $filter,
        ], $ds->getFilters());

        $ds->setFilters([]);
        $this->assertEquals([], $ds->getFilters());
    }

    public function testPaginatorAdapter()
    {
        $ds = clone $this->dsMock;

        $adapter = $this->getMockBuilder(ArrayAdapter::class)
            ->getMock();
        $ds->setPaginatorAdapter($adapter);

        $this->assertInstanceOf(AdapterInterface::class, $ds->getPaginatorAdapter());
        $this->assertEquals($adapter, $ds->getPaginatorAdapter());
    }
}
