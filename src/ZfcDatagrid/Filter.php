<?php
namespace ZfcDatagrid;

use InvalidArgumentException;
use function substr;
use function trim;
use function explode;
use function is_array;
use function min;
use function max;
use function stripos;
use function strlen;
use function count;

class Filter
{
    /**
     * The constant values are used for display on the usergrid filter
     * This is for help, how the data is filtered really.
     *
     * @var string
     */
    const LIKE = '~ *%s*';

    // OK
    const LIKE_LEFT = '~ *%s';

    // OK
    const LIKE_RIGHT = '~ %s*';

    // OK
    const NOT_LIKE = '!~ *%s*';

    // OK
    const NOT_LIKE_LEFT = '!~ *%s';

    // OK
    const NOT_LIKE_RIGHT = '!~ %s*';

    // OK
    const EQUAL = '= %s';

    // OK
    const NOT_EQUAL = '!= %s';

    // OK
    const GREATER_EQUAL = '>= %s';

    // OK
    const GREATER = '> %s';

    // OK
    const LESS_EQUAL = '<= %s';

    // OK
    const LESS = '< %s';

    // OK
    const IN = '=(%s)';

    // OK
    const NOT_IN = '!=(%s)';

    const BETWEEN = '%s <> %s';

    /**
     * List of all available operations
     *
     * @var string[]
     */
    const AVAILABLE_OPERATORS = [
        self::LIKE,
        self::LIKE_LEFT,
        self::LIKE_RIGHT,
        self::NOT_LIKE,
        self::NOT_LIKE_LEFT,
        self::NOT_LIKE_RIGHT,
        self::EQUAL,
        self::NOT_EQUAL,
        self::GREATER_EQUAL,
        self::GREATER,
        self::LESS_EQUAL,
        self::LESS,
        self::IN,
        self::NOT_IN,
        self::BETWEEN,
    ];

    /** @var Column\AbstractColumn|null */
    private $column;

    /** @var string */
    private $operator = self::LIKE;

    /** @var array */
    private $value = [];

    /** @var string */
    private $displayColumnValue = '';

    /**
     * Apply a filter based on a column.
     *
     * @param Column\AbstractColumn $column
     * @param string $inputFilterValue
     *
     * @return $this
     */
    public function setFromColumn(Column\AbstractColumn $column, string $inputFilterValue): self
    {
        $this->column = $column;
        $this->setColumnOperator($inputFilterValue, $column->getFilterDefaultOperation());

        return $this;
    }

