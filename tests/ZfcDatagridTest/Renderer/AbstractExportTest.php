<?php
namespace ZfcDatagridTest\Renderer;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @group Renderer
 * @covers \ZfcDatagrid\Renderer\AbstractExport
 */
class AbstractExportTest extends TestCase
{
    /**
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $exportMock;

    public function setUp(): void
    {
        $this->exportMock = $this->getMockForAbstractClass(\ZfcDatagrid\Renderer\AbstractExport::class);
    }

    public function testFilename()
    {
        $exportMock = clone $this->exportMock;

        $reflection = new ReflectionClass(get_class($exportMock));
        $method     = $reflection->getMethod('getFilename');
        $method->setAccessible(true);

        $filename = $method->invokeArgs($exportMock, []);
        $this->assertEquals(date('Y-m-d_H-i-s'), $filename);

        $exportMock->setTitle('My title');

        $filename = $method->invokeArgs($exportMock, []);
        $this->assertEquals(date('Y-m-d_H-i-s') . '_My_title', $filename);
    }

    public function testPaperWidth()
    {
        $exportMock = clone $this->exportMock;
        $exportMock->expects(self::any())
            ->method('getName')
            ->will($this->returnValue('abstract'));

        $reflection = new ReflectionClass(get_class($exportMock));
        $method     = $reflection->getMethod('getPaperWidth');
        $method->setAccessible(true);

        /*
         * A4 landscape
         */
        $options = [
            'renderer' => [
                'abstract' => [
                    'papersize'   => 'A4',
                    'orientation' => 'landscape',
                ],
            ],
        ];
        $exportMock->setOptions($options);

        $width = $method->invoke($exportMock);
        $this->assertEquals(297, $width);

        /*
         * A4 portrait
         */
        $options = [
            'renderer' => [
                'abstract' => [
                    'papersize'   => 'A4',
                    'orientation' => 'portrait',
                ],
            ],
        ];
        $exportMock->setOptions($options);

        $width = $method->invoke($exportMock);
        $this->assertEquals(210, $width);

        /*
         * A0 portrait
         */
        $options = [
            'renderer' => [
                'abstract' => [
                    'papersize'   => 'A0',
                    'orientation' => 'portrait',
                ],
            ],
        ];
        $exportMock->setOptions($options);

        $width = $method->invoke($exportMock);
        $this->assertEquals(841, $width);

        /*
         * A0 portrait
         */
        $options = [
            'renderer' => [
                'abstract' => [
                    'papersize'   => 'something',
                    'orientation' => 'portrait',
                ],
            ],
        ];
        $exportMock->setOptions($options);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Currently only "A" paper formats are supported!');

        $width = $method->invoke($exportMock);
    }
}
