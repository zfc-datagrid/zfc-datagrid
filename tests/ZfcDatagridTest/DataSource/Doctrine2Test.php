<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use stdClass;
use TypeError;
use ZfcDatagrid\DataSource\Doctrine2;
use ZfcDatagrid\DataSource\Doctrine2\Paginator;
use ZfcDatagrid\Filter;
use ZfcDatagridTest\DataSource\Doctrine2\AbstractDoctrine2Test;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\Doctrine2
 */
class Doctrine2Test extends AbstractDoctrine2Test
{
    /** @var Doctrine2 */
    protected $source;

    protected $qb;

    public function setUp(): void
    {
        parent::setUp();

        $this->qb = $this->em->createQueryBuilder();

        $this->source = new Doctrine2($this->qb);
        $this->source->setColumns([
            $this->colVolumne,
            $this->colEdition,
            $this->colUserDisplayName,
        ]);
    }

    public function testConstruct()
    {
        $source = clone $this->source;

        $this->assertInstanceOf(QueryBuilder::class, $source->getData());
        $this->assertSame($this->qb, $source->getData());

        $this->expectException(TypeError::class);
        $source = new Doctrine2(new stdClass('something'));
    }

    public function testExecute()
    {
        $source = clone $this->source;

        $source->addSortCondition($this->colVolumne);
        $source->addSortCondition($this->colEdition, 'DESC');
        $source->execute();

        $this->assertInstanceOf(Paginator::class, $source->getPaginatorAdapter());
    }

    public function testFilter()
    {
        $source = clone $this->source;

        $this->assertNull($source->getData()
            ->getDQLPart('where'));

        /*
         * LIKE
         */
        $filter = new Filter();
        $filter->setFromColumn($this->colUserDisplayName, '~7');

        $source->addFilter($filter);
        $source->execute();

        $this->assertInstanceOf(Andx::class, $source->getData()
            ->getDQLPart('where'));
    }
}
