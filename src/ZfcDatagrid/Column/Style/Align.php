<?php
namespace ZfcDatagrid\Column\Style;

class Align extends AbstractStyle
{
    /**
     * @var string
     */
    const LEFT = 'left';

    /**
     * @var string
     */
    const RIGHT = 'right';

    /**
     * @var string
     */
    const CENTER = 'center';

    /**
     * @var string
     */
    const JUSTIFY = 'justify';

    /**
     * @var string
     */
    protected $alignment;

    /**
     * @param string|null $alignment
     */
    public function __construct($alignment = null)
    {
        if (null === $alignment) {
            $alignment = self::LEFT;
        }

        $this->setAlignment($alignment);
    }

    /**
     * @param string $alignment
     *
     * @return $this
     */
    public function setAlignment(string $alignment): self
    {
        $this->alignment = $alignment;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlignment(): string
    {
        return $this->alignment;
    }
}
