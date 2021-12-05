<?php

declare(strict_types=1);

/**
 * Output as an excel file.
 */

namespace ZfcDatagrid\Renderer\PHPExcel;

use DateTime;
use DateTimeZone;
use Exception;
use Laminas\Http\Headers;
use Laminas\Http\Response\Stream as ResponseStream;
use Laminas\View\Model\ViewModel;
use PhpOffice\PhpSpreadsheet\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer;
use ZfcDatagrid\Column;
use ZfcDatagrid\Renderer\AbstractExport;

use function array_merge;
use function date;
use function filesize;
use function fopen;
use function get_class;
use function implode;
use function is_array;
use function is_scalar;

use const PHP_EOL;

class Renderer extends AbstractExport
{
    public function getName(): string
    {
        return 'PHPExcel';
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
     * @return ResponseStream|ViewModel
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function execute()
    {
        $options       = $this->getOptions();
        $optionsExport = $options['settings']['export'];

        $optionsRenderer = $this->getOptionsRenderer();

        $phpExcel = new Spreadsheet();

        // Sheet 1
        $phpExcel->setActiveSheetIndex(0);
        $sheet = $phpExcel->getActiveSheet();
        $sheet->setTitle($this->translate($optionsRenderer['sheetName']));

        if (true === $optionsRenderer['displayTitle']) {
            $sheet->getCell('A' . $optionsRenderer['rowTitle'])->setValue($this->getTitle());
            $sheet->getStyle('A' . $optionsRenderer['rowTitle'])
                ->getFont()
                ->setSize(15);
        }

        /*
         * Print settings
         */
        $this->setPrinting($phpExcel);

        /*
         * Calculate column width
         */
        $this->calculateColumnWidth($sheet, $this->getColumnsToExport());

        /*
         * Header
         */
        $xColumn = 1;
        $yRow    = $optionsRenderer['startRowData'];
        foreach ($this->getColumnsToExport() as $col) {
            /** @var Column\AbstractColumn $column */
            $sheet->setCellValueByColumnAndRow($xColumn, $yRow, $this->translate($col->getLabel()));

            $sheet->getColumnDimension(Cell\Coordinate::stringFromColumnIndex($xColumn))->setWidth($col->getWidth());

            ++$xColumn;
        }

        /*
         * Data
         */
        $yRow = $optionsRenderer['startRowData'] + 1;
        foreach ($this->getData() as $row) {
            $xColumn = 1;
            foreach ($this->getColumnsToExport() as $col) {
                /** @var Column\AbstractColumn $col */

                $value = $row[$col->getUniqueId()];
                if (is_array($value)) {
                    $value = implode(PHP_EOL, $value);
                }

                /** @var Column\AbstractColumn $column */
                $currentColumn = Cell\Coordinate::stringFromColumnIndex($xColumn);
                $cell          = $sheet->getCell($currentColumn . $yRow);

                switch (get_class($col->getType())) {
                    case Column\Type\Number::class:
                        $cell->setValueExplicit($value, Cell\DataType::TYPE_NUMERIC);
                        break;

                    case Column\Type\DateTime::class:
                        /** @var Column\Type\DateTime $dateType */
                        $dateType = $col->getType();

                        if (! $value instanceof DateTime && is_scalar($value)) {
                            $value = DateTime::createFromFormat($dateType->getSourceDateTimeFormat(), $value);
                            if ($value instanceof DateTime) {
                                $value->setTimezone(new DateTimeZone($dateType->getSourceTimezone()));
                            }
                        }

                        if ($value instanceof DateTime) {
                            // only apply this if we have a date object (else leave it blank)
                            $value->setTimezone(new DateTimeZone($dateType->getOutputTimezone()));
                            $cell->setValue(Date::PHPToExcel($value));

                            if ($dateType->getOutputPattern()) {
                                $outputPattern = $dateType->getOutputPattern();
                            } else {
                                $outputPattern = Style\NumberFormat::FORMAT_DATE_DATETIME;
                            }

                            $cell->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode($outputPattern);
                        }
                        break;

                    default:
                        $cell->setValueExplicit($value, Cell\DataType::TYPE_STRING);
                        break;
                }

                $columnStyle = $sheet->getStyle($currentColumn . $yRow);
                $columnStyle->getAlignment()->setWrapText(true);

                /*
                 * Styles
                 */
                $styles = array_merge($this->getRowStyles(), $col->getStyles());
                foreach ($styles as $style) {
                    /** @var Column\Style\AbstractStyle $style */
                    if ($style->isApply($row) === true) {
                        switch (get_class($style)) {
                            case Column\Style\Bold::class:
                                $columnStyle->getFont()->setBold(true);
                                break;

                            case Column\Style\Italic::class:
                                $columnStyle->getFont()->setItalic(true);
                                break;

                            case Column\Style\Color::class:
                                $columnStyle->getFont()
                                    ->getColor()
                                    ->setRGB($style->getRgbHexString());
                                break;

                            case Column\Style\BackgroundColor::class:
                                $columnStyle->getFill()->applyFromArray([
                                    'type'  => Style\Fill::FILL_SOLID,
                                    'color' => [
                                        'rgb' => $style->getRgbHexString(),
                                    ],
                                ]);
                                break;

                            case Column\Style\Align::class:
                                switch ($style->getAlignment()) {
                                    case Column\Style\Align::RIGHT:
                                        $columnStyle->getAlignment()->setHorizontal(
                                            Style\Alignment::HORIZONTAL_RIGHT
                                        );
                                        break;
                                    case Column\Style\Align::LEFT:
                                        $columnStyle->getAlignment()->setHorizontal(
                                            Style\Alignment::HORIZONTAL_LEFT
                                        );
                                        break;
                                    case Column\Style\Align::CENTER:
                                        $columnStyle->getAlignment()->setHorizontal(
                                            Style\Alignment::HORIZONTAL_CENTER
                                        );
                                        break;
                                    case Column\Style\Align::JUSTIFY:
                                        $columnStyle->getAlignment()->setHorizontal(
                                            Style\Alignment::HORIZONTAL_JUSTIFY
                                        );
                                        break;
                                    default:
                                        //throw new \Exception(
                                        //'Not defined yet: "'.get_class($style->getAlignment()).'"'
                                        //);
                                        break;
                                }

                                break;

                            case Column\Style\Strikethrough::class:
                                $columnStyle->getFont()->setStrikethrough(true);
                                break;

                            case Column\Style\Html::class:
                                // @todo strip the html?
                                break;

                            default:
                                throw new Exception('Not defined yet: "' . get_class($style) . '"');
                        }
                    }
                }

                ++$xColumn;
            }

            ++$yRow;
        }

        /*
         * Autofilter, freezing, ...
         */
        $highest = $sheet->getHighestRowAndColumn();

        // Letzte Zeile merken

        // Autofilter + Freeze
        $sheet->setAutoFilter('A' . $optionsRenderer['startRowData'] . ':' . $highest['column'] . $highest['row']);
        $freezeRow = $optionsRenderer['startRowData'] + 1;
        $sheet->freezePane('A' . $freezeRow);

        // repeat the data header for each page!
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(
            $optionsRenderer['startRowData'],
            $optionsRenderer['startRowData']
        );

        // highlight header line
        $style = [
            'font'    => [
                'bold' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Style\Border::BORDER_MEDIUM,
                    'color'       => [
                        'argb' => Style\Color::COLOR_BLACK,
                    ],
                ],
            ],
            'fill'    => [
                'fillType'   => Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => Style\Color::COLOR_YELLOW,
                ],
            ],
        ];
        $range = 'A' . $optionsRenderer['startRowData'] . ':' . $highest['column'] . $optionsRenderer['startRowData'];
        $sheet->getStyle($range)->applyFromArray($style);

