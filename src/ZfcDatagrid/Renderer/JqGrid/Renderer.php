<?php
namespace ZfcDatagrid\Renderer\JqGrid;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Stdlib\RequestInterface;
use Zend\View\Model\JsonModel;
use ZfcDatagrid\Column;
use ZfcDatagrid\Renderer\AbstractRenderer;
use function explode;
use function count;
use function strtoupper;
use function implode;

class Renderer extends AbstractRenderer
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'jqGrid';
    }

    /**
     * @return bool
     */
    public function isHtml(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isExport(): bool
    {
        return false;
    }

    /**
     * @return HttpRequest
     *
     * @throws \Exception
     */
    public function getRequest(): ?RequestInterface
    {
        $request = parent::getRequest();
        if (! $request instanceof HttpRequest) {
            throw new \Exception(
                'Request must be an instance of Zend\Http\PhpEnvironment\Request for HTML rendering'
            );
        }

        return $request;
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
     * @return array
     *
     * @throws \Exception
     */
    public function getFilters(): array
    {
        if (!empty($this->filters)) {
            // set from cache! (for export)
            return $this->filters;
        }

        $filters = [];

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $request  = $this->getRequest();
        $isSearch = $request->getPost($parameterNames['isSearch'], $request->getQuery($parameterNames['isSearch']));
        if ('true' == $isSearch) {
            // User filtering
            foreach ($this->getColumns() as $column) {
                /* @var $column \ZfcDatagrid\Column\AbstractColumn */
                if ($request->getPost($column->getUniqueId(), $request->getQuery($column->getUniqueId())) != '') {
                    $value = $request->getPost($column->getUniqueId(), $request->getQuery($column->getUniqueId()));

                    $filter = new \ZfcDatagrid\Filter();
                    $filter->setFromColumn($column, $value);

                    $filters[] = $filter;

                    $column->setFilterActive($filter->getDisplayColumnValue());
                }
            }
        }

        if (empty($filters)) {
            // No user sorting -> get default sorting
            $filters = $this->getFiltersDefault();
        }

        $this->filters = $filters;

        return $this->filters;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getCurrentPageNumber(): int
    {
        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames  = $optionsRenderer['parameterNames'];

        $request = $this->getRequest();
        if ($request instanceof HttpRequest) {
            $currentPage = $request->getPost(
                $parameterNames['currentPage'],
                $request->getQuery($parameterNames['currentPage'])
            );
            if ($currentPage != '') {
                $this->currentPageNumber = (int) $currentPage;
            }
        }

        return (int) $this->currentPageNumber;
    }

    /**
     * @return null|JsonModel|\Zend\View\Model\ViewModel
     * @throws \Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() === true &&
            $request->getPost('nd', $request->getQuery('nd')) != ''
        ) {
            // AJAX Request...load only data...
            $viewModel = new JsonModel();
            $viewModel->setVariable('data', $this->getDataJqGrid());
        } else {
            $viewModel = $this->getViewModel();
            $viewModel->setTemplate($this->getTemplate());
            $viewModel->setVariable('data', $this->getDataJqGrid());

            $columnsRowClickDisabled = [];
            $columns                 = $viewModel->getVariable('columns');
            foreach ($columns as $column) {
                /* @var $column \ZfcDatagrid\Column\AbstractColumn */

                if ($column->isRowClickEnabled() !== true) {
                    $columnsRowClickDisabled[] = $column->getUniqueId();
                }
            }

            $viewModel->setVariable('columnsRowClickDisabled', $columnsRowClickDisabled);
        }

        return $viewModel;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = parent::getData();

        foreach ($data as &$row) {
            foreach ($this->getColumns() as $column) {
                if ($column instanceof Column\Select) {
                    // $row[$column->getUniqueId()] = nl2br($row[$column->getUniqueId()], true);
                } elseif ($column instanceof Column\Action) {
                    /* @var $column \ZfcDatagrid\Column\Action */

                    if ($column->getActions()) {
                        $actions = [];
                        foreach ($column->getActions() as $action) {
                            /* @var $action \ZfcDatagrid\Column\Action\AbstractAction */
                            if ($action->isDisplayed($row) === true) {
                                $action->setTitle($this->translate($action->getTitle()));
                                $actions[] = $action->toHtml($row);
                            }
                        }
                        $row[$column->getUniqueId()] = implode(' ', $actions);
                    }
                } elseif ($column instanceof Column\Action\Icon) {
                    $row[$column->getUniqueId()] = $column->getIconClass();
                }
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    private function getDataJqGrid(): array
    {
        return [
            'rows'    => $this->getData(),
            'page'    => $this->getPaginator()->getCurrentPageNumber(),
            'total'   => $this->getPaginator()->count(),
            'records' => $this->getPaginator()->getTotalItemCount(),
        ];
    }
}
