<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Service;

use InvalidArgumentException;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Renderer\BootstrapTable\Renderer;
use ZfcDatagrid\Service\DatagridFactory;

/**
 * @covers \ZfcDatagrid\Service\DatagridFactory
 */
class DatagridFactoryTest extends TestCase
{
    /** @var array */
    private $config = [
        'ZfcDatagrid' => [
            'cache'                 => [
                'adapter' => [
                    'name' => 'Filesystem',
                ],
            ],
            'generalParameterNames' => [
                'rendererType' => null,
            ],
            'settings'              => [
                'default' => [
                    'renderer' => [
                        'http' => 'bootstrapTable',
                    ],
                ],
            ],
        ],
    ];

    private $applicationMock;

    private $rendererServiceMock;

    private $router;

    private $storageAdapterFactory;

    public function setUp(): void
    {
        $mvcEventMock = $this->getMockBuilder(MvcEvent::class)
            ->getMock();

        $this->applicationMock = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->applicationMock->expects(self::any())
            ->method('getMvcEvent')
            ->will($this->returnValue($mvcEventMock));

        $this->rendererServiceMock = $this->getMockBuilder(Renderer::class)
            ->getMock();

        $this->router = $this->getMockBuilder(RouteStackInterface::class)
            ->getMock();

        $this->storageAdapterFactory = $this->getMockBuilder(StorageAdapterFactoryInterface::class)
            ->getMock();
    }

    public function testCreateServiceException()
    {
        $sm = new ServiceManager();
        $sm->setService('config', []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Config key "ZfcDatagrid" is missing');

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, Datagrid::class);
    }

    public function testCanCreateService()
    {
        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('Router', $this->router);
        $sm->setService(StorageAdapterFactoryInterface::class, $this->storageAdapterFactory);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, Datagrid::class);

        $this->assertInstanceOf(Datagrid::class, $grid);
    }

    public function testCanCreateServiceWithTranslator()
    {
        $translatorMock = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('translator', $translatorMock);
        $sm->setService('Router', $this->router);
        $sm->setService(StorageAdapterFactoryInterface::class, $this->storageAdapterFactory);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, Datagrid::class);

        $this->assertInstanceOf(Datagrid::class, $grid);
        $this->assertEquals($translatorMock, $grid->getTranslator());
    }

    public function testCanCreateServiceWithMvcTranslator()
    {
        $mvcTranslatorMock = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('translator', $mvcTranslatorMock);
        $sm->setService('Router', $this->router);
        $sm->setService(StorageAdapterFactoryInterface::class, $this->storageAdapterFactory);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, Datagrid::class);

        $this->assertInstanceOf(Datagrid::class, $grid);
        $this->assertEquals($mvcTranslatorMock, $grid->getTranslator());
    }
}
