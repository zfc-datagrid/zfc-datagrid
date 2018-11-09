<?php
namespace ZfcDatagridTest\DataSource;

use Doctrine\Common\Collections\ArrayCollection;
use ZfcDatagrid\Column;
use ZfcDatagrid\DataSource\Doctrine2Collection;
use ZfcDatagridTest\DataSource\Doctrine2\Assets\Entity\Category;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group DataSource
 *
 * @covers \ZfcDatagrid\DataSource\Doctrine2Collection
 */
class Doctrine2CollectionTest extends TestBase
{
    /** @var Doctrine2Collection */
    private $source;

    private $collection;

    public function setUp()
    {
        parent::setUp();

        $col1 = new Column\Select('volume');
        $this->colVolumne = $col1;

        $col1 = new Column\Select('edition');
        $this->colEdition = $col1;

        $collection = new ArrayCollection();
        foreach ([1, 1, 1] as $row) {
            $collection->add(new Category());
        }
        $this->collection = $collection;

        $source = new Doctrine2Collection($this->collection);
        $source->setColumns([
            $this->colVolumne,
            $this->colEdition,
        ]);

        $this->source = $source;
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Argument 1 passed to ZfcDatagrid\DataSource\Doctrine2Collection::__construct() must implement interface Doctrine\Common\Collections\Collection, null given,
     */
    public function testConstructExceptionClass()
    {
        new Doctrine2Collection(null);
    }

    public function testGetData()
    {
        $source = new Doctrine2Collection($this->collection);

        $this->assertEquals($this->collection, $source->getData());
    }

    public function testEntityManager()
    {
        $em = $this->getMockBuilder(\Doctrine\ORM\EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $source = clone $this->source;
        $this->assertNull($source->getEntityManager());

        $source->setEntityManager($em);
        $this->assertSame($em, $source->getEntityManager());
    }

}
