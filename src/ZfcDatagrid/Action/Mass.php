<?php

declare(strict_types=1);

namespace ZfcDatagrid\Action;

class Mass
{
    /** @var string */
    private $title = '';

    /** @var string */
    private $link = '';

    /** @var bool */
    private $confirm = false;

    public function __construct(string $title = '', string $link = '', bool $confirm = false)
    {
        $this->setTitle($title);
        $this->setLink($link);
        $this->setConfirm($confirm);
    }

    /**
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return $this
     */
    public function setConfirm(bool $mode = true): self
    {
        $this->confirm = $mode;

        return $this;
    }

    public function getConfirm(): bool
    {
        return $this->confirm;
    }
}
