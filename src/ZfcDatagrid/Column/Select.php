<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column;

use Exception;

use function is_object;
use function is_string;

class Select extends AbstractColumn
{
    /** @var string|object|null */
    protected $selectPart1 = '';

    /** @var string|object|null */
    protected $selectPart2;

    /**
     * Specific column function filter e.g.
     * WHERE MONTH(%s).
     *
     * @var string|null
     */
    private $filterSelectExpression;

    /**
     * Possible calls:
     * $column = new Column('id', 'user')
     * Select the id from the user table -> UNIQUE is the comination of both
     * $column = new Column('title')
     * Just the title from an array -> UNIQUE will be just the first parameter
     * $column = new Column('(SELECT GROUP_CONCAT....)', 'someAlias')
     * Use the subselect -> UNIQUE will be the second parameter.
     *
     * @param string|object $columnOrIndexOrObject
     * @param string|null   $tableOrAliasOrUniqueId
     * @throws Exception
     */
    public function __construct($columnOrIndexOrObject, $tableOrAliasOrUniqueId = null)
    {
        if ($tableOrAliasOrUniqueId !== null && ! is_string($tableOrAliasOrUniqueId)) {
            throw new Exception('Variable $tableOrAliasOrUniqueId must be null or a string');
        }

        if (is_string($columnOrIndexOrObject) && $tableOrAliasOrUniqueId !== null) {
            $this->setUniqueId($tableOrAliasOrUniqueId . '_' . $columnOrIndexOrObject);
            $this->setSelect($tableOrAliasOrUniqueId, $columnOrIndexOrObject);
        } elseif (is_string($columnOrIndexOrObject)) {
            $this->setUniqueId($columnOrIndexOrObject);
            $this->setSelect($columnOrIndexOrObject);
        } elseif (
            is_object($columnOrIndexOrObject) &&
            null !== $tableOrAliasOrUniqueId &&
            is_string($tableOrAliasOrUniqueId)
        ) {
            $this->setUniqueId($tableOrAliasOrUniqueId);
            $this->setSelect($columnOrIndexOrObject);
        } else {
            throw new Exception('Column was not initiated correctly, please read the __construct docblock!');
        }
    }

    /**
     * @params string|object|null $part1
     * @params string|object|null $part2
     * @return $this
     */
    public function setSelect($part1, $part2 = null): self
    {
        $this->selectPart1 = $part1;
        $this->selectPart2 = $part2;

        return $this;
    }

    /**
     * @return string|object|null
     */
    public function getSelectPart1()
    {
        return $this->selectPart1;
    }

    /**
     * @return string|object|null
     */
    public function getSelectPart2()
    {
        return $this->selectPart2;
    }

    /**
     * @return $this
     */
    public function setFilterSelectExpression(?string $filterSelectExpression): self
    {
        $this->filterSelectExpression = $filterSelectExpression;

        return $this;
    }

    public function getFilterSelectExpression(): ?string
    {
        return $this->filterSelectExpression;
    }

    public function hasFilterSelectExpression(): bool
    {
        return null !== $this->filterSelectExpression;
    }
}
