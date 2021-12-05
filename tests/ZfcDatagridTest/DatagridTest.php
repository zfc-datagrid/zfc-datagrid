<?php

declare(strict_types=1);

namespace ZfcDatagridTest;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Exception;
use InvalidArgumentException;
use Laminas\Cache\Storage\Adapter\MemoryOptions;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Platform\Sqlite;
use Laminas\Db\Sql\Select;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\MvcEvent;
use Laminas\Paginator\Paginator;
use Laminas\Router\Http\HttpRouterFactory;
use Laminas\Router\Http\Segment;
use Laminas\Router\RoutePluginManagerFactory;
use Laminas\Session\Container;
use Laminas\Stdlib\ResponseInterface;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Throwable;
use ZfcDatagrid\Action\Mass;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Action;
use ZfcDatagrid\Column\Action\AbstractAction;
use ZfcDatagrid\Column\Style\Bold;
use ZfcDatagrid\Column\Style\Italic;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\DataSource\Doctrine2;
use ZfcDatagrid\DataSource\Doctrine2Collection;
use ZfcDatagrid\DataSource\LaminasSelect;
use ZfcDatagrid\DataSource\PhpArray;
use ZfcDatagrid\Renderer\AbstractRenderer;
use ZfcDatagridTest\Util\ServiceManagerFactory;
use ZfcDatagridTest\Util\TestBase;

use function array_keys;
use function array_search;
use function md5;

use const PHP_VERSION_ID;

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

    public function setUp(): void
    {
        $config = include './config/module.config.php';
        $config = $config['ZfcDatagrid'];

        $cacheOptions                          = new MemoryOptions();
        $config['cache']['adapter']['name']    = 'Memory';
        $config['cache']['adapter']['options'] = $cacheOptions->toArray();

        $this->config = $config;

        $mvcEvent = $this->getMockBuilder(MvcEvent::class)->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($this->getMockBuilder(Request::class)->getMock()));

        $this->grid = new Datagrid();
        $this->grid->setOptions($this->config);
        $this->grid->setMvcEvent($mvcEvent);
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
        $this->assertInstanceOf(Container::class, $this->grid->getSession());
        $this->assertEquals('defaultGrid', $this->grid->getSession()
            ->getName());

        $session = new Container('myName');

        $this->grid->setSession($session);
        $this->assertInstanceOf(Container::class, $this->grid->getSession());
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

    public function testMvcEvent()
    {
        $this->assertInstanceOf(MvcEvent::class, $this->grid->getMvcEvent());

        $mvcEvent = $this->getMockBuilder(MvcEvent::class)->getMock();
        $this->grid->setMvcEvent($mvcEvent);
        $this->assertInstanceOf(MvcEvent::class, $this->grid->getMvcEvent());
        $this->assertEquals($mvcEvent, $this->grid->getMvcEvent());
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

        $this->expectException(InvalidArgumentException::class);
        $grid->setDataSource(null);
    }

    public function testDataSourceLaminasSelect()
    {
        $grid = new Datagrid();

        $this->assertFalse($grid->hasDataSource());

        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $platform = $this->getMockBuilder(Sqlite::class)
            ->getMock();
        $platform->expects(self::any())
            ->method('getName')
            ->will($this->returnValue('myPlatform'));

        $adapter = $this->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapter->expects(self::any())
            ->method('getPlatform')
            ->will($this->returnValue($platform));

        $grid->setDataSource($select, $adapter);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(LaminasSelect::class, $grid->getDataSource());

        $this->expectException(InvalidArgumentException::class);
        $this->expectDeprecationMessage('For "Laminas\Db\Sql\Select" also a "Laminas\Db\Adapter\Sql" or "Laminas\Db\Sql\Sql" is needed.');
        $grid->setDataSource($select);
    }

    public function testDataSourceDoctrine()
    {
        $grid = new Datagrid();

        $this->assertFalse($grid->hasDataSource());

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grid->setDataSource($qb);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(Doctrine2::class, $grid->getDataSource());
    }

    public function testDataSourceDoctrineCollection()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'If providing a Collection, also the Doctrine\ORM\EntityManager is needed as a second parameter'
        );
        $grid = new Datagrid();

        $this->assertFalse($grid->hasDataSource());

        $coll = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $em   = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $grid->setDataSource($coll, $em);
        $this->assertTrue($grid->hasDataSource());
        $this->assertInstanceOf(Doctrine2Collection::class, $grid->getDataSource());

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

        $col = $this->getMockBuilder(AbstractColumn::class)
            ->setMethods(['getUniqueId'])
            ->getMock();

        $col->expects(self::any())
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
        if (PHP_VERSION_ID >= 80000) {
            $this->expectExceptionMessage(
                'ZfcDatagrid\Datagrid::createColumn(): Argument #1 ($config) must be of type array, null given'
            );
        } else {
            $this->expectExceptionMessage(
                'Argument 1 passed to ZfcDatagrid\Datagrid::createColumn() must be of the type array'
            );
        }
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

    public function testAddColumnArrayInvalidColType()
    {
        $grid = new Datagrid();
        $this->assertEquals([], $grid->getColumns());

        $column = [
            'colType' => 'ZfcDatagrid\Column\Unknown',
            'label'   => 'My label',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Column type: "ZfcDatagrid\Column\Unknown" not found!');
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

    public function testAddColumnArraySelectInvalidArgumentException()
    {
        $grid = new Datagrid();
        $this->assertEquals([], $grid->getColumns());

        $column = [
            'label' => 'My label',
        ];

        $this->expectExceptionMessage('For "ZfcDatagrid\Column\Select" the option select[column] must be defined!');
        $this->expectException(InvalidArgumentException::class);
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
        $this->assertInstanceOf(Action::class, $col);
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
            'select'      => [
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
            'select'      => [
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

        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('myUniqueId');

        $col2 = $this->getMockForAbstractClass(AbstractColumn::class);
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

        $grid->addRowStyle($this->getMockBuilder(Bold::class)->getMock());
        $this->assertCount(1, $grid->getRowStyles());
        $this->assertTrue($grid->hasRowStyles());

        $grid->addRowStyle($this->getMockBuilder(Italic::class)->getMock());
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

        $action = $this->getMockForAbstractClass(AbstractAction::class);
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

        $request  = new \Laminas\Console\Request();
        $mvcEvent = $this->getMockBuilder(MvcEvent::class)->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->grid->setMvcEvent($mvcEvent);
        $this->assertEquals('laminasTable', $this->grid->getRendererName());

        // change default
        $this->grid->setRendererName('myRenderer');
        $this->assertEquals('myRenderer', $this->grid->getRendererName());

        // by HTTP request
        $_GET['rendererType'] = 'jqGrid';
        $request              = new Request();
        $mvcEvent             = $this->getMockBuilder(MvcEvent::class)->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($request));
        $this->grid->setMvcEvent($mvcEvent);
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
        $this->assertInstanceOf(ViewModel::class, $defaultView);
        $this->assertSame($defaultView, $grid->getViewModel());
    }

    public function testSetViewModel()
    {
        $grid = new Datagrid();

        $customView = $this->getMockBuilder(ViewModel::class)->getMock();
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

        $customView = $this->getMockBuilder(ViewModel::class)->getMock();

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
            'select'   => [
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
            'select'   => [
                'column' => 'myCol4',
                'table'  => 'myTable',
            ],
            'position' => 2,
        ]);
        $grid->addColumn([
            'select'   => [
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
        $class                  = $this->getClass();
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
        $class                  = $this->getClass();
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