        // print borders
        $range = 'A' . $freezeRow . ':' . $highest['column'] . $highest['row'];
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Style\Border::BORDER_THIN,
                ],
            ],
        ]);

        /*
         * Save the file
         */
        $path         = $optionsExport['path'];
        $saveFilename = date('Y-m-d_H-i-s') . $this->getCacheId() . '.xlsx';

        $excelWriter = new Writer\Xlsx($phpExcel);
        $excelWriter->setPreCalculateFormulas(false);
        $excelWriter->save($path . '/' . $saveFilename);

        /*
         * Send the response stream
         */
        $response = new ResponseStream();
        $response->setStream(fopen($path . '/' . $saveFilename, 'r'));

        $headers = new Headers();
        $headers->addHeaders([
            'Content-Type'        => [
                'application/force-download',
                'application/octet-stream',
                'application/download',
            ],
            'Content-Length'      => filesize($path . '/' . $saveFilename),
            'Content-Disposition' => 'attachment;filename=' . $this->getFilename() . '.xlsx',
            'Cache-Control'       => 'must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => 'Thu, 1 Jan 1970 00:00:00 GMT',
        ]);

        $response->setHeaders($headers);

        return $response;
    }

    /**
     * Calculates the column width, based on the papersize and orientation.
     *
     * @param array               $columns
     */
    protected function calculateColumnWidth(Worksheet\Worksheet $sheet, array $columns)
    {
        // First make sure the columns width is 100 "percent"
        $this->calculateColumnWidthPercent($columns);

        // width is in mm
        $paperWidth = $this->getPaperWidth();

        // remove margins (they are in inches!)
        $paperWidth -= $sheet->getPageMargins()->getLeft() / 0.0393700787402;
        $paperWidth -= $sheet->getPageMargins()->getRight() / 0.0393700787402;

        $paperWidth /= 2;

        $factor = $paperWidth / 100;
        foreach ($columns as $column) {
            /** @var Column\AbstractColumn $column */
            $column->setWidth($column->getWidth() * $factor);
        }
    }

    /**
     * Set the printing options.
     */
    protected function setPrinting(Spreadsheet $phpExcel)
    {
        $optionsRenderer = $this->getOptionsRenderer();

        $phpExcel->getProperties()
            ->setCreator('https://github.com/zfc-datagrid/zfc-datagrid')
            ->setTitle($this->getTitle());

        /*
         * Printing setup
         */
        $papersize   = $optionsRenderer['papersize'];
        $orientation = $optionsRenderer['orientation'];
        foreach ($phpExcel->getAllSheets() as $sheet) {
            /** @var Worksheet\Worksheet $sheet */
            if ('landscape' == $orientation) {
                $sheet->getPageSetup()->setOrientation(Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
            } else {
                $sheet->getPageSetup()->setOrientation(Worksheet\PageSetup::ORIENTATION_PORTRAIT);
            }

            switch ($papersize) {
                case 'A5':
                    $sheet->getPageSetup()->setPaperSize(Worksheet\PageSetup::PAPERSIZE_A5);
                    break;

                case 'A4':
                    $sheet->getPageSetup()->setPaperSize(Worksheet\PageSetup::PAPERSIZE_A4);
                    break;

                case 'A3':
                    $sheet->getPageSetup()->setPaperSize(Worksheet\PageSetup::PAPERSIZE_A3);
                    break;

                case 'A2':
                    $sheet->getPageSetup()->setPaperSize(Worksheet\PageSetup::PAPERSIZE_A2_PAPER);
                    break;
            }

            // Margins
            $sheet->getPageMargins()->setTop(0.8);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet->getPageMargins()->setLeft(0.5);
            $sheet->getPageMargins()->setRight(0.5);

            $this->setHeaderFooter($sheet);
        }

        $phpExcel->setActiveSheetIndex(0);
    }

    protected function setHeaderFooter(Worksheet\Worksheet $sheet)
    {
        $textRight = $this->translate('Page') . ' &P / &N';

        $sheet->getHeaderFooter()->setOddHeader('&L&16&G ' . $this->translate($this->getTitle()));
        $sheet->getHeaderFooter()->setOddFooter('&R' . $textRight);
    }
}
