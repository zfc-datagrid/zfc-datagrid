<?php
namespace ZfcDatagridTest\Service;

use PHPUnit\Framework\TestCase;
use Zend\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceManager;
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

    public function setUp()
    {
        $mvcEventMock = $this->getMockBuilder(\Zend\Mvc\MvcEvent::class)
            ->getMock();

        $this->applicationMock = $this->getMockBuilder(\Zend\Mvc\Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->applicationMock->expects($this->any())
            ->method('getMvcEvent')
            ->will($this->returnValue($mvcEventMock));

        $this->rendererServiceMock = $this->getMockBuilder(\ZfcDatagrid\Renderer\BootstrapTable\Renderer::class)
            ->getMock();

        $this->router = $this->getMockBuilder(RouteStackInterface::class)
            ->getMock();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Config key "ZfcDatagrid" is missing
     */
    public function testCreateServiceException()
    {
        $sm = new ServiceManager();
        $sm->setService('config', []);

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

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
    }

    public function testCanCreateServiceWithTranslator()
    {
        $translatorMock = $this->getMockBuilder(\Zend\I18n\Translator\Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('translator', $translatorMock);
        $sm->setService('Router', $this->router);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
        $this->assertEquals($translatorMock, $grid->getTranslator());
    }

    public function testCanCreateServiceWithMvcTranslator()
    {
        $mvcTranslatorMock = $this->getMockBuilder(\Zend\I18n\Translator\Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('translator', $mvcTranslatorMock);
        $sm->setService('Router', $this->router);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
        $this->assertEquals($mvcTranslatorMock, $grid->getTranslator());
    }
}
