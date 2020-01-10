<?php
namespace ZfcDatagridTest\Column\Action;

use Exception;
use InvalidArgumentException;
use ZfcDatagrid\Column\Action\Button;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Action\Button
 */
class ButtonTest extends TestBase
{
    /** @var string */
    protected $className = Button::class;
    
    public function testConstruct()
    {
        $button = new Button();

        $this->assertEquals([
            'href'  => '#',
            'class' => 'btn btn-default',
        ], $button->getAttributes());
    }

    public function testLabelAndToHtml()
    {
        $button = new Button();

        $button->setLabel('My label');
        $this->assertEquals('My label', $button->getLabel());

        $html = '<a href="#" class="btn btn-default">My label</a>';
        $this->assertEquals($html, $button->toHtml([]));
    }

    public function testColumnLabelAndToHtml()
    {
        $col = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $col->setUniqueId('myCol');

        $button = new Button();

        $button->setLabel($col);
        $this->assertInstanceOf(\ZfcDatagrid\Column\AbstractColumn::class, $button->getLabel());

        $html = '<a href="#" class="btn btn-default">Blubb</a>';
        $this->assertEquals($html, $button->toHtml(['myCol' => 'Blubb']));
    }

    public function testHtmlException()
    {
        $button = new Button();

        $this->expectException(InvalidArgumentException::class);
        $button->toHtml([]);
    }

    public function testGetHtmlTypeException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not needed...since we have toHtml() here directly!');

        $this->getMethod('getHtmlType')->invoke($this->getClass());
    }
}
