<?php

declare(strict_types=1);

namespace ZfcDatagrid\Renderer\BootstrapTable;

use Exception;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\View\Model\ViewModel;
use ZfcDatagrid\Column\AbstractColumn;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Filter;
use ZfcDatagrid\Renderer\AbstractRenderer;

use function count;
use function explode;
use function strtoupper;

class Renderer extends AbstractRenderer
{
    public function getName(): string
    {
        return 'bootstrapTable';
    }

    public function isExport(): bool
    {
        return false;
    }

    public function isHtml(): bool
    {
        return true;
    }

    /**
     * @throws Exception
     */
    public function getRequest(): HttpRequest
    {
        $request = parent::getRequest();
        if (! $request instanceof HttpRequest) {
            throw new Exception(
                'Request must be an instance of Laminas\Http\PhpEnvironment\Request for HTML rendering'
            );
        }

        return $request;
    }

    /**
     * @see \ZfcDatagrid\Renderer\AbstractRenderer::getSortConditions()
     *
     * @return array
     *
     * @throws Exception
     */
    public function getSortConditions(): array
    {
        if (! empty($this->sortConditions)) {
            // set from cache! (for export)
            return $this->sortConditions;
        }

        $request = $this->getRequest();

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $sortConditions = [];
        $sortColumns    = $request->getPost(
            $parameterNames['sortColumns'],
            $request->getQuery($parameterNames['sortColumns'])
        );
        $sortDirections = $request->getPost(
            $parameterNames['sortDirections'],
            $request->getQuery($parameterNames['sortDirections'])
        );
        if ($sortColumns != '') {
            $sortColumns    = explode(',', $sortColumns);
            $sortDirections = explode(',', $sortDirections);

            if (count($sortColumns) !== count($sortDirections)) {
                throw new Exception('Count missmatch order columns/direction');
            }

            foreach ($sortColumns as $key => $sortColumn) {
                $sortDirection = strtoupper($sortDirections[$key]);

                if ($sortDirection != 'ASC' && $sortDirection != 'DESC') {
                    $sortDirection = 'ASC';
                }

                foreach ($this->getColumns() as $column) {
                    /** @var AbstractColumn $column */
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
     * @see \ZfcDatagrid\Renderer\AbstractRenderer::getFilters()
     *
     * @todo Make parameter config
     */
    public function getFilters(): array
    {
        if (! empty($this->filters)) {
            return $this->filters;
        }

        $request        = $this->getRequest();
        $toolbarFilters = $request->getPost('toolbarFilters', $request->getQuery('toolbarFilters'));
        $filters        = [];
        if (
            ($request->isPost() === true || $request->isGet() === true) &&
            null !== $toolbarFilters
        ) {
            foreach ($toolbarFilters as $uniqueId => $value) {
                if ($value != '') {
                    foreach ($this->getColumns() as $column) {
                        /** @var AbstractColumn $column */
                        if ($column->getUniqueId() == $uniqueId) {
                            $filter = new Filter();
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
     * @throws Exception
     */
    public function getCurrentPageNumber(): int
    {
        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $request = $this->getRequest();
        if ($request instanceof HttpRequest) {
            $this->currentPageNumber = (int) $request->getPost(
                $parameterNames['currentPage'],
                $request->getQuery($parameterNames['currentPage'], 1)
            );
        }

        return (int) $this->currentPageNumber;
    }

    public function prepareViewModel(Datagrid $grid)
    {
        parent::prepareViewModel($grid);

        $options = $this->getOptionsRenderer();

        $viewModel = $this->getViewModel();

        // Check if the datarange picker is enabled
        if (isset($options['daterange']['enabled']) && $options['daterange']['enabled'] === true) {
            $dateRangeParameters = $options['daterange']['options'];

            $viewModel->setVariable('daterangeEnabled', true);
            $viewModel->setVariable('daterangeParameters', $dateRangeParameters);
        } else {
            $viewModel->setVariable('daterangeEnabled', false);
        }
    }

    public function execute(): ViewModel
    {
        $viewModel = $this->getViewModel();
        $viewModel->setTemplate($this->getTemplate());

        return $viewModel;
    }
}
