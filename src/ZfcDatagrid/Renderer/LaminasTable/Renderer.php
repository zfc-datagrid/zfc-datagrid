<?php
namespace ZfcDatagrid\Renderer\LaminasTable;

use Laminas\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Laminas\Console\Console;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Text\Table;
use Laminas\Text\Table\Table as TextTable;
use ZfcDatagrid\Column;
use ZfcDataGrid\Column\Type;
use ZfcDatagrid\Renderer\AbstractRenderer;
use function explode;
use function strtoupper;
use function count;
use function function_exists;
use function mb_strtoupper;
use function is_array;
use function implode;
use function sprintf;
use function floor;
use function array_sum;

/**
 * For CLI.
 */
class Renderer extends AbstractRenderer
{
    /** @var ConsoleAdapter */
    private $consoleAdapter;

    /** @var Column\AbstractColumn[] */
    private $columnsToDisplay = [];

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'laminasTable';
    }

    /**
     * @return bool
     */
    public function isExport(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isHtml(): bool
    {
        return false;
    }

    /**
     * @return ConsoleRequest
     *
     * @throws \Exception
     */
    public function getRequest(): ConsoleRequest
    {
        $request = parent::getRequest();
        if (! $request instanceof ConsoleRequest) {
            throw new \Exception('Request must be an instance of Laminas\Console\Request for console rendering');
        }

        return $request;
    }

    /**
     * @param ConsoleAdapter $adapter
     *
     * @return $this
     */
    public function setConsoleAdapter(ConsoleAdapter $adapter): self
    {
        $this->consoleAdapter = $adapter;

        return $this;
    }

    /**
     * @return ConsoleAdapter
     */
    public function getConsoleAdapter(): ConsoleAdapter
    {
        if (null === $this->consoleAdapter) {
            $this->consoleAdapter = Console::getInstance();
        }

        return $this->consoleAdapter;
    }

    /**
     * @todo enable parameters from console
     *
     * @return array
     */
    public function getSortConditions(): array
    {
        if (!empty($this->sortConditions)) {
            return $this->sortConditions;
        }

        $request = $this->getRequest();

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $sortConditions = [];

        $sortColumns    = $request->getParam($parameterNames['sortColumns']);
        $sortDirections = $request->getParam($parameterNames['sortDirections']);
        if ($sortColumns != '') {
            $sortColumns    = explode(',', $sortColumns);
            $sortDirections = explode(',', $sortDirections);

            foreach ($sortColumns as $key => $sortColumn) {
                if (isset($sortDirections[$key])) {
                    $sortDirection = strtoupper($sortDirections[$key]);
                } else {
                    $sortDirection = 'ASC';
                }

                if ($sortDirection != 'ASC' && $sortDirection != 'DESC') {
                    $sortDirection = 'ASC';
                }

                foreach ($this->getColumns() as $column) {
                    /* @var $column \ZfcDatagrid\Column\AbstractColumn */
                    if ($column->getUniqueId() == $sortColumn) {
                        $sortConditions[] = [
                            'sortDirection' => $sortDirection,
                            'column'        => $column,
                        ];

                        $column->setSortActive($sortDirection);
                    }
                }
            }
        }

        if (! empty($sortConditions)) {
            $this->sortConditions = $sortConditions;
        } else {
            // No user sorting -> get default sorting
            $this->sortConditions = $this->getSortConditionsDefault();
        }

        return $this->sortConditions;
    }

    /**
     * @todo enable parameters from console
     *
     * @return array
     */
    public function getFilters(): array
    {
        return [];
    }

    /**
     * Should be implemented for each renderer itself (just default).
     *
     * @return int
     */
    public function getCurrentPageNumber(): int
    {
        $request = $this->getRequest();

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];
        if ($request->getParam($parameterNames['currentPage']) != '') {
            return (int) $request->getParam($parameterNames['currentPage']);
        }

        return (int) 1;
    }

    /**
     * @param int $defaultItems
     *
     * @return int
     *
     * @throws \Exception
     */
    public function getItemsPerPage($defaultItems = 25): int
    {
        $request = $this->getRequest();

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];
        if ($request->getParam($parameterNames['itemsPerPage']) != '') {
            return (int) $request->getParam($parameterNames['itemsPerPage']);
        }

        return (int) $defaultItems;
    }

    /**
     * @return \Laminas\Stdlib\ResponseInterface
     */
    public function execute()
    {
        $textTable = clone $this->getTable();

        $response = $this->getMvcEvent()->getResponse();
        $response->setContent($textTable);

        return $response;
    }

    /**
     * @return TextTable
     */
    private function getTable(): TextTable
    {
        $paginator = $this->getPaginator();

        $options = [
            'columnWidths' => $this->getColumnWidths(),
        ];

        $table = new TextTable($options);
        $table->setDecorator('ascii');

        /*
         * Title
         */
        $tableRow = new Table\Row();

        $tableColumn = new Table\Column($this->getTitle());
        $tableColumn->setColSpan(count($options['columnWidths']));
        $tableColumn->setAlign(Table\Column::ALIGN_CENTER);
        $tableRow->appendColumn($tableColumn);

        $table->appendRow($tableRow);

        /**
         * Header.
         */
        $tableRow = new Table\Row();
        foreach ($this->getColumnsToDisplay() as $column) {
            $label = $this->translate($column->getLabel());
            $label = function_exists('mb_strtoupper') ? mb_strtoupper($label) : strtoupper($label);

            $tableColumn = new Table\Column($label);
            $tableColumn->setAlign(
                $column->getType() instanceof Type\Number ? Table\Column::ALIGN_RIGHT : Table\Column::ALIGN_LEFT
            );
            $tableRow->appendColumn($tableColumn);
        }
        $table->appendRow($tableRow);

        /*
         * Data
         */
        foreach ($this->getData() as $row) {
            $tableRow = new Table\Row();

            foreach ($this->getColumnsToDisplay() as $column) {
                $value = '';
                if (isset($row[$column->getUniqueId()])) {
                    $value = $row[$column->getUniqueId()];
                }
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $tableColumn = new Table\Column($value);
                $tableColumn->setAlign(
                    $column->getType() instanceof Type\Number ? Table\Column::ALIGN_RIGHT : Table\Column::ALIGN_LEFT
                );
                $tableRow->appendColumn($tableColumn);
            }

            $table->appendRow($tableRow);
        }

        /*
         * Pagination
         */
        $tableRow = new Table\Row();

        $footer = $this->translate('Page') . ' ';
        $footer .= sprintf('%s %s %s', $paginator->getCurrentPageNumber(), $this->translate('of'), $paginator->count());

        $footer .= ' / ';

        $footer .= sprintf(
            '%s %s %s %s %s',
            $this->translate('Showing'),
            $paginator->getCurrentItemCount(),
            $this->translate('of'),
            $paginator->getTotalItemCount(),
            $this->translate('items')
        );

        $tableColumn = new Table\Column($footer);
        $tableColumn->setColSpan(count($options['columnWidths']));
        $tableColumn->setAlign(Table\Column::ALIGN_CENTER);
        $tableRow->appendColumn($tableColumn);

        $table->appendRow($tableRow);

        return $table;
    }

    /**
     * Decide which columns we want to display.
     *
     * @return Column\AbstractColumn[]
     *
     * @throws \Exception
     */
    private function getColumnsToDisplay(): array
    {
        if (!empty($this->columnsToDisplay)) {
            return $this->columnsToDisplay;
        }

        $columnsToDisplay = [];
        foreach ($this->getColumns() as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */

            if (! $column instanceof Column\Action && $column->isHidden() === false) {
                $columnsToDisplay[] = $column;
            }
        }
        if (empty($columnsToDisplay)) {
            throw new \Exception('No columns to display available');
        }

        $this->columnsToDisplay = $columnsToDisplay;

        return $this->columnsToDisplay;
    }

    /**
     * @return array
     */
    private function getColumnWidths(): array
    {
        $cols = $this->getColumnsToDisplay();

        $this->calculateColumnWidthPercent($cols);

        $border = count($cols) + 1;

        $widthAvailable = $this->getConsoleAdapter()->getWidth() - $border;
        $onePercent     = $widthAvailable / 100;

        $colWidths = [];
        foreach ($cols as $col) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            $width = $col->getWidth() * $onePercent;
            $width = (int) floor($width);

            $colWidths[] = $width;
        }

        $i = 0;
        while (array_sum($colWidths) < $widthAvailable) {
            $colWidths[$i] = $colWidths[$i] + 1;

            ++$i;
        }

        return $colWidths;
    }
}
