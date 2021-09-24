<?php
namespace ZfcDatagridTest\Renderer\BootstrapTable;

use Exception;
use PHPUnit\Framework\TestCase;
use Laminas\View\Model\ViewModel;
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

    public function testGetRequestException()
    {
        $request = $this->getMockBuilder(\Laminas\Console\Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mvcEvent = $this->getMockBuilder(\Laminas\Mvc\MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mvcEvent->expects(self::any())
        ->method('getRequest')
        ->will($this->returnValue($request));

        $renderer = new BootstrapTable\Renderer();
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

        $mvcEvent = $this->getMockBuilder(\Laminas\Mvc\MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mvcEvent->expects(self::any())
        ->method('getRequest')
        ->will($this->returnValue($request));

        $renderer = new BootstrapTable\Renderer();
        $renderer->setMvcEvent($mvcEvent);

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
