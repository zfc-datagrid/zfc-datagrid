<?php
namespace ZfcDatagridTest\Service;

use InvalidArgumentException;
use Laminas\Cache\Service\StorageAdapterFactory;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use PHPUnit\Framework\TestCase;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\ServiceManager;
use ZfcDatagrid\Service\DatagridFactory;

/**
 * @covers \ZfcDatagrid\Service\DatagridFactory
 */
class DatagridFactoryTest extends TestCase
{
    /** @var array */
    private $config = [
        'ZfcDatagrid' => [
            'cache' => [
                'adapter' => [
                    'name' => 'Filesystem',
                ],
            ],
            'generalParameterNames' => [
                'rendererType' => null,
            ],
            'settings' => [
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
        $mvcEventMock = $this->getMockBuilder(\Laminas\Mvc\MvcEvent::class)
            ->getMock();

        $this->applicationMock = $this->getMockBuilder(\Laminas\Mvc\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->applicationMock->expects(self::any())
            ->method('getMvcEvent')
            ->will($this->returnValue($mvcEventMock));

        $this->rendererServiceMock = $this->getMockBuilder(\ZfcDatagrid\Renderer\BootstrapTable\Renderer::class)
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
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);
    }

    public function testCanCreateService()
    {
        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('Router', $this->router);
        $sm->setService('Laminas\Cache\Service\StorageAdapterFactoryInterface', $this->storageAdapterFactory);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
    }

    public function testCanCreateServiceWithTranslator()
    {
        $translatorMock = $this->getMockBuilder(\Laminas\I18n\Translator\Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('translator', $translatorMock);
        $sm->setService('Router', $this->router);
        $sm->setService('Laminas\Cache\Service\StorageAdapterFactoryInterface', $this->storageAdapterFactory);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
        $this->assertEquals($translatorMock, $grid->getTranslator());
    }

    public function testCanCreateServiceWithMvcTranslator()
    {
        $mvcTranslatorMock = $this->getMockBuilder(\Laminas\I18n\Translator\Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('translator', $mvcTranslatorMock);
        $sm->setService('Router', $this->router);
        $sm->setService('Laminas\Cache\Service\StorageAdapterFactoryInterface', $this->storageAdapterFactory);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
        $this->assertEquals($mvcTranslatorMock, $grid->getTranslator());
    }
}
