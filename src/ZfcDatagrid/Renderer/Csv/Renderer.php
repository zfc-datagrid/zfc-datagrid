<?php

declare(strict_types=1);

/**
 * Render datagrid as CSV.
 */

namespace ZfcDatagrid\Renderer\Csv;

use Laminas\Http\Headers;
use Laminas\Http\Response\Stream as ResponseStream;
use Laminas\View\Model\ViewModel;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Renderer\AbstractExport;

use function chr;
use function date;
use function fclose;
use function filesize;
use function fopen;
use function fprintf;
use function fputcsv;
use function implode;

class Renderer extends AbstractExport
{
    public function getName(): string
    {
        return 'csv';
    }

    public function isExport(): bool
    {
        return true;
    }

    public function isHtml(): bool
    {
        return false;
    }

    /**
     * @return ViewModel
     */
    public function execute()
    {
        $optionsRenderer = $this->getOptionsRenderer();

        $delimiter = ',';
        if (isset($optionsRenderer['delimiter'])) {
            $delimiter = $optionsRenderer['delimiter'];
        }
        $enclosure = '"';
        if (isset($optionsRenderer['enclosure'])) {
            $enclosure = $optionsRenderer['enclosure'];
        }

        $options       = $this->getOptions();
        $optionsExport = $options['settings']['export'];

        $path         = $optionsExport['path'];
        $saveFilename = date('Y-m-d_H-i-s') . $this->getCacheId() . '.csv';

        $fp = fopen($path . '/' . $saveFilename, 'w');
        // Force UTF-8 for CSV rendering in EXCEL.
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        /*
         * Save the file
         */
        // header
        if (isset($optionsRenderer['header']) && true === $optionsRenderer['header']) {
            $header = [];
            foreach ($this->getColumnsToExport() as $col) {
                $header[] = $this->translate($col->getLabel());
            }
            fputcsv($fp, $header, $delimiter, $enclosure);
        }

        // data
        foreach ($this->getData() as $row) {
            $csvRow = [];
            foreach ($this->getColumnsToExport() as $col) {
                $value = $row[$col->getUniqueId()];

                if ($col->getType() instanceof Type\PhpArray || $col->getType() instanceof Type\Image) {
                    $value = implode(',', $value);
                }

                $csvRow[] = $value;
            }
            fputcsv($fp, $csvRow, $delimiter, $enclosure);
        }
        fclose($fp);

        /*
         * Return the file
         */
        $response = new ResponseStream();
        $response->setStream(fopen($path . '/' . $saveFilename, 'r'));

        $headers = new Headers();
        $headers->addHeaders([
            'Content-Type'        => [
                'application/force-download',
                'application/octet-stream',
                'application/download',
                'text/csv; charset=utf-8',
            ],
            'Content-Length'      => filesize($path . '/' . $saveFilename),
            'Content-Disposition' => 'attachment;filename=' . $this->getFilename() . '.csv',
            'Cache-Control'       => 'must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => 'Thu, 1 Jan 1970 00:00:00 GMT',
        ]);

        $response->setHeaders($headers);

        return $response;
    }
}
