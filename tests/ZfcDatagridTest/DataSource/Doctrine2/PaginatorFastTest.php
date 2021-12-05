<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\Doctrine2;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use ZfcDatagrid\DataSource\Doctrine2\PaginatorFast;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\Doctrine2\PaginatorFast
 */
class PaginatorFastTest extends TestBase
{
    /** @var string */
    protected $className = PaginatorFast::class;

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

    public function testGetItems(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'setFirstResult',
                'setMaxResults',
                'getQuery',
            ])
            ->getMock();

        $query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            true,
            true,
            ['getArrayResult']
        );
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn(['foobar']);

        $queryBuilder->expects($this->once())
            ->method('setFirstResult')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('setMaxResults')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame(['foobar'], $this->getMethod('getItems')->invokeArgs($this->getClass(), [10, 10]));
    }

    public function testCountWithRowCount(): void
    {
        $this->setProperty('rowCount', 10);

        $this->assertSame(10, $this->getMethod('count')->invoke($this->getClass()));
    }

    public function testCountWithMultiGroups(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDQLParts', 'getQuery'])
            ->getMock();

        $query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            true,
            true,
            ['getResult']
        );

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn([
                [
                    'uniqueParts' => 12,
                ],
            ]);

        $queryBuilder->setParameters([]);

        $queryBuilder->expects($this->once())
            ->method('getDQLParts')
            ->willReturn([
                'groupBy' => [
                    'test',
                    'group_foo',
                ],
            ]);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame(1, $this->getMethod('count')->invoke($this->getClass()));
    }

    public function testCountWithOneGroups(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDQLParts', 'getQuery'])
            ->getMock();

        $query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            true,
            true,
            ['getSingleScalarResult']
        );

        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(45);

        $queryBuilder->setParameters([]);

        $queryBuilder->expects($this->once())
            ->method('getDQLParts')
            ->willReturn([
                'groupBy' => [
                    'test',
                ],
            ]);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame(45, $this->getMethod('count')->invoke($this->getClass()));
    }

    public function testCountWithNoneGroups(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDQLParts', 'getQuery', 'getEntityManager'])
            ->getMock();

        $entityManager = $this->getMockForAbstractClass(
            EntityManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getConfiguration']
        );

        $config = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomStringFunction'])
            ->getMock();

        $entityManager->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($config);

        $query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            true,
            true,
            ['getSingleScalarResult']
        );

        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(12);

        $queryBuilder->setParameters([]);

        $queryBuilder->expects($this->once())
            ->method('getDQLParts')
            ->willReturn([
                'groupBy' => [],
                'from'    => [
                    new class () {
                        public function getAlias(): string
                        {
                            return 'as';
                        }
                    },
                ],
            ]);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $queryBuilder->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame(12, $this->getMethod('count')->invoke($this->getClass()));
    }

    public function testCountWithNoneGroupsButConfig(): void
    {
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDQLParts', 'getQuery', 'getEntityManager'])
            ->getMock();

        $entityManager = $this->getMockForAbstractClass(
            EntityManagerInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getConfiguration']
        );

        $config = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomStringFunction'])
            ->getMock();

        $config->expects($this->once())
            ->method('getCustomStringFunction')
            ->willReturn('custom');

        $entityManager->expects($this->once())
            ->method('getConfiguration')
            ->willReturn($config);

        $query = $this->getMockForAbstractClass(
            AbstractQuery::class,
            [],
            '',
            false,
            true,
            true,
            ['getSingleScalarResult']
        );

        $query->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(456);

        $queryBuilder->setParameters([]);

        $queryBuilder->expects($this->once())
            ->method('getDQLParts')
            ->willReturn([
                'groupBy' => [],
            ]);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);
        $queryBuilder->expects($this->once())
            ->method('getEntityManager')
            ->willReturn($entityManager);

        $this->mockedConstructorArgList = [$queryBuilder];

        $this->assertSame(456, $this->getMethod('count')->invoke($this->getClass()));
    }
}
