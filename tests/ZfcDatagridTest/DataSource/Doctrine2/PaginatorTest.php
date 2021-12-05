<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\Doctrine2;

use ArrayIterator;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use ZfcDatagrid\DataSource\Doctrine2\Paginator;
use ZfcDatagrid\DataSource\Doctrine2\PaginatorFast;
use ZfcDatagridTest\Util\TestBase;

use function rand;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\Doctrine2\Paginator
 */
class PaginatorTest extends TestBase
{
    /** @var string */
    protected $className = Paginator::class;

    public function testConstruct(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame($queryBuilder, $this->getProperty('qb'));
    }

    public function testGetQueryBuilder(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame($queryBuilder, $this->getMethod('getQueryBuilder')->invoke($this->getClass()));
    }

    /**
     * @param $having
     * @param $distinct
     * @dataProvider providerUseCustomPaginator
     */
    public function testUseCustomPaginator($having, $distinct, bool $expected): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDQLParts'])
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('getDQLParts')
            ->willReturn([
                'having'   => $having,
                'distinct' => $distinct,
            ]);

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame($expected, $this->getMethod('useCustomPaginator')->invoke($this->getClass()));
    }

    public function testGetPaginator(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockedConstructorArgList = [$queryBuilder];
        $this->mockedMethodList         = ['useCustomPaginator'];

        $class = $this->getClass();
        $class->expects($this->once())
            ->method('useCustomPaginator')
            ->willReturn(true);

        $this->assertInstanceOf(PaginatorFast::class, $this->getMethod('getPaginator')->invoke($this->getClass()));
        $this->assertInstanceOf(PaginatorFast::class, $this->getMethod('getPaginator')->invoke($this->getClass()));
    }

    public function testGetPaginatorFallBack(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockedConstructorArgList = [$queryBuilder];
        $this->mockedMethodList         = ['useCustomPaginator'];

        $class = $this->getClass();
        $class->expects($this->once())
            ->method('useCustomPaginator')
            ->willReturn(false);

        $this->assertInstanceOf(DoctrinePaginator::class, $this->getMethod('getPaginator')->invoke($this->getClass()));
        $this->assertInstanceOf(DoctrinePaginator::class, $this->getMethod('getPaginator')->invoke($this->getClass()));
    }

    public function testCount(): void
    {
        $paginator = $this->getMockBuilder(PaginatorFast::class)
            ->disableOriginalConstructor()
            ->setMethods(['count'])
            ->getMock();
        $count     = rand(1, 999);

        $paginator->expects($this->once())
            ->method('count')
            ->willReturn($count);
        $this->setProperty('paginator', $paginator);

        $this->assertSame($count, $this->getMethod('count')->invoke($this->getClass()));
    }

    public function testGetItems(): void
    {
        $paginator        = $this->getMockBuilder(PaginatorFast::class)
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMock();
        $items            = ['list'];
        $offset           = 10;
        $itemCountPerPage = 10;

        $paginator->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->setProperty('paginator', $paginator);

        $this->assertSame($items, $this->getMethod('getItems')->invokeArgs($this->getClass(), [$offset, $itemCountPerPage]));
    }

    public function testGetItemsWithDoctrine(): void
    {
        $paginator = $this->getMockBuilder(DoctrinePaginator::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIterator'])
            ->getMock();

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $paginator->expects($this->once())
            ->method('getIterator')
            ->willReturn(new ArrayIterator());

        $this->mockedConstructorArgList = [$queryBuilder];
        $this->setProperty('paginator', $paginator);

        $this->assertSame([], $this->getMethod('getItems')->invokeArgs($this->getClass(), [10, 10]));
    }

    /**
     * @return array
     */
    public function providerUseCustomPaginator(): array
    {
        return [
            [
                null,
                false,
                true,
            ],
            [
                null,
                true,
                false,
            ],
            [
                '',
                false,
                false,
            ],
            [
                '',
                true,
                false,
            ],
        ];
    }
}