    /**
     * Convert the input filter to operator + filter + display filter value.
     *
     * Partly idea taken from ZfDatagrid
     *
     * @see https://github.com/zfdatagrid/grid/blob/master/library/Bvb/Grid.php#L1438
     *
     * @param string $inputFilterValue
     * @param mixed  $defaultOperator
     *
     * @return $this
     */
    private function setColumnOperator(string $inputFilterValue, $defaultOperator = self::LIKE): self
    {
        $inputFilterValue = (string) $inputFilterValue;
        $inputFilterValue = trim($inputFilterValue);

        $this->displayColumnValue = $inputFilterValue;

        $operator = $defaultOperator;
        $value    = $inputFilterValue;

        if (substr($inputFilterValue, 0, 2) == '=(') {
            $operator = self::IN;
            $value    = substr($inputFilterValue, 2);
            if (substr($value, -1) == ')') {
                $value = substr($value, 0, -1);
            }
        } elseif (substr($inputFilterValue, 0, 3) == '!=(') {
            $operator = self::NOT_IN;
            $value    = substr($inputFilterValue, 3);
            if (substr($value, -1) == ')') {
                $value = substr($value, 0, -1);
            }
        } elseif (substr($inputFilterValue, 0, 2) == '!=' ||
            substr($inputFilterValue, 0, 2) == '<>'
        ) {
            $operator = self::NOT_EQUAL;
            $value    = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 2) == '!~' ||
            substr($inputFilterValue, 0, 1) == '!'
        ) {
            // NOT LIKE or NOT EQUAL
            if (substr($inputFilterValue, 0, 2) == '!~') {
                $value = trim(substr($inputFilterValue, 2));
            } else {
                $value = trim(substr($inputFilterValue, 1));
            }

            if (substr($inputFilterValue, 0, 2) == '!~' ||
                (
                    substr($value, 0, 1) == '%' ||
                    substr($value, -1) == '%' ||
                    substr($value, 0, 1) == '*' ||
                    substr($value, -1) == '*'
                )
            ) {
                // NOT LIKE
                if ((substr($value, 0, 1) == '*' && substr($value, -1) == '*') ||
                    (substr($value, 0, 1) == '%' && substr($value, -1) == '%')
                ) {
                    $operator = self::NOT_LIKE;
                    $value    = substr($value, 1);
                    $value    = substr($value, 0, -1);
                } elseif (substr($value, 0, 1) == '*' || substr($value, 0, 1) == '%') {
                    $operator = self::NOT_LIKE_LEFT;
                    $value    = substr($value, 1);
                } elseif (substr($value, -1) == '*' || substr($value, -1) == '%') {
                    $operator = self::NOT_LIKE_RIGHT;
                    $value    = substr($value, 0, -1);
                } else {
                    $operator = self::NOT_LIKE;
                }
            } else {
                // NOT EQUAL
                $operator = self::NOT_EQUAL;
            }
        } elseif (substr($inputFilterValue, 0, 1) == '~' ||
            substr($inputFilterValue, 0, 1) == '%' ||
            substr($inputFilterValue, -1) == '%' ||
            substr($inputFilterValue, 0, 1) == '*' ||
            substr($inputFilterValue, -1) == '*'
        ) {
            // LIKE
            if (substr($inputFilterValue, 0, 1) == '~') {
                $value = substr($inputFilterValue, 1);
            }
            $value = trim($value);

            if ((substr($value, 0, 1) == '*' && substr($value, -1) == '*') ||
                (substr($value, 0, 1) == '%' && substr($value, -1) == '%')
            ) {
                $operator = self::LIKE;
                $value    = substr($value, 1);
                $value    = substr($value, 0, -1);
            } elseif (substr($value, 0, 1) == '*' || substr($value, 0, 1) == '%') {
                $operator = self::LIKE_LEFT;
                $value    = substr($value, 1);
            } elseif (substr($value, -1) == '*' || substr($value, -1) == '%') {
                $operator = self::LIKE_RIGHT;
                $value    = substr($value, 0, -1);
            } else {
                $operator = self::LIKE;
            }
        } elseif (substr($inputFilterValue, 0, 2) == '==') {
            $operator = self::EQUAL;
            $value    = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '=') {
            $operator = self::EQUAL;
            $value    = substr($inputFilterValue, 1);
        } elseif (substr($inputFilterValue, 0, 2) == '>=') {
            $operator = self::GREATER_EQUAL;
            $value    = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '>') {
            $operator = self::GREATER;
            $value    = substr($inputFilterValue, 1);
        } elseif (substr($inputFilterValue, 0, 2) == '<=') {
            $operator = self::LESS_EQUAL;
            $value    = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '<') {
            $operator = self::LESS;
            $value    = substr($inputFilterValue, 1);
        } elseif (strpos($inputFilterValue, '<>') !== false) {
            $operator = self::BETWEEN;
            $value    = explode('<>', $inputFilterValue);
        }
        $this->operator = $operator;

        if (false === $value) {
            // NO VALUE applied...maybe only "="
            $value = '';
        }

        /*
         * Handle multiple values
         */
        $columnType = $this->getColumn()->getType();
        if ($columnType instanceof Column\Type\DateTime && $columnType->isDaterangePickerEnabled() === true) {
            $value = explode(' - ', $value);
        } elseif (! $columnType instanceof Column\Type\Number && ! is_array($value)) {
            $value = explode(',', $value);
        } elseif (! is_array($value)) {
            $value = [$value];
        }

        foreach ($value as &$val) {
            $val = trim($val);
        }

        if (self::BETWEEN == $operator) {
            if (! $this->getColumn()->getType() instanceof Column\Type\DateTime) {
                $value = [
                    min($value),
                    max($value),
                ];
            }
        }

