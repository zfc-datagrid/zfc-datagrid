<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;

use function in_array;

abstract class AbstractFormatter
{
    /** @var array */
    private $data = [];

    /** @var string */
    private $rendererName;

    /** @var array */
    protected $validRenderers = [];

    /**
     * @param array $data
     * @return $this
     */
    public function setRowData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getRowData(): array
    {
        return $this->data;
    }

    /**
     * @return $this
     */
    public function setRendererName(?string $name = null): self
    {
        $this->rendererName = $name;

        return $this;
    }

    /**
     * @return string null
     */
    public function getRendererName(): ?string
    {
        return $this->rendererName;
    }

    /**
     * @param array $validRenderers
     * @return $this
     */
    public function setValidRendererNames(array $validRenderers): self
    {
        $this->validRenderers = $validRenderers;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidRendererNames(): array
    {
        return $this->validRenderers;
    }

    public function isApply(): bool
    {
        return in_array($this->getRendererName(), $this->validRenderers);
    }

    public function format(AbstractColumn $column): string
    {
        $data = $this->getRowData();

        return true === $this->isApply() ? $this->getFormattedValue($column) : $data[$column->getUniqueId()];
    }

    abstract public function getFormattedValue(AbstractColumn $column): string;
}
