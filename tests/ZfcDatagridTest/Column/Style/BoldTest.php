<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Style;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\Style;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Style\Bold
 */
class BoldTest extends TestCase
{
    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(Style\Bold::class, new Style\Bold());
    }
}
