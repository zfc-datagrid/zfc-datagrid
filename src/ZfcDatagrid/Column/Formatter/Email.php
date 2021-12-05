<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;

class Email extends AbstractFormatter
{
    /** @var array */
    protected $validRenderers = [
        'jqGrid',
        'bootstrapTable',
    ];

    public function getFormattedValue(AbstractColumn $column): string
    {
        $row = $this->getRowData();

        return '<a href="mailto:' . $row[$column->getUniqueId()] . '">' . $row[$column->getUniqueId()] . '</a>';
    }
}
