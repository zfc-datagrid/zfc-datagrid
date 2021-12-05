<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\Style;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\Style;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Style\Italic
 */
class ItalicTest extends TestCase
{
    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(Style\Italic::class, new Style\Italic());
    }
}
