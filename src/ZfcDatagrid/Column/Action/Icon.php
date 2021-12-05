<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Action;

use InvalidArgumentException;

class Icon extends AbstractAction
{
    /** @var string */
    protected $iconClass = '';

    /** @var string */
    protected $iconLink = '';

    /**
     * Set the icon class (CSS)
     * - used for HTML if provided, overwise the iconLink is used.
     *
     * @return $this
     */
    public function setIconClass(string $name): self
    {
        $this->iconClass = $name;

        return $this;
    }

    public function getIconClass(): string
    {
        return $this->iconClass;
    }

    public function hasIconClass(): bool
    {
        return '' !== $this->getIconClass();
    }

    /**
     * Set the icon link (is used, if no icon class is provided).
     *
     * @return $this
     */
    public function setIconLink(string $httpLink): self
    {
        $this->iconLink = $httpLink;

        return $this;
    }

    /**
     * Get the icon link.
     */
    public function getIconLink(): string
    {
        return $this->iconLink;
    }

    public function hasIconLink(): bool
    {
        return '' !== $this->getIconLink();
    }

    protected function getHtmlType(): string
    {
        if (true === $this->hasIconClass()) {
            // a css class is provided, so use it
            return '<i class="' . $this->getIconClass() . '"></i>';
        } elseif (true === $this->hasIconLink()) {
            // no css class -> use the icon link instead
            return '<img src="' . $this->getIconLink() . '" />';
        }

        throw new InvalidArgumentException('Either a link or a class for the icon is required');
    }
}
