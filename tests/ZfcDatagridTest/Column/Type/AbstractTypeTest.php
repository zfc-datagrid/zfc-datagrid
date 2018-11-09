<?php
namespace ZfcDatagridTest\Column\Type;

use PHPUnit\Framework\TestCase;
// use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Filter;

/**
 * @group Column
 * @covers \ZfcDatagrid\Column\Type\AbstractType
 */
class AbstractTypeTest extends TestCase
{
    /**
     *
     * @var \ZfcDatagrid\Column\Type\AbstractType
     */
    private $type;

    public function setUp(): void
    {
        $this->type = $this->getMockForAbstractClass(\ZfcDatagrid\Column\Type\AbstractType::class);
    }

    /**
     * @throws \Exception
     */
    public function testFilterDefaultOperationException(): void
    {
        // Test default value.
        $this->assertEquals(Filter::LIKE, $this->type->getFilterDefaultOperation());

        // Set incorrect value.
        $this->expectException(\InvalidArgumentException::class);
        $this->type->setFilterDefaultOperation('invalid');
    }

    public function testFilterDefaultOperation(): void
    {
        $this->assertEquals(Filter::LIKE, $this->type->getFilterDefaultOperation());

        // Set correct value.
        foreach (Filter::AVAILABLE_OPERATORS as $operator) {
            $this->assertSame($this->type, $this->type->setFilterDefaultOperation($operator));
            $this->assertEquals($operator, $this->type->getFilterDefaultOperation());
        }
    }

    public function testGetFilterValue(): void
    {
        $this->assertEquals('01.05.12', $this->type->getFilterValue('01.05.12'));
    }

    public function testGetUserValue(): void
    {
        $this->assertEquals('01.05.12', $this->type->getUserValue('01.05.12'));
    }
}
