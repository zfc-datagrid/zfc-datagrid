<?php

declare(strict_types=1);

/**
 * Methods which can be used in (all) export renderer.
 */

namespace ZfcDatagrid\Renderer;

use Exception;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\AbstractColumn;

use function date;
use function floor;
use function get_class;
use function implode;
use function in_array;
use function preg_replace;
use function str_replace;
use function substr;

abstract class AbstractExport extends AbstractRenderer
{
    /** @var string[] */
    protected $allowedColumnTypes = [
        Column\Type\DateTime::class,
        Column\Type\Number::class,
        Column\Type\PhpArray::class,
        Column\Type\PhpString::class,
    ];

    /** @var Column\AbstractColumn[] */
    protected $columnsToExport = [];

    /**
     * Decide which columns we want to display.
     *
     * @return Column\AbstractColumn[]
     * @throws Exception
     */
    protected function getColumnsToExport(): array
    {
        if (! empty($this->columnsToExport)) {
            return $this->columnsToExport;
        }

        $columnsToExport = [];
        foreach ($this->getColumns() as $column) {
            /** @var AbstractColumn $column */

            if (
                ! $column instanceof Column\Action &&
                $column->isHidden() === false &&
                in_array(get_class($column->getType()), $this->allowedColumnTypes)
            ) {
                $columnsToExport[] = $column;
            }
        }
        if (empty($columnsToExport)) {
            throw new Exception('No columns to export available');
        }

        $this->columnsToExport = $columnsToExport;

        return $this->columnsToExport;
    }

    /**
     * Get the paper width in MM (milimeter).
     *
     * @throws Exception
     */
    protected function getPaperWidth(): float
    {
        $optionsRenderer = $this->getOptionsRenderer();

        $papersize   = $optionsRenderer['papersize'];
        $orientation = $optionsRenderer['orientation'];

        if (substr($papersize, 0, 1) != 'A') {
            throw new Exception('Currently only "A" paper formats are supported!');
        }

        // calc from A0 to selected
        $divisor = substr($papersize, 1, 1);

        // A0 dimensions = 841 x 1189 mm
        $currentX = 841;
        $currentY = 1189;
        for ($i = 0; $i < $divisor; ++$i) {
            $tempY = $currentX;
            $tempX = floor($currentY / 2);

            $currentX = $tempX;
            $currentY = $tempY;
        }

        return 'landscape' === $orientation ? $currentY : $currentX;
    }

    /**
     * Get a valid filename to save
     * (WITHOUT the extension!).
     */
    protected function getFilename(): string
    {
        $filenameParts   = [];
        $filenameParts[] = date('Y-m-d_H-i-s');

        if ($this->getTitle() != '') {
            $title = $this->getTitle();
            $title = str_replace(' ', '_', $title);

            $filenameParts[] = preg_replace('/[^a-z0-9_-]+/i', '', $title);
        }

        return implode('_', $filenameParts);
    }
}
