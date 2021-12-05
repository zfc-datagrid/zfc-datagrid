<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Renderer;

use Laminas\Http\Request;
use Laminas\I18n\Translator\Translator;
use Laminas\Mvc\MvcEvent;
use Laminas\Paginator\Adapter\ArrayAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Filter;
use ZfcDatagrid\Renderer\AbstractRenderer;
use ZfcDatagrid\Renderer\TCPDF\Renderer;

use function get_class;
use function range;

/**
 * @group Renderer
 * @covers \ZfcDatagrid\Renderer\AbstractRenderer
 */
class AbstractRendererTest extends TestCase
{
    /** @var AbstractColumn */
    private $colMock;

    public function setUp(): void
    {
        $this->colMock = $this->getMockForAbstractClass(AbstractColumn::class);
    }

    public function testOptions()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);
        $renderer->setOptions([
            'test',
        ]);

        $this->assertEquals([
            'test',
        ], $renderer->getOptions());
    }

    public function testRendererOptions()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);
        $renderer->expects(self::any())
            ->method('getName')
            ->will($this->returnValue('abstract'));

        $this->assertEquals([], $renderer->getOptionsRenderer());

        $renderer->setOptions([
            'renderer' => [
                'abstract' => [
                    'test',
                ],
            ],
        ]);

        $this->assertEquals([
            'test',
        ], $renderer->getOptionsRenderer());
    }

    public function testViewModel()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertNull($renderer->getViewModel());

        $viewModel = $this->getMockBuilder(ViewModel::class)
            ->getMock();
        $renderer->setViewModel($viewModel);
        $this->assertSame($viewModel, $renderer->getViewModel());
    }

    public function testTemplate()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);
        $renderer->expects(self::any())
            ->method('getName')
            ->will($this->returnValue('abstract'));

        $this->assertEquals('zfc-datagrid/renderer/abstract/layout', $renderer->getTemplate());
        $this->assertEquals('zfc-datagrid/toolbar/toolbar', $renderer->getToolbarTemplate());

        $renderer->setTemplate('blubb/layout');
        $this->assertEquals('blubb/layout', $renderer->getTemplate());

        $renderer->setToolbarTemplate('blubb/toolbar');
        $this->assertEquals('blubb/toolbar', $renderer->getToolbarTemplate());
    }

    public function testTemplateConfig()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);
        $renderer->expects(self::any())
            ->method('getName')
            ->will($this->returnValue('abstract'));

        $renderer->setOptions([
            'renderer' => [
                'abstract' => [
                    'templates' => [
                        'layout'  => 'config/my/template',
                        'toolbar' => 'config/my/toolbar',
                    ],
                ],
            ],
        ]);

        $this->assertEquals('config/my/template', $renderer->getTemplate());
        $this->assertEquals('config/my/toolbar', $renderer->getToolbarTemplate());
    }

    public function testPaginator()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertNull($renderer->getPaginator());

        $testCollection = range(1, 101);
        $pagintorMock   = new Paginator(new ArrayAdapter($testCollection));
        $renderer->setPaginator($pagintorMock);

        $this->assertSame($pagintorMock, $renderer->getPaginator());
    }

    public function testColumns()
    {
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertEquals([], $renderer->getColumns());

        $col = clone $this->colMock;
        $renderer->setColumns([
            $col,
        ]);

        $this->assertEquals([
            $col,
        ], $renderer->getColumns());
    }

    public function testRowStyles()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertEquals([], $renderer->getRowStyles());

        $bold = new Style\Bold();
        $renderer->setRowStyles([
            $bold,
        ]);
        $this->assertEquals([
            $bold,
        ], $renderer->getRowStyles());
    }

    public function testCalculateColumnWidthPercent()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $reflection = new ReflectionClass(get_class($renderer));
        $method     = $reflection->getMethod('calculateColumnWidthPercent');
        $method->setAccessible(true);

        $col1 = clone $this->colMock;
        $cols = [
            $col1,
        ];

        /*
         * Width lower than 100%
         */
        $this->assertEquals(5, $col1->getWidth());
        $method->invokeArgs($renderer, [
            $cols,
        ]);
        $this->assertEquals(100, $col1->getWidth());

        /*
         * Width higher than 100%
         */
        $col1 = clone $this->colMock;
        $col1->setWidth(90);

        $col2 = clone $this->colMock;
        $col2->setWidth(60);
        $cols = [
            $col1,
            $col2,
        ];

        $method->invokeArgs($renderer, [
            $cols,
        ]);
        $this->assertEquals(60, $col1->getWidth());
        $this->assertEquals(40, $col2->getWidth());
    }

    public function testData()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertEquals([], $renderer->getData());

        $data = [
            [
                'myCol' => 123,
            ],
        ];
        $renderer->setData($data);
        $this->assertEquals($data, $renderer->getData());
    }

