<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Type;

use Exception;
use InvalidArgumentException;
use ZfcDatagrid\Filter;

use function in_array;
use function sprintf;
use function strval;

abstract class AbstractType implements TypeInterface
{
    /** @var string */
    protected $filterDefaultOperation = Filter::LIKE;

    /**
     * the default filter operation.
     */
    public function getFilterDefaultOperation(): string
    {
        return $this->filterDefaultOperation;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function setFilterDefaultOperation(string $operator): TypeInterface
    {
        if (! in_array($operator, Filter::AVAILABLE_OPERATORS)) {
            throw new InvalidArgumentException(sprintf('Invalid filter operator \'%s\'', strval($operator)));
        }

        $this->filterDefaultOperation = $operator;

        return $this;
    }

    /**
     * Convert the user value to a general value, which will be filtered.
     */
    public function getFilterValue(string $val): string
    {
        return $val;
    }

    /**
     * Convert the value from the source to the value, which the user will see.
     *
     * @param mixed $val
     * @return mixed
     */
    public function getUserValue($val)
    {
        return $val;
    }

    /**
     * Get the type name.
     */
    abstract public function getTypeName(): string;
}