        /*
         * The searched value must be converted maybe.... - Translation - Replace - DateTime - ...
         */
        foreach ($value as &$val) {
            $type = $this->getColumn()->getType();
            $val  = $type->getFilterValue($val);

            // @TODO Translation + Replace
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Is this a column filter.
     *
     * @return bool
     */
    public function isColumnFilter(): bool
    {
        return $this->getColumn() instanceof Column\AbstractColumn;
    }

    /**
     * Only needed for column filter.
     *
     * @return Column\AbstractColumn|null
     */
    public function getColumn(): ?Column\AbstractColumn
    {
        return $this->column;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * Get the value displayed to the user.
     *
     * @return string
     */
    public function getDisplayColumnValue(): string
    {
        return $this->displayColumnValue;
    }

    /**
     * Check if a value is the same (used for style, display actions).
     *
     * @param mixed  $currentValue
     *                              rowValue
     * @param mixed  $expectedValue
     *                              filterValue
     * @param string $operator
     *
     * @return bool
     */
    public static function isApply($currentValue, $expectedValue, string $operator = self::EQUAL): bool
    {
        list($currentValue, $expectedValue) = self::convertValues($currentValue, $expectedValue, $operator);

        switch ($operator) {
            case self::LIKE:
                if (stripos($currentValue, $expectedValue) !== false) {
                    return true;
                }
                break;

            case self::LIKE_LEFT:
                $length        = strlen($expectedValue);
                $start         = 0 - $length;
                $searchedValue = substr($currentValue, $start, $length);
                if (stripos($searchedValue, $expectedValue) !== false) {
                    return true;
                }
                break;

            case self::LIKE_RIGHT:
                $length        = strlen($expectedValue);
                $searchedValue = substr($currentValue, 0, $length);
                if (stripos($searchedValue, $expectedValue) !== false) {
                    return true;
                }
                break;

            case self::NOT_LIKE:
                if (stripos($currentValue, $expectedValue) === false) {
                    return true;
                }
                break;

            case self::NOT_LIKE_LEFT:
                $length        = strlen($expectedValue);
                $start         = 0 - $length;
                $searchedValue = substr($currentValue, $start, $length);
                if (stripos($searchedValue, $expectedValue) === false) {
                    return true;
                }
                break;

            case self::NOT_LIKE_RIGHT:
                $length        = strlen($expectedValue);
                $searchedValue = substr($currentValue, 0, $length);
                if (stripos($searchedValue, $expectedValue) === false) {
                    return true;
                }
                break;

            case self::EQUAL:
            case self::IN:
                return $currentValue == $expectedValue;

            case self::NOT_EQUAL:
            case self::NOT_IN:
                return $currentValue != $expectedValue;

            case self::GREATER_EQUAL:
                return $currentValue >= $expectedValue;

            case self::GREATER:
                return $currentValue > $expectedValue;

            case self::LESS_EQUAL:
                return $currentValue <= $expectedValue;

            case self::LESS:
                return $currentValue < $expectedValue;

            case self::BETWEEN:
                if (is_array($expectedValue) && count($expectedValue) >= 2) {
                    if ($currentValue >= $expectedValue[0] && $currentValue <= $expectedValue[1]) {
                        return true;
                    }
                } else {
                    throw new InvalidArgumentException(sprintf('Between needs exactly an array of two expected values. Give: "%s"',print_r($expectedValue, true)));
                }
                break;

            default:
                throw new InvalidArgumentException('currently not implemented filter type: "' . $operator . '"');
        }

        return false;
    }

    /**
     * @param mixed $currentValue
     * @param mixed $expectedValue
     * @param string $operator
     *
     * @return string[]
     */
    private static function convertValues($currentValue, $expectedValue, string $operator = self::EQUAL): array
    {
        switch ($operator) {
            case self::LIKE:
            case self::LIKE_LEFT:
            case self::LIKE_RIGHT:
            case self::NOT_LIKE:
            case self::NOT_LIKE_LEFT:
            case self::NOT_LIKE_RIGHT:
                $currentValue  = (string) $currentValue;
                $expectedValue = (string) $expectedValue;
                break;
        }

        return [
            $currentValue,
            $expectedValue,
        ];
    }
}
