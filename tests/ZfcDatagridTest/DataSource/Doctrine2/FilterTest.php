<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\Doctrine2;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use ZfcDatagrid\DataSource\Doctrine2\Filter as FilterDoctrine2;
use ZfcDatagrid\Filter;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\Doctrine2\Filter
 */
class FilterTest extends AbstractDoctrine2Test
{
    /** @var FilterDoctrine2 */
    private $filterDoctrine2;

    public function setUp(): void
    {
        parent::setUp();

        $qb                    = $this->em->createQueryBuilder();
        $this->filterDoctrine2 = new FilterDoctrine2($qb);
    }

    public function testBasic()
    {
        $this->assertInstanceOf(QueryBuilder::class, $this->filterDoctrine2->getQueryBuilder());

        // Test two filters
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '~myValue,123');

        $filter2 = new Filter();
        $filter2->setFromColumn($this->colEdition, '~456');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);
        $filterDoctrine2->applyFilter($filter2);

        /** @var Andx $where */
        $where = $filterDoctrine2->getQueryBuilder()->getDQLPart('where');

        $this->assertEquals(2, $where->count());
        $this->assertInstanceOf(Andx::class, $where);

        $whereParts = $where->getParts();

        /** @var Orx $wherePart1 */
        $wherePart1 = $whereParts[0];

        $this->assertEquals(2, $wherePart1->count());
        $this->assertInstanceOf(Orx::class, $wherePart1);

        /** @var Orx $wherePart2 */
        $wherePart2 = $whereParts[1];

        $this->assertEquals(1, $wherePart2->count());
        $this->assertInstanceOf(Orx::class, $wherePart2);
    }

    /**
     * @param  number                                $part
     * @return Comparison[]
     */
    private function getWhereParts(QueryBuilder $qb, $part = 0)
    {
        /** @var Andx $where */
        $where = $qb->getDQLPart('where');

        $whereParts = $where->getParts();

        $this->assertInstanceOf(Orx::class, $whereParts[$part]);

        return $whereParts[$part]->getParts();
    }

    /**
     * @return Parameter[]
     */
    private function getParameters(FilterDoctrine2 $filter)
    {
        return $filter->getQueryBuilder()->getParameters();
    }

    public function testLike()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '~myV\'alue,123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('LIKE', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('%myV\'alue%', $parameters[0]->getValue());

        $this->assertEquals('volume', $whereParts[1]->getLeftExpr());
        $this->assertEquals('LIKE', $whereParts[1]->getOperator());
        $this->assertEquals(':volume1', $whereParts[1]->getRightExpr());
        $this->assertEquals('%123%', $parameters[1]->getValue());
    }

    public function testLikeLeft()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '~%123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('LIKE', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('%123', $parameters[0]->getValue());
    }

    public function testLikeRight()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '~123%');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('LIKE', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('123%', $parameters[0]->getValue());
    }

    public function testNotLike()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '!~123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('NOT LIKE', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('%123%', $parameters[0]->getValue());
    }

    public function testNotLikeLeft()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '!~%123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('NOT LIKE', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('%123', $parameters[0]->getValue());
    }

    public function testNotLikeRight()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '!~123%');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('NOT LIKE', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('123%', $parameters[0]->getValue());
    }

    public function testEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '=123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('=', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('123', $parameters[0]->getValue());
    }

    public function testNotEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '!=a String');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('<>', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('a String', $parameters[0]->getValue());
    }

    public function testGreaterEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '>=123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('>=', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('123', $parameters[0]->getValue());
    }

    public function testGreater()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '>123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('>', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('123', $parameters[0]->getValue());
    }

    public function testLessEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '<=string');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('<=', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('string', $parameters[0]->getValue());
    }

    public function testLess()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '<123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume', $whereParts[0]->getLeftExpr());
        $this->assertEquals('<', $whereParts[0]->getOperator());
        $this->assertEquals(':volume0', $whereParts[0]->getRightExpr());
        $this->assertEquals('123', $parameters[0]->getValue());
    }

    public function testBetween()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '789 <> 123');

        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);

        $whereParts = $this->getWhereParts($filterDoctrine2->getQueryBuilder());
        $parameters = $this->getParameters($filterDoctrine2);

        $this->assertEquals('volume BETWEEN :volume0 AND :volume1', $whereParts[0]);
        $this->assertEquals('123', $parameters[0]->getValue());
        $this->assertEquals('789', $parameters[1]->getValue());
    }

    public function testException()
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->getMock();
        $filter->expects(self::any())
            ->method('getColumn')
            ->will($this->returnValue($this->colVolumne));
        $filter->expects(self::any())
            ->method('getValues')
            ->will($this->returnValue([
                1,
            ]));
        $filter->expects(self::any())
            ->method('getOperator')
            ->will($this->returnValue(' () '));

        $this->expectException(InvalidArgumentException::class);
        $filterDoctrine2 = clone $this->filterDoctrine2;
        $filterDoctrine2->applyFilter($filter);
    }
}
