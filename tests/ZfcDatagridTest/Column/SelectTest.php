<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column;

use Exception;
use Laminas\Db\Sql\Expression;
use PHPUnit\Framework\TestCase;
use stdClass;
use ZfcDatagrid\Column;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Select
 */
class SelectTest extends TestCase
{
    public function testConstructDefaultBoth()
    {
        $col = new Column\Select('id', 'user');

        $this->assertEquals('user_id', $col->getUniqueId());
        $this->assertEquals('user', $col->getSelectPart1());
        $this->assertEquals('id', $col->getSelectPart2());
    }

    public function testConstructDefaultSingle()
    {
        $col = new Column\Select('title');

        $this->assertEquals('title', $col->getUniqueId());
        $this->assertEquals('title', $col->getSelectPart1());
    }

    public function testSelectPart12()
    {
        $col = new Column\Select('id', 'user');

        $col->setSelect('id', 'user');
        $this->assertEquals('id', $col->getSelectPart1());
        $this->assertEquals('user', $col->getSelectPart2());
    }

    public function testObject()
    {
        $expr = new Expression('Something...');
        $col  = new Column\Select($expr, 'myAlias');

        $this->assertEquals($expr, $col->getSelectPart1());
        $this->assertEquals('myAlias', $col->getUniqueId());
    }

    public function testException()
    {
        $expr = new Expression('Something...');

        $this->expectException(Exception::class);
        $col = new Column\Select($expr);
    }

    public function testExceptionNotString()
    {
        $expr = new Expression('Something...');

        $this->expectException(Exception::class);
        $col = new Column\Select($expr, new stdClass());
    }

    public function testGetFilterSelectExpression()
    {
        $col = new Column\Select('id', 'user');

        $this->assertFalse($col->hasFilterSelectExpression());
        $this->assertNull($col->getFilterSelectExpression());

        $col->setFilterSelectExpression('CONCAT(%s)');
        $this->assertEquals('CONCAT(%s)', $col->getFilterSelectExpression());
        $this->assertTrue($col->hasFilterSelectExpression());
    }
}
