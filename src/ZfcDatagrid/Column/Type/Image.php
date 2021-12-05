<?php

declare(strict_types=1);

/**
 * Image type.
 */

namespace ZfcDatagrid\Column\Type;

use InvalidArgumentException;

class Image extends AbstractType
{
    /** @var string */
    protected $resizeType = 'fixed';

    /** @var float */
    protected $resizeHeight = 20.5;

    public function getTypeName(): string
    {
        return 'image';
    }

    /**
     * Set the resize type for TCPDF export.
     *
     * @throws InvalidArgumentException
     * @return $this
     */
    public function setResizeType(string $type): self
    {
        if ($type != 'fixed' && $type != 'dynamic') {
            throw new InvalidArgumentException('Only dynamic or fixed is allowed as Type');
        }

        $this->resizeType = $type;

        return $this;
    }

    public function getResizeType(): string
    {
        return $this->resizeType;
    }

    /**
     * @return $this
     */
    public function setResizeHeight(float $height): self
    {
        $this->resizeHeight = $height;

        return $this;
    }

    public function getResizeHeight(): float
    {
        return $this->resizeHeight;
    }
}
