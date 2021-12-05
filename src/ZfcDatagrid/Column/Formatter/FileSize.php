<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Formatter;

use ZfcDatagrid\Column\AbstractColumn;

use function count;
use function sprintf;

class FileSize extends AbstractFormatter
{
    /**
     * We implement isApply here ourself, because it's always valid!
     *
     * @var array
     */
    protected $validRenderers = [];

    /** @var string[] */
    const PREFIXES = [
        '',
        'K',
        'M',
        'G',
        'T',
        'P',
        'E',
        'Z',
        'Y',
    ];

    public function isApply(): bool
    {
        return true;
    }

    /**
     * The value should be in bytes.
     *
     * @see \ZfcDatagrid\Column\Formatter\AbstractFormatter::getFormattedValue()
     */
    public function getFormattedValue(AbstractColumn $column): string
    {
        $row   = $this->getRowData();
        $value = (string) $row[$column->getUniqueId()];

        if ('' == $value) {
            return $value;
        }

        $index = 0;
        while ($value >= 1024 && $index < count(self::PREFIXES)) {
            $value /= 1024;
            ++$index;
        }

        return sprintf('%1.2f %sB', $value, self::PREFIXES[$index]);
    }
}
