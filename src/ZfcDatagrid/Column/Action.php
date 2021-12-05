<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column;

/**
 * Action Column
 * IMPORTANT: Will only be shown on HTML renderer.
 *
 * So Attributes for HTML are valid...
 */
class Action extends AbstractColumn
{
    /** @var Action\AbstractAction[] */
    private $actions = [];

    public function __construct(string $uniqueId = 'action')
    {
        $this->setUniqueId($uniqueId);
        $this->setLabel('Actions');

        $this->setUserSortDisabled(true);
        $this->setUserFilterDisabled(true);

        $this->setRowClickDisabled(true);
    }

    /**
     * @return $this
     */
    public function addAction(Action\AbstractAction $action): self
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * @return Action\AbstractAction[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param array|Action\AbstractAction[] $actions
     * @return $this
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * @param int $key
     */
    public function getAction($key): ?Action\AbstractAction
    {
        return $this->actions[$key] ?? null;
    }

    /**
     * @param int $key
     * @return $this
     */
    public function removeAction($key = null): self
    {
        unset($this->actions[$key]);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearActions(): self
    {
        $this->actions = [];

        return $this;
    }
}
