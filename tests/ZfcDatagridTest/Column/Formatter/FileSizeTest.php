<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Formatter;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Formatter;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Formatter\FileSize
 */
class FileSizeTest extends TestCase
{
    public function testGetValidRendererNames()
    {
        $formatter = new Formatter\FileSize();

        $this->assertEquals([], $formatter->getValidRendererNames());

        $formatter->setRendererName('something');

        // Always true!
        $this->assertTrue($formatter->isApply());
    }

    public function testGetFormattedValue()
    {
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('myCol');

        $formatter = new Formatter\FileSize();

        $formatter->setRowData([
            'myCol' => null,
        ]);
        $this->assertSame('', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => '',
        ]);
        $this->assertEquals('', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => null,
        ]);
        $this->assertSame('', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => 1,
        ]);
        $this->assertEquals('1.00 B', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => 1024,
        ]);
        $this->assertEquals('1.00 KB', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => 1030,
        ]);
        $this->assertEquals('1.01 KB', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => 1048576,
        ]);
        $this->assertEquals('1.00 MB', $formatter->getFormattedValue($col));

        $formatter->setRowData([
            'myCol' => 1073741824,
        ]);
        $this->assertEquals('1.00 GB', $formatter->getFormattedValue($col));
    }
}
