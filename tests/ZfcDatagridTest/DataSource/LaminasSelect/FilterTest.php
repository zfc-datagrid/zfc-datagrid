<?php
namespace ZfcDatagridTest\DataSource\LaminasSelect;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Predicate\Operator;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use ZfcDatagrid\Column;
use ZfcDatagrid\DataSource\LaminasSelect\Filter as FilterSelect;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\LaminasSelect\Filter
 */
class FilterTest extends TestCase
{
    /**
     *
     * @var Column\AbstractColumn
     */
    private $column;

    /**
     *
     * @var Column\AbstractColumn
     */
    private $column2;

    /**
     *
     * @var FilterSelect
     */
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

        $this->mockDriver     = $this->getMockBuilder(\Laminas\Db\Adapter\Driver\DriverInterface::class)
            ->getMock();
        $this->mockConnection = $this->getMockBuilder(\Laminas\Db\Adapter\Driver\ConnectionInterface::class)
            ->getMock();
        $this->mockDriver->expects(self::any())
            ->method('checkEnvironment')
            ->will($this->returnValue(true));
        $this->mockDriver->expects(self::any())
            ->method('getConnection')
            ->will($this->returnValue($this->mockConnection));
        $this->mockPlatform  = $this->getMockBuilder(\Laminas\Db\Adapter\Platform\PlatformInterface::class)
            ->getMock();
        $this->mockStatement = $this->getMockBuilder(\Laminas\Db\Adapter\Driver\StatementInterface::class)
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
        $this->assertInstanceOf(\Laminas\Db\Sql\Select::class, $this->filterSelect->getSelect());
        $this->assertInstanceOf(\Laminas\Db\Sql\Sql::class, $this->filterSelect->getSql());

        // Test two filters
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '~myValue,123');

        $filter2 = new \ZfcDatagrid\Filter();
        $filter2->setFromColumn($this->column2, '~myValue,123');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);
        $filterSelect->applyFilter($filter2);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();
        $this->assertEquals(2, count($predicates));
    }

    /**
     *
     * @param unknown $predicates
     * @param number  $part
     *
     * @return \Laminas\Db\Sql\Predicate\Expression
     */
    private function getWherePart($predicates, $part = 0)
    {
        /* @var $predicateSet \Laminas\Db\Sql\Predicate\PredicateSet */
        $predicateSet = $predicates[0][1];

        $pred      = $predicateSet->getPredicates();
        $where     = $pred[$part][1];
        $wherePred = $where->getPredicates();

        return $wherePred[0][1];
    }

    public function testLike()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '~myValue,123');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();
        $this->assertEquals(1, count($predicates));

        $like = $this->getWherePart($predicates, 0);
        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Like::class, $like);
        $this->assertEquals('%myValue%', $like->getLike());

        $like = $this->getWherePart($predicates, 1);
        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Like::class, $like);
        $this->assertEquals('%123%', $like->getLike());
    }

    public function testLikeLeft()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '~%myValue,123');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $like = $this->getWherePart($predicates, 0);
        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Like::class, $like);
        $this->assertEquals('%myValue', $like->getLike());

        $like = $this->getWherePart($predicates, 1);
        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Like::class, $like);
        $this->assertEquals('%123', $like->getLike());
    }

    public function testLikeRight()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '~myValue%');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $like = $this->getWherePart($predicates, 0);
        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Like::class, $like);
        $this->assertEquals('myValue%', $like->getLike());
    }

    public function testNotLike()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '!~myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $notLike    = $this->getWherePart($predicates, 0);
        $parameters = $notLike->getParameters();

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Expression::class, $notLike);
        $this->assertEquals('NOT LIKE ?', $notLike->getExpression());
        $this->assertEquals('%myValue%', $parameters[0]);
    }

    public function testNotLikeLeft()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '!~%myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $notLike    = $this->getWherePart($predicates, 0);
        $parameters = $notLike->getParameters();

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Expression::class, $notLike);
        $this->assertEquals('NOT LIKE ?', $notLike->getExpression());
        $this->assertEquals('%myValue', $parameters[0]);
    }

    public function testNotLikeRight()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '!~myValue%');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $notLike    = $this->getWherePart($predicates, 0);
        $parameters = $notLike->getParameters();

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Expression::class, $notLike);
        $this->assertEquals('NOT LIKE ?', $notLike->getExpression());
        $this->assertEquals('myValue%', $parameters[0]);
    }

    public function testEqual()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Operator::class, $operator);
        $this->assertEquals(Operator::OP_EQ, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testNotEqual()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '!=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Operator::class, $operator);
        $this->assertEquals(Operator::OP_NE, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testGreaterEqual()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '>=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Operator::class, $operator);
        $this->assertEquals(Operator::OP_GTE, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testGreater()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '>myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Operator::class, $operator);
        $this->assertEquals(Operator::OP_GT, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testLessEqual()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '<=myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Operator::class, $operator);
        $this->assertEquals(Operator::OP_LTE, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testLess()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '<myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Operator::class, $operator);
        $this->assertEquals(Operator::OP_LT, $operator->getOperator());
        $this->assertEquals('myCol', $operator->getLeft());
        $this->assertEquals('myValue', $operator->getRight());
    }

    public function testBetween()
    {
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($this->column, '3 <> myValue');

        $filterSelect = clone $this->filterSelect;
        $filterSelect->applyFilter($filter);

        $select = $filterSelect->getSelect();
        /* @var $where \Laminas\Db\Sql\Where */
        $where = $select->getRawState('where');

        $predicates = $where->getPredicates();

        $operator = $this->getWherePart($predicates, 0);

        $this->assertInstanceOf(\Laminas\Db\Sql\Predicate\Between::class, $operator);
        $this->assertEquals('myCol', $operator->getIdentifier());
        $this->assertEquals('3', $operator->getMinValue());
        $this->assertEquals('myValue', $operator->getMaxValue());
    }

    public function testException()
    {
        $filter = $this->getMockBuilder(\ZfcDatagrid\Filter::class)
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
