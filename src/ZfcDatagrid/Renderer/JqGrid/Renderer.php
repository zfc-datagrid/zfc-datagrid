<?php

namespace ZfcDatagrid\Renderer\JqGrid;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\View\Model\JsonModel;
use ZfcDatagrid\Column;
use ZfcDatagrid\Renderer\AbstractRenderer;

class Renderer extends AbstractRenderer
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'jqGrid';
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isExport()
    {
        return false;
    }

    /**
     * @return HttpRequest
     *
     * @throws \Exception
     */
    public function getRequest()
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
    public function getSortConditions()
    {
        if (is_array($this->sortConditions)) {
            return $this->sortConditions;
        }

        $request = $this->getRequest();

        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames = $optionsRenderer['parameterNames'];

        $sortConditions = [];

        $sortColumns = $request->getPost(
            $parameterNames['sortColumns'],
            $request->getQuery($parameterNames['sortColumns'])
        );
        $sortDirections = $request->getPost(
            $parameterNames['sortDirections'],
            $request->getQuery($parameterNames['sortDirections'])
        );
        if ($sortColumns != '') {
            $sortColumns = explode(',', $sortColumns);
            $sortDirections = explode(',', $sortDirections);

            if (count($sortColumns) != count($sortDirections)) {
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
                            'column' => $column,
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
     * @throws \Exception
     */
    public function getFilters()
    {
        if (is_array($this->filters)) {
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
            $values = $this->prepareFilter(json_decode($request->getPost('filters'), true));
            foreach ($this->getColumns() as $column) {
                /* @var $column \ZfcDatagrid\Column\AbstractColumn */
                if ($request->getPost($column->getUniqueId(), $request->getQuery($column->getUniqueId())) != '') {
                    $value = $request->getPost($column->getUniqueId(), $request->getQuery($column->getUniqueId()));
                    $filters[] = $this->createFilter($column, $value);
                } elseif ($values !== null && isset($values[$column->getUniqueId()])) {
                    $value = implode(',', $values[$column->getUniqueId()]['values']);
                    $filter = $this->createFilter($column, $value);
                    $filters[] = $filter;
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

    public function createFilter($column, $value) {
        /* @var $column \ZfcDatagrid\Column\AbstractColumn */
        $filter = new \ZfcDatagrid\Filter();
        $filter->setFromColumn($column, $value);
        $column->setFilterActive($filter->getDisplayColumnValue());

        return $filter;
    }

    public function prepareFilter($rawFilters) {
        static $fields = [];
        foreach ($rawFilters as $key => $values) {
            if ($values && $key === 'rules') {
                foreach ($values as $rule) {
                    if (!isset($fields[$rule['field']])) {
                        $fields[$rule['field']] = [];
                    }
                    $fields[$rule['field']]['values'][] = $rule['data'];
                }
            }
            if ($values && $key === 'groups') {
                foreach ($values as $sub) {
                    $this->prepareFilter($sub);
                }
            }
        }

        return $fields;
    }

    public function getCurrentPageNumber()
    {
        $optionsRenderer = $this->getOptionsRenderer();
        $parameterNames = $optionsRenderer['parameterNames'];

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
            $columns = $viewModel->getVariable('columns');
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

    public function getData()
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

    private function getDataJqGrid()
    {
        return [
            'rows' => $this->getData(),
            'page' => $this->getPaginator()->getCurrentPageNumber(),
            'total' => $this->getPaginator()->count(),
            'records' => $this->getPaginator()->getTotalItemCount(),
        ];
    }
}
