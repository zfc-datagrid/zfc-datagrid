<?php
namespace ZfcDatagridTest\Column\Formatter;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Formatter\AbstractFormatter;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Formatter\AbstractFormatter
 */
class AbstractFormatterTest extends TestCase
{
    public function testGetValidRendererNames()
    {
        /** @var AbstractFormatter $formatter */
        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class);

        $this->assertEquals([], $formatter->getValidRendererNames());

        $formatter->setValidRendererNames([
            'jqGrid',
        ]);
        $this->assertEquals([
            'jqGrid',
        ], $formatter->getValidRendererNames());
    }

    public function testRowData()
    {
        /** @var AbstractFormatter $formatter */
        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class);
        $this->assertEquals([], $formatter->getRowData());

        $data = [
            'myCol'  => 123,
            'myCol2' => 'text',
        ];

        $formatter->setRowData($data);
        $this->assertEquals($data, $formatter->getRowData());
    }

    public function testRendererName()
    {
        /** @var AbstractFormatter $formatter */
        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class);

        $this->assertNull($formatter->getRendererName());

        $formatter->setRendererName('jqGrid');
        $this->assertEquals('jqGrid', $formatter->getRendererName());
    }

    public function testIsApply()
    {
        /** @var AbstractFormatter $formatter */
        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class);
        $formatter->setValidRendererNames([
            'jqGrid',
        ]);

        $formatter->setRendererName('jqGrid');
        $this->assertTrue($formatter->isApply());

        $formatter->setRendererName('tcpdf');
        $this->assertFalse($formatter->isApply());
    }

    public function testFormat()
    {
        /** @var AbstractFormatter $formatter */
        $formatter = $this->getMockForAbstractClass(AbstractFormatter::class);
        $formatter->setValidRendererNames([
            'jqGrid',
        ]);
        $data = [
            'myCol'  => 123,
            'myCol2' => 'text',
        ];
        $formatter->setRowData($data);

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('myCol');

        $formatter->setRendererName('tcpdf');
        $this->assertEquals(123, $formatter->format($col));

        //Null because the method is not implemented in AbstractClass!
        $formatter->setRendererName('jqGrid');
        $this->assertEquals(null, $formatter->format($col));
    }
}
