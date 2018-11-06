<?php
namespace ZfcDatagrid\Column\Style;

use function is_array;
use function count;
use function dechex;
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
    public static $RED = [
        255,
        0,
        0,
    ];

    /**
     * @var array
     */
    public static $GREEN = [
        0,
        255,
        0,
    ];

    /**
     * @var array
     */
    public static $BLUE = [
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
            list($red, $green, $blue) = $redOrStaticOrArray;
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
     * @param int $red
     *                   integer 0-255
     * @param int $green
     *                   0-255
     * @param int $blue
     *                   0-255
     */
    public function setRgb(int $red, int $green, int $blue)
    {
        $this->red   = (int) $red;
        $this->green = (int) $green;
        $this->blue  = (int) $blue;
    }

    /**
     * @param int $red
     */
    public function setRed(int $red)
    {
        $this->red = $red;
    }

    /**
     * @return int
     */
    public function getRed(): int
    {
        return $this->red;
    }

    /**
     * @param int $green
     */
    public function setGreen(int $green)
    {
        $this->green = $green;
    }

    /**
     * @return int
     */
    public function getGreen(): int
    {
        return $this->green;
    }

    /**
     * @param int $blue
     */
    public function setBlue(int $blue)
    {
        $this->blue = $blue;
    }

    /**
     * @return int
     */
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
     *
     * @return string
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
