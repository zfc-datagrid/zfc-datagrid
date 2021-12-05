<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Type;

use function explode;
use function is_array;

class PhpArray extends AbstractType
{
    /**
     * Separator of the string to be used to explode the array.
     *
     * @var string
     */
    protected $separator = '';

    public function __construct(string $separator = ',')
    {
        $this->setSeparator($separator);
    }

    /**
     * Set separator of the string to be used to explode the array.
     *
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
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

    public function getTypeName(): string
    {
        return 'array';
    }

    /**
     * Convert a value into an array.
     *
     * @param mixed $value
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
