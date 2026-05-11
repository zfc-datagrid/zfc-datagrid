<?php
namespace ZfcDatagrid\Renderer\BootstrapTable;

use Laminas\Diactoros\Response\HtmlResponse;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Renderer\AbstractRenderer;
use function explode;
use function count;
use function strtoupper;

class Renderer extends AbstractRenderer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'bootstrapTable';
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
        return true;
    }

    /**
     * @see \ZfcDatagrid\Renderer\AbstractRenderer::getSortConditions()
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getSortConditions(): array
    {
        if (!empty($this->sortConditions)) {
            // set from cache! (for export)
            return $this->sortConditions;
        }

        $request = $this->getRequest();

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $sortConditions = [];
        $sortColumns = $parsedBody[$parameterNames['sortColumns']] ?? ($queryParams[$parameterNames['sortColumns']] ?? null);
        $sortDirections = $parsedBody[$parameterNames['sortDirections']] ?? ($queryParams[$parameterNames['sortDirections']] ?? null);

        if ($sortColumns != '') {
            $sortColumns    = explode(',', $sortColumns);
            $sortDirections = explode(',', $sortDirections);

            if (count($sortColumns) !== count($sortDirections)) {
                throw new \Exception('Count missmatch order columns/direction');
            }

            foreach ($sortColumns as $key => $sortColumn) {
                $sortDirection = strtoupper($sortDirections[$key]);

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
     * @todo Make parameter config
     *
     * @see \ZfcDatagrid\Renderer\AbstractRenderer::getFilters()
     */
    public function getFilters(): array
    {
        if (!empty($this->filters)) {
            return $this->filters;
        }

        $request = $this->getRequest();

        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $toolbarFilters = $parsedBody[$parameterNames['toolbarFilters']] ?? ($queryParams[$parameterNames['toolbarFilters']] ?? null);

        $filters = [];
        if ((in_array(strtoupper($request->getMethod()), ['POST', 'GET'])) &&
            null !== $toolbarFilters
        ) {
            foreach ($toolbarFilters as $uniqueId => $value) {
                if ($value != '') {
                    foreach ($this->getColumns() as $column) {
                        if ($column->getUniqueId() == $uniqueId) {
                            $filter = new \ZfcDatagrid\Filter();
                            $filter->setFromColumn($column, $value);

                            $filters[] = $filter;

                            $column->setFilterActive($filter->getDisplayColumnValue());
                        }
                    }
                }
            }
        }

        if (! empty($filters)) {
            $this->filters = $filters;
        } else {
            // No user sorting -> get default sorting
            $this->filters = $this->getFiltersDefault();
        }

        return $this->filters;
    }

    /**
     * @return int
     *
     * @throws \Exception
     */
    public function getCurrentPageNumber(): int
    {
        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $request = $this->getRequest();

        $parsedBody = $request->getParsedBody();
        $queryParams = $request->getQueryParams();

        $this->currentPageNumber = (int)($parsedBody[$parameterNames['currentPage']] ?? ($queryParams[$parameterNames['currentPage']] ?? 1));

        return (int) $this->currentPageNumber;
    }

    /**
     * @param Datagrid $grid
     */
    public function prepareViewModel(Datagrid $grid): array
    {
        $data = parent::prepareViewModel($grid);

        $options = $this->getOptionsRenderer();

        // Check if the datarange picker is enabled
        if (isset($options['daterange']['enabled']) && $options['daterange']['enabled'] === true) {
            $dateRangeParameters = $options['daterange']['options'];

            $data['daterangeEnabled'] = true;
            $data['daterangeParameters'] = $dateRangeParameters;
        } else {
            $data['daterangeEnabled'] = false;
        }

        return $data;
    }

    public function execute(array $data): mixed
    {
        return new HtmlResponse($this->templateRenderer->render($this->getTemplate(), $data));
    }
}
