<?php
namespace ZfcDatagrid\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;

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
     */
    public function setRowData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getRowData(): array
    {
        return $this->data;
    }

    /**
     * @param string $name
     */
    public function setRendererName(?string $name = null)
    {
        $this->rendererName = $name;
    }

    /**
     * @return string null
     */
    public function getRendererName(): ?string
    {
        return $this->rendererName;
    }

    /**
     * @param array $validRendrerers
     */
    public function setValidRendererNames(array $validRendrerers)
    {
        $this->validRenderers = $validRendrerers;
    }

    /**
     * @return array
     */
    public function getValidRendererNames(): array
    {
        return $this->validRenderers;
    }

    /**
     * @return bool
     */
    public function isApply(): bool
    {
        return in_array($this->getRendererName(), $this->validRenderers);
    }

    /**
     * @param AbstractColumn $column
     *
     * @return string
     */
    public function format(AbstractColumn $column): string
    {
        $data = $this->getRowData();
        if ($this->isApply() === true) {
            return $this->getFormattedValue($column);
        }

        return $data[$column->getUniqueId()];
    }

    /**
     * @param AbstractColumn $column
     *
     * @return string
     */
    abstract public function getFormattedValue(AbstractColumn $column): string;
}
