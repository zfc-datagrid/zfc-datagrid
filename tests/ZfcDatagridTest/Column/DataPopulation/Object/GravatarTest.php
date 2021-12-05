<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column\DataPopulation\Object;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\DataPopulation\Object\Gravatar;

use function md5;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\DataPopulation\Object\Gravatar
 */
class GravatarTest extends TestCase
{
    public function testAll()
    {
        $gravatar = new Gravatar();

        // DEFAULT
        $this->assertEquals('http://www.gravatar.com/avatar/', $gravatar->toString());

        // valid email
        $gravatar->setParameterFromColumn('email', 'martin.keckeis1@gmail.com');
        $this->assertEquals(
            'http://www.gravatar.com/avatar/' . md5('martin.keckeis1@gmail.com'),
            $gravatar->toString()
        );
    }

    public function testException()
    {
        $gravatar = new Gravatar();

        $this->expectException(InvalidArgumentException::class);
        $gravatar->setParameterFromColumn('invalidPara', 'someValue');
    }
}
