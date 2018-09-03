<?php

namespace ZfcDatagrid\Column\Type;

use ZfcDatagrid\Filter;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var string
     */
    protected $filterDefaultOperation = Filter::LIKE;

    /**
     * the default filter operation.
     *
     * @return string
     */
    public function getFilterDefaultOperation()
    {
        return $this->filterDefaultOperation;
    }

    /**
     * @param string $operator
     *
     * @return $this
     * @throws \Exception
     */
    public function setFilterDefaultOperation($operator)
    {
        if (! in_array($operator, Filter::AVAILABLE_OPERATORS)) {
            throw new \InvalidArgumentException(sprintf('Invalid filter operator \'%s\'', strval($operator)));
        }

        $this->filterDefaultOperation = $operator;

        return $this;
    }

    /**
     * Convert the user value to a general value, which will be filtered.
     *
     * @param string $val
     *
     * @return string
     */
    public function getFilterValue($val)
    {
        return $val;
    }

    /**
     * Convert the value from the source to the value, which the user will see.
     *
     * @param string $val
     *
     * @return string
     */
    public function getUserValue($val)
    {
        return $val;
    }

    /**
     * Get the type name.
     *
     * @return string
     */
    abstract public function getTypeName();
}
