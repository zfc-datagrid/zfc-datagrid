<?php
namespace ZfcDatagridTest\Column\Formatter;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\Formatter;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Formatter\Link
 */
class LinkTest extends TestCase
{
    public function testGetValidRendererNames()
    {
        $formatter = new Formatter\Link();

        $this->assertEquals([
            'jqGrid',
            'bootstrapTable',
        ], $formatter->getValidRendererNames());
    }

    public function testGetFormattedValue()
    {
        $col = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $col->setUniqueId('myCol');

        $formatter = new Formatter\Link();
        $formatter->setRowData([
            'myCol' => 'http://example.com',
        ]);

        $this->assertEquals('<a href="http://example.com">http://example.com</a>', $formatter->getFormattedValue($col));
    }
}
