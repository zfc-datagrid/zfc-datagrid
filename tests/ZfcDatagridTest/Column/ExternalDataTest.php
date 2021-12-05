<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Column;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\DataPopulation;
use ZfcDatagrid\Column\DataPopulation\DataObject;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\ExternalData
 */
class ExternalDataTest extends TestCase
{
    public function testConstruct()
    {
        $col = new Column\ExternalData('myData');

        $this->assertEquals('myData', $col->getUniqueId());

        $this->assertFalse($col->isUserFilterEnabled());
        $this->assertFalse($col->isUserSortEnabled());
    }

    public function testGetDataPopulationException()
    {
        $col = new Column\ExternalData('myData');

        $this->expectException(InvalidArgumentException::class);
        $col->getDataPopulation();
    }

    public function testSetGetData()
    {
        $col = new Column\ExternalData('myData');

        $object = new DataPopulation\DataObject();
        $object->setObject(new DataPopulation\Object\Gravatar());
        $this->assertEquals(false, $col->hasDataPopulation());

        $col->setDataPopulation($object);

        $this->assertEquals(true, $col->hasDataPopulation());
        $this->assertInstanceOf(DataObject::class, $col->getDataPopulation());
    }

    public function testException()
    {
        $col = new Column\ExternalData('myData');

        $object = new DataPopulation\DataObject();

        $this->expectException(Exception::class);
        $col->setDataPopulation($object);
    }
}
