<?php
namespace ZfcDatagridTest\Renderer\PrintHtml;

use Laminas\View\Model\ViewModel;
use ZfcDatagrid\Renderer\PrintHtml;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group Renderer
 * @covers \ZfcDatagrid\Renderer\PrintHtml\Renderer
 */
class RendererTest extends TestBase
{
    /** @var string */
    protected $className = PrintHtml\Renderer::class;

    public function testGetName()
    {
        $renderer = new PrintHtml\Renderer();

        $this->assertEquals('printHtml', $renderer->getName());
    }

    public function testIsExport()
    {
        $renderer = new PrintHtml\Renderer();

        $this->assertTrue($renderer->isExport());
    }

    public function testIsHtml()
    {
        $renderer = new PrintHtml\Renderer();

        $this->assertTrue($renderer->isHtml());
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
        $viewModel->expects($this->once())
            ->method('setTerminal');
        $viewModel->expects($this->once())
            ->method('addChild');
        $viewModel->expects($this->once())
            ->method('getVariables')
            ->willReturn([]);

        $class = $this->getClass();
        $class->expects($this->once())
            ->method('getViewModel')
            ->willReturn($viewModel);

        $this->assertSame($viewModel, $this->getMethod('execute')->invoke($this->getClass()));
    }
}
