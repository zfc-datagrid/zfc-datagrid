<?php
namespace ZfcDatagridTest\DataSource;

/**
 * All copyright here goes to Doctrine2!
 *
 * Copied from: https://github.com/doctrine/doctrine2/blob/master/tests/Doctrine/Tests/OrmTestCase.php
 */

use Exception;
use InvalidArgumentException;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use TypeError;
use ZfcDatagrid\Column;
use ZfcDatagrid\DataSource\LaminasSelect;
use ZfcDatagrid\Filter;

/**
 * @group DataSource
 * @covers \ZfcDatagrid\DataSource\LaminasSelect
 */
class LaminasSelectTest extends DataSourceTestCase
{
    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Sql object
     *
     * @var Sql
     */
    protected $sql = null;

    /**
     *
     * @var LaminasSelect
     */
    protected $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->mockDriver     = $this->getMockBuilder(\Laminas\Db\Adapter\Driver\DriverInterface::class)->getMock();
        $this->mockConnection = $this->getMockBuilder(\Laminas\Db\Adapter\Driver\ConnectionInterface::class)->getMock();
        $this->mockDriver->expects(self::any())
            ->method('checkEnvironment')
            ->will($this->returnValue(true));
        $this->mockDriver->expects(self::any())
            ->method('getConnection')
            ->will($this->returnValue($this->mockConnection));
        $this->mockPlatform = $this->getMockBuilder(\Laminas\Db\Adapter\Platform\PlatformInterface::class)->getMock();
        $this->mockPlatform->expects(self::any())
            ->method('getIdentifierSeparator')
            ->will($this->returnValue('.'));

        $this->mockStatement = $this->getMockBuilder(\Laminas\Db\Adapter\Driver\StatementInterface::class)->getMock();
        $this->mockDriver->expects(self::any())
            ->method('createStatement')
            ->will($this->returnValue($this->mockStatement));

        $this->adapter = new Adapter($this->mockDriver, $this->mockPlatform);

        $this->sql = new Sql($this->adapter, 'foo');

        $select = new Select();

        $this->source = new LaminasSelect($select);
        $this->source->setAdapter($this->sql);
        $this->source->setColumns([
            $this->colVolumne,
            $this->colEdition,
        ]);
    }

    public function testConstruct()
    {
        $select = $this->getMockBuilder(\Laminas\Db\Sql\Select::class)->getMock();

        $source = new LaminasSelect($select);

        $this->assertInstanceOf(\Laminas\Db\Sql\Select::class, $source->getData());
        $this->assertEquals($select, $source->getData());

        $this->expectException(TypeError::class);
        $source = new LaminasSelect([]);
    }

    public function testExecuteException()
    {
        $select = $this->getMockBuilder(\Laminas\Db\Sql\Select::class)->getMock();

        $source = new LaminasSelect($select);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Object "Laminas\Db\Sql\Sql" is missing, please call setAdapter() first!');
        $source->execute();
    }

    public function testAdapter()
    {
        $source = clone $this->source;

        $this->assertInstanceOf(\Laminas\Db\Sql\Sql::class, $source->getAdapter());
        $this->assertEquals($this->sql, $source->getAdapter());

        $source->setAdapter($this->adapter);
        $this->assertInstanceOf(\Laminas\Db\Sql\Sql::class, $source->getAdapter());

        $this->expectException(InvalidArgumentException::class);
        $source->setAdapter('something');
    }

    public function testExecute()
    {
        $source = clone $this->source;

        $source->addSortCondition($this->colVolumne);
        $source->addSortCondition($this->colEdition, 'DESC');
        $source->execute();

        $this->assertInstanceOf(\Laminas\Paginator\Adapter\DbSelect::class, $source->getPaginatorAdapter());
    }

    public function testJoinTable()
    {
        $this->markTestIncomplete('LaminasSelect join table test');

        $col1 = new Column\Select('id', 'o');
        $col2 = new Column\Select('name', 'u');

        $select = new Select();
        $select->from([
            'o' => 'orders',
        ]);
        $select->join([
            'u' => 'user',
        ], 'u.order = o.id');

        $source = new LaminasSelect($select);
        $source->setAdapter($this->sql);
        $source->setColumns([
            $col1,
            $col2,
        ]);
        $source->execute();
    }

    public function testFilter()
    {
        $this->markTestSkipped();
        $source = clone $this->source;

        /*
         * LIKE
         */
        $filter = new Filter();
        $filter->setFromColumn($this->colVolumne, '~7');

        $source->addFilter($filter);
        $source->execute();

        // $this->assertEquals(2, $source->getPaginatorAdapter()
        // ->count());
    }
}
