<?php
namespace ZfcDatagridTest\Column\Formatter;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use ZfcDatagrid\Column\Formatter\GenerateLink;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Formatter\GenerateLink
 */
class GenerateLinkTest extends TestCase
{
    public function testConstructor()
    {
        /** @var \Zend\View\Renderer\PhpRenderer $phpRenderer */
        $phpRenderer = $this->getMockBuilder(\Zend\View\Renderer\PhpRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generateLink = new GenerateLink($phpRenderer, 'route');

        $this->assertEquals('route', $this->getProperty($generateLink, 'route'));
        $this->assertEmpty($this->getProperty($generateLink, 'routeKey'));
        $this->assertEmpty($this->getProperty($generateLink, 'routeParams'));
    }

    public function testGetFormattedValue()
    {
        /** @var \ZfcDatagrid\Column\AbstractColumn $col */
        $col = $this->getMockForAbstractClass(\ZfcDatagrid\Column\AbstractColumn::class);
        $col->setUniqueId('foo');

        $phpRenderer = $this->getMockBuilder(\Zend\View\Renderer\PhpRenderer::class)
            ->disableOriginalConstructor()
            ->setMethods(['url'])
            ->getMock();

        $phpRenderer->expects($this->any())
            ->method('url')
            ->will($this->returnValue(''));

        $generateLink = new GenerateLink($phpRenderer, 'route');
        $generateLink->setRowData([
            'foo' => 'bar',
        ]);

        $this->assertEquals('<a href="">bar</a>', $generateLink->getFormattedValue($col));
    }

    /**
     * @param $class
     * @param $name
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getProperty($class, $name)
    {
        $class = new ReflectionProperty($class, $name);
        $class->setAccessible(true);
        return $class->getValue($class);
    }
}
