<?php
namespace ZfcDatagridTest\Service;

use PHPUnit\Framework\TestCase;
use Zend\Mvc\Application;
use Zend\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcDatagrid\Middleware\RequestHelper;
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

    /** @var Application */
    private $applicationMock;

    /** @var Renderer */
    private $rendererServiceMock;

    /** @var RouteStackInterface */
    private $router;

    /** @var RequestHelper */
    private $requestHelper;

    public function setUp()
    {
        $this->applicationMock = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rendererServiceMock = $this->getMockBuilder(Renderer::class)
            ->getMock();

        $this->router = $this->getMockBuilder(RouteStackInterface::class)
            ->getMock();

        $this->requestHelper = $this->getMockBuilder(RequestHelper::class)
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
        $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);
    }

    public function testCanCreateService()
    {
        $sm = new ServiceManager();
        $sm->setService('config', $this->config);
        $sm->setService('application', $this->applicationMock);
        $sm->setService('zfcDatagrid.renderer.bootstrapTable', $this->rendererServiceMock);
        $sm->setService('Router', $this->router);
        $sm->setService(RequestHelper::class, $this->requestHelper);

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
        $sm->setService(RequestHelper::class, $this->requestHelper);

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
        $sm->setService(RequestHelper::class, $this->requestHelper);

        $factory = new DatagridFactory();
        $grid    = $factory->__invoke($sm, \ZfcDatagrid\Datagrid::class);

        $this->assertInstanceOf(\ZfcDatagrid\Datagrid::class, $grid);
        $this->assertEquals($mvcTranslatorMock, $grid->getTranslator());
    }
}
