<?php

namespace ZfcDatagridTest\Column\Formatter;

use Zend\Router\RouteStackInterface;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Column\Formatter;
use ZfcDatagridTest\Util\TestBase;

class HtmlTagTest extends TestBase
{
    /** @var string */
    protected $className = Formatter\HtmlTag::class;

    public function testConstructor()
    {
        $htmlTag = new Formatter\HtmlTag();
        $this->assertInstanceOf(Formatter\AbstractFormatter::class, $htmlTag);
        $this->assertInstanceOf(RouteStackInterface::class, $htmlTag);
    }

    public function testGetLinkColumnPlaceholders()
    {
        $htmlTag = new Formatter\HtmlTag();
        $this->assertSame([], $htmlTag->getLinkColumnPlaceholders());
    }

    public function testGetFormattedValue()
    {
        $this->mockedMethodList = [
            'getRowData',
            'getName',
            'getAttributesString',
        ];

        $class = $this->getClass();
        $class->expects($this->exactly(1))
            ->method('getRowData')
            ->willReturn(['id' => 'foobar']);
        $class->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('span');
        $class->expects($this->exactly(1))
            ->method('getAttributesString')
            ->willReturn('foobar="test"');

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        $this->assertSame('<span foobar="test">foobar</span>', $class->getFormattedValue($col));
    }

    public function testSetLink()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setLink('://example.com');

        $this->assertSame([
            'href' => '://example.com',
        ], $this->getProperty('attributes'));
    }

    public function testSetRouter()
    {
        /** @var RouteStackInterface $router */
        $router = $this->getMockForAbstractClass(RouteStackInterface::class);

        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setRouter($router);

        $this->assertSame($router, $this->getProperty('router'));
    }

    public function testRemoveAttribute()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setLink('://example.com');

        $this->assertSame([
            'href' => '://example.com',
        ], $this->getProperty('attributes'));

        $htmlTag->removeAttribute('href');

        $this->assertSame([], $this->getProperty('attributes'));
    }

    public function testGetLink()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setLink('://example.com');

        $this->assertSame('://example.com', $htmlTag->getLink());
    }

    public function testGetName()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $this->assertSame('span', $htmlTag->getName());
    }

    public function testGetRouter()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $this->assertNull($htmlTag->getRouter());
        /** @var RouteStackInterface $route */
        $route = $this->getMockForAbstractClass(RouteStackInterface::class);
        $htmlTag->setRouter($route);
        $this->assertSame($route, $htmlTag->getRouter());
    }

    public function testGetRowIdPlaceholder()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $this->assertSame(Formatter\HtmlTag::ROW_ID_PLACEHOLDER, $htmlTag->getRowIdPlaceholder());
    }

    public function testGetRouteParams()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $this->assertSame([], $htmlTag->getRouteParams());
    }

    public function testSetAttribute()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $attribute = (string)time();
        $htmlTag->setAttribute('stuff', $attribute);
        $this->assertSame(['stuff' => $attribute], $this->getProperty('attributes'));
    }

    public function testGetColumnValuePlaceholder()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        $this->assertSame(':id:', $htmlTag->getColumnValuePlaceholder($col));
        $this->assertSame([$col], $this->getProperty('linkColumnPlaceholders'));
    }

    public function testSetRoute()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $htmlTag->setRoute('foobar');
        $this->assertSame('foobar', $this->getProperty('route'));
    }

    public function testSetRouteParams()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $htmlTag->setRouteParams(['foobar' => 'barfoo']);
        $this->assertSame(['foobar' => 'barfoo'], $this->getProperty('routeParams'));
    }

    public function testSetName()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setName('a');

        $this->assertSame('a', $this->getProperty('name'));
    }

    public function testGetAttribute()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $attribute = (string)time();
        $htmlTag->setAttribute('stuff', $attribute);
        $this->assertSame('', $htmlTag->getAttribute('foobarrr'));
        $this->assertSame($attribute, $htmlTag->getAttribute('stuff'));
    }

    public function testGetAttributes()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $attribute = (string)time();
        $htmlTag->setAttribute('stuff', $attribute);
        $this->assertSame(['stuff' => $attribute], $htmlTag->getAttributes());
    }

    public function testGetRoute()
    {
        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();

        $this->assertSame('', $htmlTag->getRoute());
        $htmlTag->setRoute('foobar');
        $this->assertSame('foobar', $this->getProperty('route'));
    }

    public function testGetAttributesString()
    {
        /** @var RouteStackInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->getMockForAbstractClass(
            RouteStackInterface::class,
            [],
            'RouteStackInterface',
            true,
            true,
            true,
            ['assemble']
        );
        $route = 'foobar';
        $routeParams = [];

        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        $router->expects($this->exactly(1))
            ->method('assemble')
            ->willReturn('foobar.com/:id:');

        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setRouter($router);
        $htmlTag->setRoute($route);
        $htmlTag->setRouteParams($routeParams);
        $htmlTag->setAttribute('title', 'foobar');
        $htmlTag->getColumnValuePlaceholder($col);
        $htmlTag->setRowData(['id' => 'foobar']);

        $this->assertSame('title="foobar" href="foobar.com/foobar"', $this->getMethod('getAttributesString')->invokeArgs($htmlTag, [$col]));
    }

    public function testGetLinkReplacedWithEmptyLink()
    {
        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setRowData(['id' => 'foobar']);

        $this->assertSame('foobar', $this->getMethod('getLinkReplaced')->invokeArgs($htmlTag, [$col]));
    }

    public function testGetLinkReplacedWithPlaceHolder()
    {
        /** @var AbstractColumn $col */
        $col = $this->getMockForAbstractClass(AbstractColumn::class);
        $col->setUniqueId('id');

        /** @var Formatter\HtmlTag $htmlTag */
        $htmlTag = $this->getClass();
        $htmlTag->setRowData(
            [
                'id' => 'foobar',
                'idConcated' => 'idConcated',
            ]
        );
        $htmlTag->setLink(':id: ' . Formatter\HtmlTag::ROW_ID_PLACEHOLDER . ' end');
        $htmlTag->getColumnValuePlaceholder($col);

        $this->assertSame('foobar idConcated end', $this->getMethod('getLinkReplaced')->invokeArgs($htmlTag, [$col]));
    }
}
