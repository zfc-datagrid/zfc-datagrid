<?php

namespace ZfcDatagrid\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;

/**
 * Class Image
 *
 * @package ZfcDatagrid\Column\Formatter
 */
class Image extends AbstractFormatter
{
    /**
     * @var array
     */
    protected $validRenderers = [
        'jqGrid',
        'bootstrapTable',
        'printHtml',
    ];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var
     */
    protected $prefix;

    /**
     * @var array
     */
    protected $linkAttributes = [];

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setLinkAttribute($name, $value)
    {
        $this->linkAttributes[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getLinkAttributes()
    {
        return $this->linkAttributes;
    }

    /**
     * Get the prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the prefix of the image path and the prefix of the link.
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @param \ZfcDatagrid\Column\AbstractColumn $column
     *
     * @return string
     */
    public function getFormattedValue(AbstractColumn $column)
    {
        $row = $this->getRowData();
        $value = $row[$column->getUniqueId()];
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
            $thumb = $value;
            $original = $value;
        }

        $linkAttributes = [];
        foreach ($this->getLinkAttributes() as $key => $value) {
            $linkAttributes[] = $key.'="'.$value.'"';
        }

        $attributes = [];
        foreach ($this->getAttributes() as $key => $value) {
            $attributes[] = $key.'="'.$value.'"';
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
