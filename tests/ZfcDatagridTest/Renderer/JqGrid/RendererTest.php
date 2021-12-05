<?php

declare(strict_types=1);

namespace ZfcDatagridTest\Renderer\JqGrid;

use Exception;
use Laminas\Console\Request;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;
use ZfcDatagrid\Renderer\JqGrid;

/**
 * @group Renderer
 * @covers \ZfcDatagrid\Renderer\JqGrid\Renderer
 */
class RendererTest extends TestCase
{
    private $options = [
        'renderer' => [
            'jqGrid' => [
                'parameterNames' => [
                    'sortColumns'    => 'cols',
                    'sortDirections' => 'dirs',
                    'currentPage'    => 'page',
                    'itemsPerPage'   => 'items',
                ],
            ],
        ],
    ];

    public function testGetName()
    {
        $renderer = new JqGrid\Renderer();

        $this->assertEquals('jqGrid', $renderer->getName());
    }

    public function testIsExport()
    {
        $renderer = new JqGrid\Renderer();

        $this->assertFalse($renderer->isExport());
    }

    public function testIsHtml()
    {
        $renderer = new JqGrid\Renderer();

        $this->assertTrue($renderer->isHtml());
    }

    public function testGetRequestException()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mvcEvent = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $renderer = new JqGrid\Renderer();
        $renderer->setMvcEvent($mvcEvent);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Request must be an instance of Laminas\Http\PhpEnvironment\Request for HTML rendering');

        $renderer->getRequest();
    }

    public function testGetRequest()
    {
        $request = $this->getMockBuilder(\Laminas\Http\PhpEnvironment\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mvcEvent = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mvcEvent->expects(self::any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $renderer = new JqGrid\Renderer();
        $renderer->setMvcEvent($mvcEvent);

        $this->assertEquals($request, $renderer->getRequest());
    }

    public function testGetSortConditions()
    {
        $this->assertInstanceOf(JqGrid\Renderer::class, new JqGrid\Renderer());
    }
}
