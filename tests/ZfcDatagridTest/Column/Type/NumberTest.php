<?php
namespace ZfcDatagridTest\Column\Type;

use Locale;
use NumberFormatter;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Filter;
use ZfcDatagridTest\Util\TestBase;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Type\Number
 */
class NumberTest extends TestBase
{
    /** @var string */
    protected $className = Type\Number::class;

    /** @var Type\Number */
    private $numberFormatterAT;

    /** @var Type\Number */
    private $numberFormatterEN;

    public function setUp()
    {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('ext/intl not enabled');
        }

        $type = new Type\Number();
        $type->setLocale('de_AT');
        $this->numberFormatterAT = $type;

        $type = new Type\Number();
        $type->setLocale('en_US');
        $this->numberFormatterEN = $type;
    }

    public function testConstruct()
    {
        $type = new Type\Number();

        $this->assertEquals(NumberFormatter::DECIMAL, $type->getFormatStyle());
        $this->assertEquals(NumberFormatter::TYPE_DEFAULT, $type->getFormatType());
        $this->assertEquals(Locale::getDefault(), $type->getLocale());

        $this->assertEquals(Filter::EQUAL, $type->getFilterDefaultOperation());
    }

    public function testTypeName()
    {
        $type = new Type\Number();

        $this->assertEquals('number', $type->getTypeName());
    }

    public function testFormatStyle()
    {
        $type = new Type\Number();
        $type->setFormatStyle(NumberFormatter::CURRENCY);
        $this->assertEquals(NumberFormatter::CURRENCY, $type->getFormatStyle());
    }

    public function testFormatType()
    {
        $type = new Type\Number();
        $type->setFormatType(NumberFormatter::TYPE_DOUBLE);
        $this->assertEquals(NumberFormatter::TYPE_DOUBLE, $type->getFormatType());
    }

    public function testLocale()
    {
        $type = new Type\Number();
        $type->setLocale('de_AT');
        $this->assertEquals('de_AT', $type->getLocale());
    }

    public function testAttribute()
    {
        $type = new Type\Number();

        $this->assertCount(0, $type->getAttributes());

        $type->addAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $this->assertCount(1, $type->getAttributes());
    }

    public function testSuffixPreffix()
    {
        $type = new Type\Number();

        $this->assertEquals('', $type->getPrefix());
        $this->assertEquals('', $type->getSuffix());

        $type->setPrefix('$');
        $this->assertEquals('$', $type->getPrefix());

        $type->setSuffix('EURO');
        $this->assertEquals('EURO', $type->getSuffix());
    }

    /**
     * Convert the user value to a filter value
     */
    public function testFilterValueAT()
    {
        $type = clone $this->numberFormatterAT;
        $this->assertEquals('23.15', $type->getFilterValue('23,15'));

        $type->setPrefix('€');
        $this->assertEquals('23.15', $type->getFilterValue('€23,15'));

        $type->setSuffix('#');
        $this->assertEquals('23.15', $type->getFilterValue('€23,15#'));
    }

    /**
     * Convert the user value to a filter value
     */
    public function testFilterValueEN()
    {
        $type = clone $this->numberFormatterEN;
        $this->assertEquals('23.15', $type->getFilterValue('23.15'));

        $type->setPrefix('€');
        $this->assertEquals('23.15', $type->getFilterValue('€23.15'));

        $type->setSuffix('#');
        $this->assertEquals('23.15', $type->getFilterValue('€23.15#'));
    }

    public function testFilterValueException(): void
    {
        $formatter = $this->getMockBuilder(\NumberFormatter::class)
            ->setMethods(['parse'])
            ->disableOriginalConstructor()
            ->getMock();

        $formatter->expects($this->once())
            ->method('parse')
            ->willThrowException(new \Exception('test parse exception'));

        $this->mockedMethodList = ['getFormatter'];
        $class = $this->getClass();
        $class->expects($this->once())
            ->method('getFormatter')
            ->willReturn($formatter);

        $this->assertSame(
            'dfsdf',
            $this->getMethod('getFilterValue')->invokeArgs($this->getClass(), ['dfsdf'])
        );
    }

    /**
     * Convert the database value to a display value
     */
    public function testUserValueAT()
    {
        $type = clone $this->numberFormatterAT;

        $this->assertEquals('23,15', $type->getUserValue(23.15));

        $type->setPrefix('€');
        $this->assertEquals('€23,15', $type->getUserValue(23.15));

        $type->setSuffix('#');
        $this->assertEquals('€23,15#', $type->getUserValue(23.15));
    }

    public function testWrongValues()
    {
        $type = clone $this->numberFormatterAT;

        // Print the user a 0
        $this->assertEquals('0', $type->getUserValue('myString'));

        // Filtering converting is dangerous, so keep the value...
        $this->assertEquals('myString', $type->getFilterValue('myString'));
    }

    public function testPattern(): void
    {
        $type = $this->numberFormatterEN;
        
        $this->assertNull($type->getPattern());

        $type->setPattern('foobar');
        $this->assertSame('foobar', $type->getPattern());

        $type->setPattern(null);
        $this->assertNull($type->getPattern());
    }

    public function testGetFormatter(): void
    {
        $this->setProperty('pattern', 'pattern');
        $this->setProperty('attributes', [[
            'attribute' => 5,
            'value'     => 3,
        ]]);
        $actual = $this->getMethod('getFormatter')->invoke($this->getClass());

        $this->assertSame('pattern#000', $actual->getPattern());
        $this->assertSame(3, $actual->getAttribute(5));
    }
}
