<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Style;

use function count;
use function dechex;
use function is_array;
use function strlen;

/**
 * Abstract color class for font and backgroundColor.
 */
class AbstractColor extends AbstractStyle
{
    /**
     * Array values are RGB (red, green,blue).
     *
     * @var array
     */
    const RED = [
        255,
        0,
        0,
    ];

    /**
     * @var array
     */
    const GREEN = [
        0,
        255,
        0,
    ];

    /**
     * @var array
     */
    const BLUE = [
        0,
        0,
        255,
    ];

    /** @var int */
    protected $red = 0;

    /** @var int */
    protected $green = 0;

    /** @var int */
    protected $blue = 0;

    /**
     * Set red green blue.
     *
     * @param mixed $redOrStaticOrArray
     *                                  0-255
     * @param int   $green
     *                                  0-255
     * @param int   $blue
     *                                  0-255
     */
    public function __construct($redOrStaticOrArray, int $green = 0, int $blue = 0)
    {
        if (is_array($redOrStaticOrArray) && count($redOrStaticOrArray) === 3) {
            [$red, $green, $blue] = $redOrStaticOrArray;
        } else {
            $red = $redOrStaticOrArray;
        }

        $this->red   = (int) $red;
        $this->green = (int) $green;
        $this->blue  = (int) $blue;
    }

    /**
     * Set the RGB.
     *
     * @param int $red 0-255
     * @param int $green 0-255
     * @param int $blue 0-255
     * @return $this
     */
    public function setRgb(int $red, int $green, int $blue): self
    {
        $this->red   = (int) $red;
        $this->green = (int) $green;
        $this->blue  = (int) $blue;

        return $this;
    }

    /**
     * @return $this
     */
    public function setRed(int $red): self
    {
        $this->red = $red;

        return $this;
    }

    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * @return $this
     */
    public function setGreen(int $green): self
    {
        $this->green = $green;

        return $this;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * @return $this
     */
    public function setBlue(int $blue): self
    {
        $this->blue = $blue;

        return $this;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }

    /**
     * @return array
     */
    public function getRgbArray(): array
    {
        return [
            'red'   => $this->getRed(),
            'green' => $this->getGreen(),
            'blue'  => $this->getBlue(),
        ];
    }

    /**
     * Convert RGB dec to hex as a string.
     */
    public function getRgbHexString(): string
    {
        $red = dechex($this->getRed());
        if (strlen($red) === 1) {
            $red = '0' . $red;
        }
        $green = dechex($this->getGreen());
        if (strlen($green) === 1) {
            $green = '0' . $green;
        }
        $blue = dechex($this->getBlue());
        if (strlen($blue) === 1) {
            $blue = '0' . $blue;
        }

        return $red . $green . $blue;
    }
}
