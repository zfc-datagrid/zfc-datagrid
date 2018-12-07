<?php
namespace ZfcDatagridTest;

use Exception;
use InvalidArgumentException;
use Throwable;
use Zend\Http\PhpEnvironment\Request;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\MvcEvent;
use Zend\Paginator\Paginator;
use Zend\Router\Http\HttpRouterFactory;
use Zend\Router\Http\Segment;
use Zend\Router\RoutePluginManagerFactory;
use Zend\Session\Container;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZfcDatagrid\Action\Mass;
use ZfcDatagrid\Column;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\DataSource\PhpArray;
use ZfcDatagrid\Renderer\AbstractRenderer;
use ZfcDatagridTest\Util\ServiceManagerFactory;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group Datagrid
 * @covers \ZfcDatagrid\Datagrid
 */
class DatagridTest extends TestBase
{
    /** @var string */
    protected $className = Datagrid::class;

    /** @var Datagrid */
    private $grid;

    /** @var array */
    private $config;

    public function setUp()
    {
        $config = include './config/module.config.php';
        $config = $config['ZfcDatagrid'];

        $cacheOptions                          = new \Zend\Cache\Storage\Adapter\MemoryOptions();
        $config['cache']['adapter']['name']    = 'Memory';
        $config['cache']['adapter']['options'] = $cacheOptions->toArray();

        $this->config = $config;

        $this->grid = new Datagrid();
        $this->grid->setOptions($this->config);
        $this->grid->setRequest($this->getMockBuilder(Request::class)->getMock());
    }

    public function testInit()
    {
        $this->assertFalse($this->grid->isInit());

        $this->grid->init();

        $this->assertTrue($this->grid->isInit());
    }

    public function testId()
    {
        $grid = new Datagrid();

        $this->assertEquals('defaultGrid', $this->grid->getId());

        $grid->setId('myCustomId');
        $this->assertEquals('myCustomId', $grid->getId());
    }

    public function testSession()
    {
        $this->assertInstanceOf(\Zend\Session\Container::class, $this->grid->getSession());
        $this->assertEquals('defaultGrid', $this->grid->getSession()
            ->getName());

        $session = new Container('myName');

        $this->grid->setSession($session);
        $this->assertInstanceOf(\Zend\Session\Container::class, $this->grid->getSession());
        $this->assertSame($session, $this->grid->getSession());
        $this->assertEquals('myName', $this->grid->getSession()
            ->getName());
    }

    public function testCacheId()
    {
        $grid      = new Datagrid();
        $sessionId = $grid->getSession()
            ->getManager()
            ->getId();

        $this->assertEquals(md5($sessionId . '_defaultGrid'), $this->grid->getCacheId());

        $this->grid->setCacheId('myCacheId');
        $this->assertEquals('myCacheId', $this->grid->getCacheId());
    }

    public function testRequest()
    {
        $this->assertInstanceOf(Request::class, $this->grid->getRequest());
    }

