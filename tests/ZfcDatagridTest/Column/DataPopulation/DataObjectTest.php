<?php
namespace ZfcDatagridTest\Column\DataPopulation;

use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Column\DataPopulation\DataObject;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\DataPopulation\DataObject
 */
class DataObjectTest extends TestCase
{
    public function testObject()
    {
        $mock = $this->getMockBuilder(\ZfcDatagrid\Column\DataPopulation\Object\Gravatar::class)->getMock();
        $mock->expects(self::any())
            ->method('toString')
            ->will($this->returnValue('myReturn'));

        $object = new DataObject();

        $object->setObject($mock);
        $this->assertSame($mock, $object->getObject());

        $this->assertEquals('myReturn', $object->toString());
    }

    public function testParameters()
    {
        $column = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $mock   = $this->getMockBuilder(\ZfcDatagrid\Column\DataPopulation\Object\Gravatar::class)->getMock();
        $mock->expects(self::any())
        ->method('toString')
        ->will($this->returnValue('myReturn'));

        $object = new DataObject();
        $object->setObject($mock);

        $this->assertCount(0, $object->getObjectParametersColumn());

        $object->addObjectParameterColumn('idPara', $column);

        $parameters = $object->getObjectParametersColumn();

        $this->assertCount(1, $parameters);
        $this->assertEquals([
            'objectParameterName' => 'idPara',
            'column'              => $column,
        ], $parameters[0]);

        $object->setObjectParameter('otherPara', '123');
    }
}
