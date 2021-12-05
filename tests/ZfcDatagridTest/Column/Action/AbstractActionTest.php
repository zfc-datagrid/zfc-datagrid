<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Action;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Action\AbstractAction;
use ZfcDatagrid\Filter;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Action\AbstractAction
 */
class AbstractActionTest extends TestCase
{
    /** @var AbstractColumn */
    private $column;

    public function setUp(): void
    {
        $this->column = $this->getMockForAbstractClass(AbstractColumn::class);
        $this->column->setUniqueId('colName');
    }

    public function testLink()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->assertEquals('#', $action->getLink());
        $this->assertEquals('#', $action->getAttribute('href'));

        $action->setLink('/my/page/is/cool');
        $this->assertEquals('/my/page/is/cool', $action->getLink());
    }

    public function testLinkPlaceholder()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $action->setLink('/myLink/id/' . $action->getRowIdPlaceholder());
        $this->assertEquals('/myLink/id/:rowId:', $action->getLink());

        $this->assertEquals('/myLink/id/3', $action->getLinkReplaced([
            'idConcated' => 3,
        ]));

        // Column
        $column = $this->getMockForAbstractClass(AbstractColumn::class);
        $column->setUniqueId('myCol');

        $action->setLink('/myLink/para1/' . $action->getColumnValuePlaceholder($column));
        $this->assertEquals('/myLink/para1/:myCol:', $action->getLink());

        $this->assertEquals('/myLink/para1/someValue', $action->getLinkReplaced([
            'myCol' => 'someValue',
        ]));
    }

    public function testRoute()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $action->setRoute('my_route');
        $this->assertEquals('my_route', $action->getRoute());

        $action->setRouteParams([
            'id'  => 1,
            'foo' => 'bar',
        ]);

        $this->assertEquals([
            'id'  => 1,
            'foo' => 'bar',
        ], $action->getRouteParams());
    }

    public function testToHtml()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);
        $action->expects(self::any())
            ->method('getHtmlType')
            ->will($this->returnValue(''));

        $this->assertEquals('<a href="#"></a>', $action->toHtml([]));
    }

    public function testAttributes()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->assertCount(1, $action->getAttributes());
        $this->assertEquals([
            'href' => '#',
        ], $action->getAttributes());

        $this->assertEquals('', $action->getAttribute('something'));

        $action->setAttribute('class', 'error');
        $this->assertCount(2, $action->getAttributes());
        $this->assertEquals([
            'href'  => '#',
            'class' => 'error',
        ], $action->getAttributes());

        $this->assertEquals('error', $action->getAttribute('class'));

        $action->removeAttribute('class');
        $this->assertEquals('', $action->getAttribute('class'));
    }

    public function testTitle()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->assertEquals('', $action->getTitle());

        $action->setTitle('This is my action');
        $this->assertEquals('This is my action', $action->getTitle());
    }

    public function testAddClass()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->assertEquals('', $action->getAttribute('class'));

        $action->addClass('cssClass');
        $this->assertEquals('cssClass', $action->getAttribute('class'));

        $action->addClass('cssClass2');
        $this->assertEquals('cssClass cssClass2', $action->getAttribute('class'));
    }

    public function testShowOnValue()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->assertCount(0, $action->getShowOnValues());

        $this->assertFalse($action->hasShowOnValues());
        $action->addShowOnValue($this->column, '23', Filter::EQUAL);
        $this->assertTrue($action->hasShowOnValues());
    }

    public function testIsDisplayed()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->assertTrue($action->isDisplayed([
            $this->column->getUniqueId() => '23',
        ]));

        // EQUAL
        $action->addShowOnValue($this->column, '23', Filter::EQUAL);

        $this->assertTrue($action->isDisplayed([
            $this->column->getUniqueId() => '23',
        ]));

        $this->assertFalse($action->isDisplayed([
            $this->column->getUniqueId() => '33',
        ]));
    }

    public function testIsDisplayedNotEqual()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $action->addShowOnValue($this->column, '23', Filter::NOT_EQUAL);

        $this->assertTrue($action->isDisplayed([
            $this->column->getUniqueId() => '32',
        ]));

        $this->assertFalse($action->isDisplayed([
            $this->column->getUniqueId() => '23',
        ]));
    }

    public function testIsDisplayedAndOperatorDisplay()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $action->setShowOnValueOperator('AND');

        $this->assertTrue($action->isDisplayed([
            $this->column->getUniqueId() => '23',
        ]));

        $action->addShowOnValue($this->column, '23', Filter::EQUAL);
        $action->addShowOnValue($this->column, '24', Filter::NOT_EQUAL);

        $this->assertTrue($action->isDisplayed([
            $this->column->getUniqueId() => '23',
        ]));
    }

    public function testIsDisplayedAndOperatorNoDisplay()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $action->setShowOnValueOperator('AND');

        $action->addShowOnValue($this->column, '23', Filter::EQUAL);
        $action->addShowOnValue($this->column, '23', Filter::NOT_EQUAL);

        $this->assertFalse($action->isDisplayed([
            $this->column->getUniqueId() => '23',
        ]));

        $this->assertFalse($action->isDisplayed([
            $this->column->getUniqueId() => '33',
        ]));
    }

    public function testSetShowOnValueOperatorException()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $this->expectException(InvalidArgumentException::class);
        $action->setShowOnValueOperator('XOR');
    }

    public function testIsDisplayedException()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $action->addShowOnValue($this->column, '23', 'UNknownFilter');

        $this->expectException(InvalidArgumentException::class);
        $action->isDisplayed([
            $this->column->getUniqueId() => '32',
        ]);
    }

    public function testIsDisplayedByColumn()
    {
        /** @var AbstractAction $action */
        $action = $this->getMockForAbstractClass(AbstractAction::class);

        $columnCompare = clone $this->column;
        $columnCompare->setUniqueId('columnCompare');

        $action->addShowOnValue($this->column, $columnCompare, Filter::GREATER_EQUAL);
        $this->assertEquals([
            [
                'column'     => $this->column,
                'value'      => $columnCompare,
                'comparison' => Filter::GREATER_EQUAL,
            ],
        ], $action->getShowOnValues());

        $this->assertTrue($action->hasShowOnValues());

        // Test lower value
        $row = [
            $this->column->getUniqueId()  => 5,
            $columnCompare->getUniqueId() => 15,
        ];
        $this->assertFalse($action->isDisplayed($row));

        // Test greater value
        $row = [
            $this->column->getUniqueId()  => 15,
            $columnCompare->getUniqueId() => 10,
        ];
        $this->assertTrue($action->isDisplayed($row));

        // Test row without compared column
        $row = [
            $this->column->getUniqueId() => 15,
        ];
        $this->assertTrue($action->isDisplayed($row));
    }
}
