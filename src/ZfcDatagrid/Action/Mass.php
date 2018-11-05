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
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
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
     */
    public function setLink(string $link)
    {
        $this->link = $link;
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
     */
    public function setConfirm(bool $mode = true)
    {
        $this->confirm = $mode;
    }

    /**
     * @return bool
     */
    public function getConfirm(): bool
    {
        return $this->confirm;
    }
}
