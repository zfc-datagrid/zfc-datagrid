<?php
namespace ZfcDatagrid\Action;

class Mass
{
    /** @var string */
    private $title = '';

    /** @var string */
    private $link = '';

    /** @var bool */
    private $confirm = false;

    /**
     * @param string $title
     * @param string $link
     * @param bool   $confirm
     */
    public function __construct(string $title = '', string $link = '', bool $confirm = false)
    {
        $this->setTitle($title);
        $this->setLink($link);
        $this->setConfirm($confirm);
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param bool $mode
     *
     * @return $this
     */
    public function setConfirm(bool $mode = true): self
    {
        $this->confirm = $mode;

        return $this;
    }

    /**
     * @return bool
     */
    public function getConfirm(): bool
    {
        return $this->confirm;
    }
}
