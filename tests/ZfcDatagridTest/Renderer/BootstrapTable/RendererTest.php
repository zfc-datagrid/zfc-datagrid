<?php
namespace ZfcDatagridTest\Renderer\BootstrapTable;

use PHPUnit\Framework\TestCase;
use Zend\View\Model\ViewModel;
use ZfcDatagrid\Renderer\BootstrapTable;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group Renderer
 * @covers \ZfcDatagrid\Renderer\BootstrapTable\Renderer
 */
class RendererTest extends TestBase
{
    /** @var string */
    protected $className = BootstrapTable\Renderer::class;

    public function testGetName()
    {
        $renderer = new BootstrapTable\Renderer();

        $this->assertEquals('bootstrapTable', $renderer->getName());
    }

    public function testIsExport()
    {
        $renderer = new BootstrapTable\Renderer();

        $this->assertFalse($renderer->isExport());
    }

    public function testIsHtml()
    {
        $renderer = new BootstrapTable\Renderer();

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

        $renderer = new BootstrapTable\Renderer();
        $renderer->setRequest($request);

        $renderer->getRequest();
    }

    public function testGetRequest()
    {
        $request = $this->getMockBuilder(\Zend\Http\PhpEnvironment\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = new BootstrapTable\Renderer();
        $renderer->setRequest($request);

        $this->assertEquals($request, $renderer->getRequest());
    }

    public function testExecute(): void
    {
        $this->mockedMethodList = [
            'getViewModel',
        ];
        $viewModel = $this->getMockBuilder(ViewModel::class)
            ->getMock();

        $viewModel->expects($this->once())
            ->method('setTemplate');
        $class = $this->getClass();
        $class->expects($this->once())
            ->method('getViewModel')
            ->willReturn($viewModel);

        $this->assertSame($viewModel, $this->getMethod('execute')->invoke($this->getClass()));
    }
}
