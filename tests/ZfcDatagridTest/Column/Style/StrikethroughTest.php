<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Style;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\Style;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Style\Strikethrough
 */
class StrikethroughTest extends TestCase
{
    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(Style\Strikethrough::class, new Style\Strikethrough());
    }
}
