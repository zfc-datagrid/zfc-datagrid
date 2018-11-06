<?php
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
     * @param string $name
     */
    public function setIconClass(string $name)
    {
        $this->iconClass = $name;
    }

    /**
     * @return string
     */
    public function getIconClass(): string
    {
        return $this->iconClass;
    }

    /**
     * @return bool
     */
    public function hasIconClass(): bool
    {
        return '' !== $this->getIconClass();
    }

    /**
     * Set the icon link (is used, if no icon class is provided).
     *
     * @param string $httpLink
     */
    public function setIconLink(string $httpLink)
    {
        $this->iconLink = $httpLink;
    }

    /**
     * Get the icon link.
     *
     * @return string
     */
    public function getIconLink(): string
    {
        return $this->iconLink;
    }

    /**
     * @return bool
     */
    public function hasIconLink(): bool
    {
        return '' !== $this->getIconLink();
    }

    /**
     * @return string
     */
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
