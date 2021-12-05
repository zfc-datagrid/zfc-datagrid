<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Service;

use Laminas\Db\Adapter\Adapter;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
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

        $this->assertInstanceOf(Adapter::class, $grid);
    }
}
