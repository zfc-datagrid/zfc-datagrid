<?php
namespace ZfcDatagridTest\Service;

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use ZfcDatagrid\Service\LaminasDbAdapterFactory;

/**
 * @covers \ZfcDatagrid\Service\LaminasDbAdapterFactory
 */
class LaminasDbAdapterFactoryTest extends TestCase
{
    private $config = [
        'zfcDatagrid_dbAdapter' => [
            'driver'   => 'Pdo_Sqlite',
            'database' => 'somewhere/testDb.sqlite',
        ],
    ];

    public function testCanCreateService()
    {
        $sm = new ServiceManager();
        $sm->setService('config', $this->config);

        $factory = new LaminasDbAdapterFactory();
        $grid    = $factory->__invoke($sm, 'zfcDatagrid_dbAdapter');

        $this->assertInstanceOf(\Laminas\Db\Adapter\Adapter::class, $grid);
    }
}
