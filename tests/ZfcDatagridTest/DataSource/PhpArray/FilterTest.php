<?php

declare(strict_types=1);

namespace ZfcDatagridTest\DataSource\PhpArray;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\DataSource\PhpArray\Filter as FilterArray;
use ZfcDatagrid\Filter;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\PhpArray\Filter
 */
class FilterTest extends TestCase
{
    /** @var AbstractColumn */
    private $column;

    public function setUp(): void
    {
        $this->column = $this->getMockForAbstractClass(AbstractColumn::class);
        $this->column->setUniqueId('myCol');
    }

    public function testConstruct()
    {
        /** @var Filter $filter */
        $filter = $this->getMockBuilder(Filter::class)
            ->getMock();
        $filter->setFromColumn($this->column, 'myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertInstanceOf(Filter::class, $filterArray->getFilter());
    }

    public function testLike()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~myValue,123');
        $this->assertEquals(Filter::LIKE, $filter->getOperator());

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '1234',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '51237',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testLikeLeft()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~%myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'asdfsdf123',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'something.... myValue',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '1234',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '51237',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testLikeRight()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '~myValue,123%');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123asdf',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'myValue....something',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'something.... myValue',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '4123',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '51237',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    /**
     * Test NOT LIKE is just a copy from testLike -> because it's just swapped
     */
    public function testNotLike()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!~myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '1234',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '51237',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testNotLikeLeft()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!~%myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'asdfsdf123',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'something.... myValue',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '1234',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '51237',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testNotLikeRight()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!~myValue,123%');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123asdf',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'myValue....something',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'something.... myValue',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '4123',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '51237',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '=myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'myValue',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'myvalue',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '1234',
        ]));
    }

    public function testNotEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!=myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'myValue',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'myvalue',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '1234',
        ]));
    }

    public function testGreaterEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '>=myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '322',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '11',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '00',
        ]));
    }

    public function testGreater()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '>myValue,123');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '322',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '11',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '00',
        ]));
    }

    public function testLessEqual()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '<=123');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '11',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '00',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '322',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'ZZZ',
        ]));
    }

    public function testLess()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '<123');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '322',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '11',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '00',
        ]));
    }

    public function testIN()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '=(myValue,123)');

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => 'myValue',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testNotIN()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '!=(myValue,123)');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => 'myValue',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '123',
        ]));

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '321',
        ]));
    }

    public function testBetween()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, '15 <> 30');
        $this->assertEquals(Filter::BETWEEN, $filter->getOperator());

        $filterArray = new FilterArray($filter);

        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '15',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '20',
        ]));
        $this->assertTrue($filterArray->applyFilter([
            'myCol' => '30',
        ]));

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '14',
        ]));
        $this->assertFalse($filterArray->applyFilter([
            'myCol' => '31',
        ]));
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

        $filterArray = new FilterArray($filter);

        $this->expectException(InvalidArgumentException::class);
        $filterArray->applyFilter([
            'myCol' => '15',
        ]);
    }

    public function testDefaultOperatorWithNullValue()
    {
        $filter = new Filter();
        $filter->setFromColumn($this->column, 'test');

        $filterArray = new FilterArray($filter);

        $this->assertFalse($filterArray->applyFilter([
            'myCol' => null,
        ]));
    }
}