    public function testTranslator()
    {
        $this->assertFalse($this->grid->hasTranslator());

        $this->grid->setTranslator($this->getMockBuilder(Translator::class)->getMock());

        $this->assertTrue($this->grid->hasTranslator());
        $this->assertInstanceOf(Translator::class, $this->grid->getTranslator());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDataSourceArray()
    {
        $grid = new Datagrid();
        $this->assertFalse($grid->hasDataSource());

        $grid->setDataSource([]);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(PhpArray::class, $grid->getDataSource());

        $source = $this->getMockBuilder(PhpArray::class)
            ->setMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $grid->setDataSource($source);
        $this->assertTrue($grid->hasDataSource());

        $grid->setDataSource(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage For "Zend\Db\Sql\Select" also a "Zend\Db\Adapter\Sql" or "Zend\Db\Sql\Sql" is needed.
     */
    public function testDataSourceZendSelect()
    {
        $grid = new Datagrid();

        $this->assertFalse($grid->hasDataSource());

        $select = $this->getMockBuilder(\Zend\Db\Sql\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $platform = $this->getMockBuilder(\Zend\Db\Adapter\Platform\Sqlite::class)
            ->getMock();
        $platform->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('myPlatform'));

        $adapter = $this->getMockBuilder(\Zend\Db\Adapter\Adapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapter->expects($this->any())
            ->method('getPlatform')
            ->will($this->returnValue($platform));

        $grid->setDataSource($select, $adapter);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(\ZfcDatagrid\Datasource\ZendSelect::class, $grid->getDataSource());
        $grid->setDataSource($select);
    }

    public function testDataSourceDoctrine()
    {
        $grid = new Datagrid();

        $this->assertFalse($grid->hasDataSource());

        $qb = $this->getMockBuilder(\Doctrine\ORM\QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grid->setDataSource($qb);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(\ZfcDatagrid\DataSource\Doctrine2::class, $grid->getDataSource());
    }

    public function testDataSourceDoctrineCollection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'If providing a Collection, also the Doctrine\ORM\EntityManager is needed as a second parameter'
        );
        $grid = new Datagrid();

        $this->assertFalse($grid->hasDataSource());

        $coll = $this->getMockBuilder(\Doctrine\Common\Collections\ArrayCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $em   = $this->getMockBuilder(\Doctrine\ORM\EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grid->setDataSource($coll, $em);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(\ZfcDatagrid\DataSource\Doctrine2Collection::class, $grid->getDataSource());

        $grid->setDataSource($coll);
    }

    public function testDefaultItemsPerRow()
    {
        $this->assertEquals(25, $this->grid->getDefaultItemsPerPage());

        $this->grid->setDefaultItemsPerPage(- 1);
        $this->assertEquals(- 1, $this->grid->getDefaultItemsPerPage());
    }

    public function testTitle()
    {
        $this->assertEquals('', $this->grid->getTitle());

        $this->grid->setTitle('My title');
        $this->assertEquals('My title', $this->grid->getTitle());
    }

    public function testParameters()
    {
        $this->assertFalse($this->grid->hasParameters());
        $this->assertEquals([], $this->grid->getParameters());

        $this->grid->addParameter('myPara', 'test');

        $this->assertEquals([
            'myPara' => 'test',
        ], $this->grid->getParameters());

        $this->grid->setParameters([
            'other' => 'blubb',
        ]);
        $this->assertEquals([
            'other' => 'blubb',
        ], $this->grid->getParameters());
        $this->assertTrue($this->grid->hasParameters());
    }

    public function testUrl()
    {
        $this->assertSame('', $this->grid->getUrl());

        $this->grid->setUrl('/module/controller/action');
        $this->assertEquals('/module/controller/action', $this->grid->getUrl());
    }

    public function testExportRenderers()
    {
        /*
         * NEVER define default export renderer -> because the user cant remove them after!
         */
        $this->assertEquals([], $this->grid->getExportRenderers());

        $this->grid->setExportRenderers([
            'tcpdf' => 'PDF',
        ]);

        $this->assertEquals([
            'tcpdf' => 'PDF',
        ], $this->grid->getExportRenderers());
    }

    public function testAddColumn()
    {
        $this->assertEquals([], $this->grid->getColumns());

        $col = $this->getMockBuilder(\ZfcDatagrid\Column\AbstractColumn::class)
            ->setMethods(['getUniqueId'])
            ->getMock();

        $col->expects($this->any())
            ->method('getUniqueId')
            ->will($this->returnValue('myUniqueId'));

        $this->grid->addColumn($col);

        $this->assertCount(1, $this->grid->getColumns());

        $this->assertEquals(null, $this->grid->getColumnByUniqueId('notAvailable'));
    }

    /**
     * @requires PHP 7.0
     */
    public function testAddColumnInvalidArgumentException()
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage(
            'Argument 1 passed to ZfcDatagrid\Datagrid::createColumn() must be of the type array'
        );
        $grid = new Datagrid();

        $grid->addColumn(null);
    }

    public function testAddColumnArrayFQN()
    {
        $grid = new Datagrid();
        $this->assertEquals([], $grid->getColumns());

        $column = [
            'colType' => \ZfcDatagrid\Column\Select::class,
            'label'   => 'My label',
            'select'  => [
                'column' => 'myCol',
                'table'  => 'myTable',
            ],
        ];
        $grid->addColumn($column);

        $this->assertCount(1, $grid->getColumns());

        $col = $grid->getColumnByUniqueId('myTable_myCol');
        $this->assertInstanceOf(\ZfcDatagrid\Column\Select::class, $col);
        $this->assertEquals('My label', $col->getLabel());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Column type: "ZfcDatagrid\Column\Unknown" not found!
     */
    public function testAddColumnArrayInvalidColType()
    {
        $grid = new Datagrid();
        $this->assertEquals([], $grid->getColumns());

        $column = [
            'colType' => 'ZfcDatagrid\Column\Unknown',
            'label'   => 'My label',
        ];

        $grid->addColumn($column);
    }

    public function testAddColumnArraySelect()
    {
        $grid = new Datagrid();
        $this->assertEquals([], $grid->getColumns());

        $column = [
            'label'  => 'My label',
            'select' => [
                'column' => 'myCol',
                'table'  => 'myTable',
            ],
        ];
        $grid->addColumn($column);

        $this->assertCount(1, $grid->getColumns());

        $col = $grid->getColumnByUniqueId('myTable_myCol');
        $this->assertInstanceOf(\ZfcDatagrid\Column\Select::class, $col);
        $this->assertEquals('My label', $col->getLabel());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage For "ZfcDatagrid\Column\Select" the option select[column] must be defined!
     */
    public function testAddColumnArraySelectInvalidArgumentException()
    {
        $grid = new Datagrid();
        $this->assertEquals([], $grid->getColumns());

        $column = [
            'label' => 'My label',
        ];

        $grid->addColumn($column);
    }

    public function testAddColumnArrayTypeAction()
    {
        $grid = new Datagrid();

        $column = [
            'colType' => 'action',
            'label'   => 'My action',
        ];
        $grid->addColumn($column);

        $this->assertCount(1, $grid->getColumns());

        $col = $grid->getColumnByUniqueId('action');
        $this->assertInstanceOf(\ZfcDatagrid\Column\Action::class, $col);
        $this->assertEquals('My action', $col->getLabel());
    }

    public function testAddColumnArrayStyle()
    {
        $grid = new Datagrid();

        $bold = new Column\Style\Bold();

        $column = [
            'select' => [
                'column' => 'myCol',
                'table'  => 'myTable',
            ],
            'styles' => [
                $bold,
            ],
        ];
        $grid->addColumn($column);

        $this->assertCount(1, $grid->getColumns());

        $col = $grid->getColumnByUniqueId('myTable_myCol');
        $this->assertInstanceOf(\ZfcDatagrid\Column\Select::class, $col);

        $this->assertEquals([
            $bold,
        ], $col->getStyles());
    }

    public function testAddColumnArraySortDefaultMinimal()
    {
        $grid = new Datagrid();

        $column = [
            'select' => [
                'column' => 'myCol',
                'table'  => 'myTable',
            ],
            'sortDefault' => 1,
        ];
        $grid->addColumn($column);

        $this->assertCount(1, $grid->getColumns());

        $col = $grid->getColumnByUniqueId('myTable_myCol');
        $this->assertInstanceOf(\ZfcDatagrid\Column\Select::class, $col);

        $this->assertEquals([
            'priority'      => 1,
            'sortDirection' => 'ASC',
        ], $col->getSortDefault());
    }

    public function testAddColumnArraySortDefault()
    {
        $grid = new Datagrid();

        $column = [
            'select' => [
                'column' => 'myCol',
                'table'  => 'myTable',
            ],
            'sortDefault' => [
                1,
                'ASC',
            ],
        ];
        $grid->addColumn($column);

        $this->assertCount(1, $grid->getColumns());

        $col = $grid->getColumnByUniqueId('myTable_myCol');
        $this->assertInstanceOf(\ZfcDatagrid\Column\Select::class, $col);

        $this->assertEquals([
            'priority'      => 1,
            'sortDirection' => 'ASC',
        ], $col->getSortDefault());
    }

    public function testSetColumn()
    {
        $grid = new Datagrid();

        $this->assertEquals([], $grid->getColumns());

        $col = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $col->setUniqueId('myUniqueId');

        $col2 = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $col2->setUniqueId('myUniqueId2');

        $grid->setColumns([
            $col,
            $col2,
        ]);

        $this->assertCount(2, $grid->getColumns());
        $this->assertEquals($col, $grid->getColumnByUniqueId('myUniqueId'));
        $this->assertEquals($col2, $grid->getColumnByUniqueId('myUniqueId2'));
    }

    public function testRowStyle()
    {
        $grid = new Datagrid();
        $this->assertFalse($grid->hasRowStyles());

        $grid->addRowStyle($this->getMockBuilder(\ZfcDatagrid\Column\Style\Bold::class)->getMock());
        $this->assertCount(1, $grid->getRowStyles());
        $this->assertTrue($grid->hasRowStyles());

        $grid->addRowStyle($this->getMockBuilder(\ZfcDatagrid\Column\Style\Italic::class)->getMock());
        $this->assertCount(2, $grid->getRowStyles());
        $this->assertTrue($grid->hasRowStyles());
    }

    public function testUserFilter()
    {
        $this->assertTrue($this->grid->isUserFilterEnabled());

        $this->grid->setUserFilterDisabled(true);
        $this->assertFalse($this->grid->isUserFilterEnabled());
    }

    public function testRowClickAction()
    {
        $this->assertFalse($this->grid->hasRowClickAction());

        $action = $this->getMockForAbstractClass(\ZfcDatagrid\Column\Action\AbstractAction::class);
        $this->grid->setRowClickAction($action);
        $this->assertEquals($action, $this->grid->getRowClickAction());
        $this->assertTrue($this->grid->hasRowClickAction());
    }

    public function testRendererName()
    {
        // Default on HTTP
        $this->assertEquals('bootstrapTable', $this->grid->getRendererName());

        // Default on CLI
        $_SERVER['argv'] = [
            'foo.php',
            'foo' => 'baz',
            'bar',
        ];
        $_ENV["FOO_VAR"] = "bar";

        $request  = new \Zend\Console\Request();
        $this->grid->setRequest($request);
        $this->assertEquals('zendTable', $this->grid->getRendererName());

        // change default
        $this->grid->setRendererName('myRenderer');
        $this->assertEquals('myRenderer', $this->grid->getRendererName());

        // by HTTP request
        $_GET['rendererType'] = 'jqGrid';
        $request              = new \Zend\Http\PhpEnvironment\Request();
        $this->grid->setRequest($request);
        $this->assertEquals('jqGrid', $this->grid->getRendererName());
    }

    public function testToolbarTemplate()
    {
        $grid = new Datagrid();

        $this->assertNull($grid->getToolbarTemplate());

        $grid->setToolbarTemplate('my-module/my-controller/grid-toolbar');
        $this->assertEquals('my-module/my-controller/grid-toolbar', $grid->getToolbarTemplate());
    }

    public function testViewModelDefault()
    {
        $grid = new Datagrid();

        $defaultView = $grid->getViewModel();
        $this->assertInstanceOf(\Zend\View\Model\ViewModel::class, $defaultView);
        $this->assertSame($defaultView, $grid->getViewModel());
    }

    public function testSetViewModel()
    {
        $grid = new Datagrid();

        $customView = $this->getMockBuilder(\Zend\View\Model\ViewModel::class)->getMock();
        $grid->setViewModel($customView);
        $this->assertSame($customView, $grid->getViewModel());
    }

    public function testSetViewModelException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'A viewModel is already set. Did you already called $grid->render() or $grid->getViewModel() before?'
        );
        $grid = new Datagrid();
        $grid->getViewModel();

        $customView = $this->getMockBuilder(\Zend\View\Model\ViewModel::class)->getMock();

        $grid->setViewModel($customView);
    }

    public function getRouter()
    {
        $config = [
            'router' => [
                'routes' => [
                    'myTestRoute' => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'    => '/foo[/:bar]',
                            'defaults' => [
                                'controller' => 'MyController',
                                'action'     => 'index',
                                'bar'        => 'baz',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // Setup service manager, we need that for the route
        ServiceManagerFactory::setConfig($config);
        $serviceLocator = ServiceManagerFactory::getServiceManager();

        $routePluginManager = new RoutePluginManagerFactory();
        $serviceLocator->setService('RoutePluginManager', $routePluginManager->createService($serviceLocator));
        $routerFactory = new HttpRouterFactory();

        return $routerFactory->createService($serviceLocator);
    }

    public function testGetAndSetRouter()
    {
        $router = $this->getRouter();
        $grid   = new Datagrid();
        $grid->setRouter($router);
        $this->assertSame($router, $grid->getRouter());
    }

    public function testColumnsPositions()
    {
        $grid = new Datagrid();

        $grid->addColumn([
            'select' => [
                'column' => 'myCol1',
                'table'  => 'myTable',
            ],
        ]);
        $grid->addColumn([
            'select' => [
                'column' => 'myCol2',
                'table'  => 'myTable',
            ],
            'position' => 5,
        ]);
        $grid->addColumn([
            'select' => [
                'column' => 'myCol3',
                'table'  => 'myTable',
            ],
        ]);
        $grid->addColumn([
            'select' => [
                'column' => 'myCol4',
                'table'  => 'myTable',
            ],
            'position' => 2,
        ]);
        $grid->addColumn([
            'select' => [
                'column' => 'myCol5',
                'table'  => 'myTable',
            ],
            'position' => 10,
        ]);

        $gridColumns = $grid->sortColumns();

        $this->assertEquals(array_search('myTable_myCol1', array_keys($gridColumns)), 0);
        $this->assertEquals(array_search('myTable_myCol3', array_keys($gridColumns)), 1);
        $this->assertEquals(array_search('myTable_myCol4', array_keys($gridColumns)), 2);
        $this->assertEquals(array_search('myTable_myCol2', array_keys($gridColumns)), 3);
        $this->assertEquals(array_search('myTable_myCol5', array_keys($gridColumns)), 4);
    }

    public function testMassAction(): void
    {
        $dg = new Datagrid();
        $this->assertFalse($dg->hasMassAction());
        $this->assertSame([], $dg->getMassActions());

        $mass = new Mass();
        $dg->addMassAction($mass);

        $this->assertTrue($dg->hasMassAction());
        $this->assertSame([$mass], $dg->getMassActions());
    }

    public function testIsDataLoaded(): void
    {
        $this->assertFalse($this->getMethod('isDataLoaded')->invoke($this->getClass()));
        $this->setProperty('isDataLoaded', true);
        $this->assertTrue($this->getMethod('isDataLoaded')->invoke($this->getClass()));
    }

    public function testSetRendererService(): void
    {
        $this->assertNull($this->getProperty('rendererService'));

        $rendererService = $this->getMockForAbstractClass(AbstractRenderer::class);
        $this->getMethod('setRendererService')->invokeArgs($this->getClass(), [$rendererService]);

        $this->assertSame($rendererService, $this->getProperty('rendererService'));
    }

    public function testToolbarTemplateVariables(): void
    {
        $this->assertSame([], $this->getProperty('toolbarTemplateVariables'));

        $params = [
            'variables',
            'foobar',
        ];
        $this->getMethod('setToolbarTemplateVariables')->invokeArgs($this->getClass(), [$params]);

        $this->assertSame($params, $this->getMethod('getToolbarTemplateVariables')->invoke($this->getClass()));
        $this->assertSame($params, $this->getProperty('toolbarTemplateVariables'));
    }

    public function testIsRendered()
    {
        $this->assertFalse($this->getMethod('isRendered')->invoke($this->getClass()));
        $this->setProperty('isRendered', true);
        $this->assertTrue($this->getMethod('isRendered')->invoke($this->getClass()));
    }

    public function testGetPaginatorException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Paginator is only available after calling "loadData()"');
        (new Datagrid())->getPaginator();
    }

    public function testGetPaginator(): void
    {
        $paginator = $this->getMockBuilder(Paginator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setProperty('paginator', $paginator);
        $this->assertSame($paginator, $this->getMethod('getPaginator')->invoke($this->getClass()));
    }

    public function testGetPreparedData(): void
    {
        $this->assertSame([], $this->getMethod('getPreparedData')->invoke($this->getClass()));

        $params = [
            'variables',
            'foobar',
        ];
        $this->setProperty('preparedData', $params);
        $this->assertSame($params, $this->getMethod('getPreparedData')->invoke($this->getClass()));
    }

    public function testGetResponse(): void
    {
        $this->mockedMethodList = [
            'isRendered',
            'render',
        ];

        $class = $this->getClass();
        $class->expects($this->once())
            ->method('isRendered')
            ->willReturn(false);
        $class->expects($this->once())
            ->method('render')
            ->willReturn(false);

        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->setProperty('response', $response);

        $this->assertSame($response, $this->getMethod('getResponse')->invoke($this->getClass()));
    }

    public function testGetResponseWithNoRenderer(): void
    {
        $this->mockedMethodList = [
            'isRendered',
            'render',
        ];

        $class = $this->getClass();
        $class->expects($this->once())
            ->method('isRendered')
            ->willReturn(true);
        $class->expects($this->never())
            ->method('render')
            ->willReturn(false);

        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->setProperty('response', $response);

        $this->assertSame($response, $this->getMethod('getResponse')->invoke($this->getClass()));
    }

    public function testIsHtmlInitResponseFalse(): void
    {
        $this->mockedMethodList = [
            'getResponse',
        ];
        $this->assertTrue($this->getMethod('isHtmlInitReponse')->invoke($this->getClass()));
    }

    public function testIsHtmlInitResponseJsonModel(): void
    {
        $this->mockedMethodList = [
            'getResponse',
        ];
        $class = $this->getClass();
        $class->expects($this->once())
            ->method('getResponse')
            ->willReturn(new JsonModel());

        $this->assertFalse($this->getMethod('isHtmlInitReponse')->invoke($this->getClass()));
    }

    public function testIsHtmlInitResponseResponseInterface(): void
    {
        $this->mockedMethodList = [
            'getResponse',
        ];
        $class = $this->getClass();
        $class->expects($this->exactly(2))
            ->method('getResponse')
            ->willReturn(new ViewModel(), $this->getMockForAbstractClass(ResponseInterface::class));

        $this->assertFalse($this->getMethod('isHtmlInitReponse')->invoke($this->getClass()));
    }

    public function testLoadDataWithProperty(): void
    {
        $this->setProperty('isDataLoaded', true);
        $this->assertTrue($this->getMethod('loadData')->invoke($this->getClass()));
    }

    public function testLoadDataInitException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The init() method has to be called, before you can call loadData()!');
        $this->getMethod('loadData')->invoke($this->getClass());
    }

    public function testLoadDataDataSourceException(): void
    {
        $this->mockedMethodList = [
            'isInit',
        ];

        $class = $this->getClass();
        $class->expects($this->once())
            ->method('isInit')
            ->willReturn(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No datasource defined! Please call "setDataSource()" first"');
        $this->getMethod('loadData')->invoke($this->getClass());
    }
}