//     public function testCacheData()
//     {
//         $cache = $this->getMockForAbstractClass(\Laminas\Cache\Storage\Adapter\AbstractAdapter::class);

//         /* @var $renderer \ZfcDatagrid\Renderer\AbstractRenderer */
//         $renderer = $this->getMockForAbstractClass(\ZfcDatagrid\Renderer\AbstractRenderer::class);
//         $renderer->setCache($cache);

//         $this->assertEquals(array(), $renderer->getCacheData());

//         $data = array(
//             'sortConditions' => '',
//             'filters' => '',
//             'currentPage' => 123,
//             'data' => array(
//                 array(
//                     'myCol' => 123
//                 )
//             )
//         );
//         $renderer->setCacheData($data);
//         $this->assertEquals($data, $renderer->getCacheData());
//     }

    public function testMvcEvent()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mvcEvent = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $this->assertNull($renderer->getMvcEvent());
        $renderer->setMvcEvent($mvcEvent);
        $this->assertSame($mvcEvent, $renderer->getMvcEvent());

        // request
        $this->assertSame($request, $renderer->getRequest());
    }

    public function testTranslator()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull($renderer->getTranslator());
        $renderer->setTranslator($translator);
        $this->assertSame($translator, $renderer->getTranslator());
    }

    public function testTranslate()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);
        $this->assertEquals('foobar', $renderer->translate('foobar'));

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->setMethods(['translate'])
            ->getMock();
        $translator->expects(self::any())
            ->method('translate')
            ->willReturn('barfoo');

        $renderer->setTranslator($translator);

        $this->assertEquals('barfoo', $renderer->translate('foobar'));
    }

    public function testTitle()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertSame('', $renderer->getTitle());

        $renderer->setTitle('My title');
        $this->assertEquals('My title', $renderer->getTitle());
    }

    public function testCacheId()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertNull($renderer->getCacheId());

        $renderer->setCacheId('a_cache_id');
        $this->assertEquals('a_cache_id', $renderer->getCacheId());
    }

    public function testGetSortConditionsSortEmpty()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        // no sorting
        $sortConditions = $renderer->getSortConditions();
        $this->assertEquals([], $sortConditions);

        // 2nd call -> from array
        $sortConditions = $renderer->getSortConditions();
        $this->assertEquals([], $sortConditions);
    }

    public function testGetSortConditionsSortDefault()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $col1 = clone $this->colMock;
        $col1->setUniqueId('myCol');
        $col1->setSortDefault(1);
        $renderer->setColumns([
            $col1,
        ]);

        $sortConditions = $renderer->getSortConditions();
        $this->assertEquals([
            1 => [
                'column'        => $col1,
                'sortDirection' => 'ASC',
            ],
        ], $sortConditions);
    }

    public function testGetFiltersDefault()
    {
        $request = $this->getMockBuilder(\Laminas\Http\PhpEnvironment\Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects(self::any())
            ->method('isPost')
            ->will($this->returnValue(false));

        $mvcEvent = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);
        $renderer->setMvcEvent($mvcEvent);

        $col1 = clone $this->colMock;
        $col1->setUniqueId('myCol');
        $col1->setFilterDefaultValue('filterValue');
        $renderer->setColumns([
            $col1,
        ]);

        $filters = $renderer->getFiltersDefault();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(Filter::class, $filters[0]);

        // getFilters are the same like getFiltersDefault in this case
        $this->assertEquals($filters, $renderer->getFilters());

        // 2nd call from array cache
        $this->assertEquals($filters, $renderer->getFilters());
    }

    public function testGetFiltersNothingOnlyFromCustom()
    {
        $this->markTestSkipped();
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $col1 = clone $this->colMock;
        $col1->setUniqueId('myCol');
        $col1->setFilterDefaultValue('filterValue');
        $renderer->setColumns([
            $col1,
        ]);
        $filters = $renderer->getFiltersDefault();

        // getFilters are the same like getFiltersDefault in this case
        $this->assertEquals($filters, $renderer->getFilters());

        // 2nd call from array cache
        $this->assertEquals($filters, $renderer->getFilters());
    }

    public function testCurrentPageNumber()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertEquals(1, $renderer->getCurrentPageNumber());

        $renderer->setCurrentPageNumber(25);
        $this->assertEquals(25, $renderer->getCurrentPageNumber());
    }

    public function testGetItemsPerPage()
    {
        /** @var AbstractRenderer $renderer */
        $renderer = $this->getMockForAbstractClass(AbstractRenderer::class);

        $this->assertEquals(25, $renderer->getItemsPerPage());
        $this->assertEquals(100, $renderer->getItemsPerPage(100));

        // exports are unlimited
        /** @var AbstractRenderer $renderer */
        $renderer = new Renderer();
        $this->assertEquals(- 1, $renderer->getItemsPerPage());
    }
}
