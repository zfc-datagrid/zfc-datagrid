<?php
namespace ZfcDatagrid\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;

class Image extends AbstractFormatter
{
    /** @var string[] */
    protected $validRenderers = [
        'jqGrid',
        'bootstrapTable',
        'printHtml',
    ];

    /** @var array */
    protected $attributes = [];

    /** @var string */
    protected $prefix = '';

    /** @var array */
    protected $linkAttributes = [];

    /**
     * @param string $name
     * @param string $value
     */
    public function setAttribute(string $name, string $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setLinkAttribute(string $name, string $value)
    {
        $this->linkAttributes[$name] = $value;
    }

    /**
     * @return array
     */
    public function getLinkAttributes(): array
    {
        return $this->linkAttributes;
    }

    /**
     * Get the prefix.
     *
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * Set the prefix of the image path and the prefix of the link.
     *
     * @param string $prefix
     */
    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param AbstractColumn $column
     * @return string
     */
    public function getFormattedValue(AbstractColumn $column): string
    {
        $row    = $this->getRowData();
        $value  = $row[$column->getUniqueId()];
        $prefix = $this->getPrefix();

        if ($value == '') {
            return '';
        }

        if (is_array($value)) {
            $thumb = $value[0];

            if (isset($value[1])) {
                $original = $value[1];
            } else {
                $original = $thumb;
            }
        } else {
            $thumb    = $value;
            $original = $value;
        }

        $linkAttributes = [];
        foreach ($this->getLinkAttributes() as $key => $value) {
            $linkAttributes[] = $key . '="' . $value . '"';
        }

        $attributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            $attributes[] = $key . '="' . $value . '"';
        }

        return sprintf(
            '<a href="%s%s" %s><img src="%s%s" %s/></a>',
            $prefix,
            $original,
            implode(' ', $linkAttributes),
            $prefix,
            $thumb,
            implode(' ', $attributes)
        );
    }
}
