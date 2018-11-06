<?php
namespace ZfcDatagrid\Column\Type;

use function is_array;
use function explode;

class PhpArray extends AbstractType
{
    /**
     * Separator of the string to be used to explode the array.
     *
     * @var string
     */
    protected $separator = '';

    /**
     * @param string $separator
     */
    public function __construct(string $separator = ',')
    {
        $this->setSeparator($separator);
    }

    /**
     * Set separator of the string to be used to explode the array.
     *
     * @param string $separator
     */
    public function setSeparator(string $separator)
    {
        $this->separator = $separator;
    }

    /*
     * Get the string separator
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'array';
    }

    /**
     * Convert a value into an array.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getUserValue($value)
    {
        if (! is_array($value)) {
            if ('' == $value) {
                $value = [];
            } else {
                $value = explode($this->getSeparator(), $value);
            }
        }

        return $value;
    }
}
