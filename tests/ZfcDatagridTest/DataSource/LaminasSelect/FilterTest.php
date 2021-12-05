<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\LaminasSelect;

use InvalidArgumentException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Driver\ConnectionInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\StatementInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Sql\Predicate\Between;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\Db\Sql\Predicate\Like;
use Laminas\Db\Sql\Predicate\Operator;
use Laminas\Db\Sql\Predicate\PredicateSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column;
use ZfcDatagrid\DataSource\LaminasSelect\Filter as FilterSelect;
use ZfcDatagrid\Filter;

use function count;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\LaminasSelect\Filter
 */
class FilterTest extends TestCase
{
    /** @var Column\AbstractColumn */
    private $column;

    /** @var Column\AbstractColumn */
    private $column2;

    /** @var FilterSelect */
    private $filterSelect;

    public function setUp(): void
    {
        $this->column = $this->getMockBuilder(Column\Select::class)->disableOriginalConstructor()->getMock();
        $this->column->method('getSelectPart1')
        ->willReturn('myCol');
        $this->column->method('getType')
        ->willReturn(new Column\Type\PhpString());

        $this->column->setUniqueId('myCol');
        $this->column->setSelect('myCol');

        $this->column2 = $this->getMockBuilder(Column\Select::class)->disableOriginalConstructor()->getMock();
        $this->column2->method('getSelectPart1')
        ->willReturn('myCol2');
        $this->column2->method('getType')
        ->willReturn(new Column\Type\PhpString());

        $this->column2->setUniqueId('myCol2');
        $this->column2->setSelect('myCol2');

        $this->mockDriver     = $this->getMockBuilder(DriverInterface::class)
            ->getMock();
        $this->mockConnection = $this->getMockBuilder(ConnectionInterface::class)
            ->getMock();
        $this->mockDriver->expects(self::any())
            ->method('checkEnvironment')
            ->will($this->returnValue(true));
        $this->mockDriver->expects(self::any())
            ->method('getConnection')
            ->will($this->returnValue($this->mockConnection));
        $this->mockPlatform  = $this->getMockBuilder(PlatformInterface::class)
            ->getMock();
        $this->mockStatement = $this->getMockBuilder(StatementInterface::class)
            ->getMock();
        $this->mockDriver->expects(self::any())
            ->method('createStatement')
            ->will($this->returnValue($this->mockStatement));

        $this->adapter = new Adapter($this->mockDriver, $this->mockPlatform);

        $sql = new Sql($this->adapter, 'foo');

        $select = new Select('myTable');
        $select->columns([
            'myCol',
            'myCol2',
        ]);

        $this->filterSelect = new FilterSelect($sql, $select);
    }

    public function testBasic()
    {
        $this->assertInstanceOf(Select::class, $this->filterSelect->getSelect());
        $this->assertInstanceOf(Sql::class, $this->filterSelect->getSql());

        // Test two filters
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~myValue,123');

        $filter2 = new Filter();
        $filter2->setFromColumn($this->column2, '~myValue,123');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);
        $filterSelect->applyFilter($filter2);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();
        $this->assertEquals(2, count($predicates));
    }

    /**
     * @param unknown $predicates
     * @param number  $part
     * @return Expression
     */
    private function getWherePart($predicates, $part = 0)
    {
        /** @var PredicateSet $predicateSet */
        $predicateSet = $predicates[0][1];

        $pred      = $predicateSet->getPredicates();
        $where     = $pred[$part][1];
        $wherePred = $where->getPredicates();

        return $wherePred[0][1];
    }

    public function testLike()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~myValue,123');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();
        $this->assertEquals(1, count($predicates));

        $like = $this->getWherePart($predicates, 0);
        $this->assertInstanceOf(Like::class, $like);
        $this->assertEquals('%myValue%', $like->getLike());

        $like = $this->getWherePart($predicates, 1);
        $this->assertInstanceOf(Like::class, $like);
        $this->assertEquals('%123%', $like->getLike());
    }

    public function testLikeLeft()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~%myValue,123');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $like = $this->getWherePart($predicates, 0);
        $this->assertInstanceOf(Like::class, $like);
        $this->assertEquals('%myValue', $like->getLike());

        $like = $this->getWherePart($predicates, 1);
        $this->assertInstanceOf(Like::class, $like);
        $this->assertEquals('%123', $like->getLike());
    }

    public function testLikeRight()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~myValue%');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $like = $this->getWherePart($predicates, 0);
        $this->assertInstanceOf(Like::class, $like);
        $this->assertEquals('myValue%', $like->getLike());
    }

    public function testNotLike()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!~myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $notLike    = $this->getWherePart($predicates, 0);
        $parameters = $notLike->getParameters();

        $this->assertInstanceOf(Expression::class, $notLike);
        $this->assertEquals('NOT LIKE ?', $notLike->getExpression());
        $this->assertEquals('%myValue%', $parameters[0]);
    }

    public function testNotLikeLeft()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!~%myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $notLike    = $this->getWherePart($predicates, 0);
        $parameters = $notLike->getParameters();

        $this->assertInstanceOf(Expression::class, $notLike);
        $this->assertEquals('NOT LIKE ?', $notLike->getExpression());
        $this->assertEquals('%myValue', $parameters[0]);
    }

    public function testNotLikeRight()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!~myValue%');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $notLike    = $this->getWherePart($predicates, 0);
        $parameters = $notLike->getParameters();

        $this->assertInstanceOf(Expression::class, $notLike);
        $this->assertEquals('NOT LIKE ?', $notLike->getExpression());
        $this->assertEquals('myValue%', $parameters[0]);
    }

    public function testEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Operator::class, $operator);
        $this->assertEquals(Operator::OP_EQ, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testNotEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Operator::class, $operator);
        $this->assertEquals(Operator::OP_NE, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testGreaterEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '>=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Operator::class, $operator);
        $this->assertEquals(Operator::OP_GTE, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testGreater()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '>myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Operator::class, $operator);
        $this->assertEquals(Operator::OP_GT, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testLessEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '<=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Operator::class, $operator);
        $this->assertEquals(Operator::OP_LTE, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testLess()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '<myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Operator::class, $operator);
        $this->assertEquals(Operator::OP_LT, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testBetween()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '3 <> myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /** @var Where $where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(Between::class, $operator);
        $this->assertEquals('myCol', $operator->getIdentifier());
        $this->assertEquals('3', $operator->getMinValue());
        $this->assertEquals('myValue', $operator->getMaxValue());
    }

    public function testException()
    {
        $filter = $this->getMockBuilder(Filter::class)
            ->getMock();
        $filter->expects(self::any())
            ->method('getColumn')
            ->will($this->returnValue($this->column));
        $filter->expects(self::any())
            ->method('getValues')
            ->will($this->returnValue([
                1,
            ]));
        $filter->expects(self::any())
            ->method('getOperator')
            ->will($this->returnValue(' () '));

        $this->expectException(InvalidArgumentException::class);
        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);
    }
}
