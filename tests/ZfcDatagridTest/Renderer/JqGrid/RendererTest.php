<?php
namespace ZfcDatagridTest\Renderer\JqGrid;

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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Request must be an instance of Zend\Http\PhpEnvironment\Request for HTML rendering
     */
    public function testGetRequestException()
    {
        $request = $this->getMockBuilder(\Zend\Console\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = new JqGrid\Renderer();
        $renderer->setRequest($request);

        $renderer->getRequest();
    }

    public function testGetRequest()
    {
        $request = $this->getMockBuilder(\Zend\Http\PhpEnvironment\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = new JqGrid\Renderer();
        $renderer->setRequest($request);

        $this->assertEquals($request, $renderer->getRequest());
    }

    public function testGetSortConditions()
    {
        $this->assertInstanceOf(JqGrid\Renderer::class, new JqGrid\Renderer());
    }
}
