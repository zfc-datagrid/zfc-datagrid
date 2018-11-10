<?php
namespace ZfcDatagrid\Column\Style;

use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Filter;

abstract class AbstractStyle
{
    /** @var string */
    protected $byValueOperator = 'OR';

    /** @var array */
    private $byValues = [];

    /**
     * Display the values with AND or OR (if multiple showOnValues are defined).
     *
     * @param string $operator
     *
     * @return $this
     */
    public function setByValueOperator(string $operator = 'OR'): self
    {
        if ($operator != 'AND' && $operator != 'OR') {
            throw new \InvalidArgumentException('not allowed operator: "' . $operator . '" (AND / OR is allowed)');
        }

        $this->byValueOperator = $operator;

        return $this;
    }

    /**
     * Get the show on value operator, e.g.
     * OR, AND.
     *
     * @return string
     */
    public function getByValueOperator(): string
    {
        return $this->byValueOperator;
    }

    /**
     * Set the style value based and not general.
     *
     * @param AbstractColumn $column
     * @param mixed          $value
     * @param string         $operator
     *
     * @return $this
     */
    public function addByValue(AbstractColumn $column, $value, $operator = Filter::EQUAL): self
    {
        $this->byValues[] = [
            'column'   => $column,
            'value'    => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getByValues(): array
    {
        return $this->byValues;
    }

    /**
     * @return bool
     */
    public function hasByValues(): bool
    {
        return !empty($this->byValues);
    }

    /**
     * @param array $row
     *
     * @return bool
     */
    public function isApply(array $row): bool
    {
        if (false === $this->hasByValues()) {
            return true;
        }

        $isApply = false;
        foreach ($this->getByValues() as $rule) {
            $value = '';
            if (isset($row[$rule['column']->getUniqueId()])) {
                $value = $row[$rule['column']->getUniqueId()];
            }

            if ($rule['value'] instanceof AbstractColumn) {
                if (isset($row[$rule['value']->getUniqueId()])) {
                    $ruleValue = $row[$rule['value']->getUniqueId()];
                } else {
                    $ruleValue = '';
                }
            } else {
                $ruleValue = $rule['value'];
            }

            $isApplyMatch = Filter::isApply($value, $ruleValue, $rule['operator']);
            if ($this->getByValueOperator() == 'OR' && true === $isApplyMatch) {
                // For OR one match is enough
                return true;
            } elseif ($this->getByValueOperator() == 'AND' && false === $isApplyMatch) {
                return false;
            } else {
                $isApply = $isApplyMatch;
            }
        }

        return $isApply;
    }
}
