<?php

namespace ZfcDatagrid\Renderer;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Zend\Cache;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\MvcEvent;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Filter;

/**
 * Class AbstractRenderer
 *
 * @package ZfcDatagrid\Renderer
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $title;

    /**
     * @var Cache\Storage\StorageInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheId;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var \ZfcDatagrid\Column\AbstractColumn[]
     */
    protected $columns = [];

    /**
     * @var \ZfcDataGrid\Column\Style\AbstractStyle[]
     */
    protected $rowStyles = [];

    /**
     * @var array
     */
    protected $sortConditions = null;

    /**
     * @var Filter[]
     */
    protected $filters = null;

    /**
     * @var int
     */
    protected $currentPageNumber = null;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var ViewModel
     */
    protected $viewModel;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $templateToolbar;

    /**
     * @var array
     */
    protected $toolbarTemplateVariables = [];

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptionsRenderer()
    {
        $options = $this->getOptions();
        if (isset($options['renderer'][$this->getName()])) {
            return $options['renderer'][$this->getName()];
        } else {
            return [];
        }
    }

    /**
     * @param ViewModel $viewModel
     *
     * @return $this
     */
    public function setViewModel(ViewModel $viewModel)
    {
        $this->viewModel = $viewModel;

        return $this;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function getViewModel()
    {
        return $this->viewModel;
    }

    /**
     * Set the view template.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setTemplate($name)
    {
        $this->template = (string) $name;

        return $this;
    }

    /**
     * Get the view template name.
     *
     * @return string
     * @throws \Exception
     */
    public function getTemplate()
    {
        if (null === $this->template) {
            $this->template = $this->getTemplatePathDefault('layout');
        }

        return $this->template;
    }

    /**
     * Get the default template path (if there is no own set).
     *
     * @param string $type layout or toolbar
     *
     * @return string
     *
     * @throws \Exception
     */
    private function getTemplatePathDefault($type = 'layout')
    {
        $optionsRenderer = $this->getOptionsRenderer();
        if (isset($optionsRenderer['templates'][$type])) {
            return $optionsRenderer['templates'][$type];
        }

        if ('layout' === $type) {
            return 'zfc-datagrid/renderer/'.$this->getName().'/'.$type;
        } elseif ('toolbar' === $type) {
            return 'zfc-datagrid/toolbar/toolbar';
        }

        throw new \Exception('Unknown type: "'.$type.'"');
    }

    /**
     * Set the toolbar view template name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setToolbarTemplate($name)
    {
        $this->templateToolbar = (string) $name;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getToolbarTemplate()
    {
        if (null === $this->templateToolbar) {
            $this->templateToolbar = $this->getTemplatePathDefault('toolbar');
        }

        return $this->templateToolbar;
    }

    /**
     * Set the toolbar view template variables.
     *
     * @param array $variables
     *
     * @return $this
     */
    public function setToolbarTemplateVariables(array $variables)
    {
        $this->toolbarTemplateVariables = $variables;

        return $this;
    }

    /**
     * Get the toolbar template variables.
     *
     * @return array
     */
    public function getToolbarTemplateVariables()
    {
        return $this->toolbarTemplateVariables;
    }

    /**
     * Paginator is here to retrieve the totalItemCount, count pages, current page, not for the actual data.
     *
     * @param \Zend\Paginator\Paginator $paginator
     *
     * @return $this
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Set the columns.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get all columns.
     *
     * @return \ZfcDatagrid\Column\AbstractColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param \ZfcDataGrid\Column\Style\AbstractStyle[] $rowStyles
     *
     * @return $this
     */
    public function setRowStyles($rowStyles = [])
    {
        $this->rowStyles = $rowStyles;

        return $this;
    }

    /**
     * @return \ZfcDataGrid\Column\Style\AbstractStyle[]
     */
    public function getRowStyles()
    {
        return $this->rowStyles;
    }

    /**
     * Calculate the sum of the displayed column width to 100%.
     *
     * @param array $columns
     *
     * @return $this
     */
    protected function calculateColumnWidthPercent(array $columns)
    {
        $widthAllColumn = 0;
        foreach ($columns as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            $widthAllColumn += $column->getWidth();
        }

        $widthSum = 0;
        // How much 1 percent columnd width is really "one" percent...
        $relativeOnePercent = $widthAllColumn / 100;

        foreach ($columns as $column) {
            $widthSum += (($column->getWidth() / $relativeOnePercent));
            $column->setWidth(($column->getWidth() / $relativeOnePercent));
        }

        return $this;
    }

    /**
     * The prepared data.
     *
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getCacheData()
    {
        return $this->getCache()->getItem($this->getCacheId());
    }

    /**
     * @throws \Exception
     *
     * @return array|false
     */
    private function getCacheSortConditions()
    {
        $cacheData = $this->getCacheData();
        if (! isset($cacheData['sortConditions'])) {
            return false;
        }

        return $cacheData['sortConditions'];
    }

    /**
     * @throws \Exception
     *
     * @return array|false
     */
    private function getCacheFilters()
    {
        $cacheData = $this->getCacheData();
        if (! isset($cacheData['filters'])) {
            return false;
        }

        return $cacheData['filters'];
    }

    /**
     * Not used ATM...
     *
     * @see \ZfcDatagrid\Renderer\RendererInterface::setMvcEvent()
     *
     * @return $this
     */
    public function setMvcEvent(MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;

        return $this;
    }

    /**
     * Not used ATM...
     *
     * @return MvcEvent
     */
    public function getMvcEvent()
    {
        return $this->mvcEvent;
    }

    /**
     * @return \Zend\Stdlib\RequestInterface
     */
    public function getRequest()
    {
        return $this->getMvcEvent()->getRequest();
    }

    /**
     * @param Translator $translator
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setTranslator($translator)
    {
        if (! $translator instanceof Translator && ! $translator instanceof \Zend\I18n\Translator\TranslatorInterface) {
            throw new \InvalidArgumentException(
                'Translator must be an instanceof ' .
                '"Zend\I18n\Translator\Translator" or "Zend\I18n\Translator\TranslatorInterface"'
            );
        }

        $this->translator = $translator;

        return $this;
    }

    /**
     * @return \Zend\I18n\Translator\Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function translate($string)
    {
        return $this->getTranslator() ? $this->getTranslator()->translate($string) : $string;
    }

    /**
     * Set the title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param Cache\Storage\StorageInterface $cache
     *
     * @return $this
     */
    public function setCache(Cache\Storage\StorageInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return Cache\Storage\StorageInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param string $cacheId
     *
     * @return $this
     */
    public function setCacheId($cacheId)
    {
        $this->cacheId = $cacheId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheId()
    {
        return $this->cacheId;
    }

    /**
     * Set the sort conditions explicit (e.g.
     * from a custom form).
     *
     * @param array $sortConditions
     *
     * @return $this
     */
    public function setSortConditions(array $sortConditions)
    {
        foreach ($sortConditions as $sortCondition) {
            if (! is_array($sortCondition)) {
                throw new InvalidArgumentException('Sort condition have to be an array');
            }

            if (! array_key_exists('column', $sortCondition)) {
                throw new InvalidArgumentException('Sort condition missing array key column');
            }
        }

        $this->sortConditions = $sortConditions;

        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSortConditions()
    {
        if (is_array($this->sortConditions)) {
            return $this->sortConditions;
        }

        if ($this->isExport() === true && $this->getCacheSortConditions() !== false) {
            // Export renderer should always retrieve the sort conditions from cache!
            $this->sortConditions = $this->getCacheSortConditions();

            return $this->sortConditions;
        }

        $this->sortConditions = $this->getSortConditionsDefault();

        return $this->sortConditions;
    }

    /**
     * Get the default sort conditions defined for the columns.
     *
     * @return array
     */
    public function getSortConditionsDefault()
    {
        $sortConditions = [];
        foreach ($this->getColumns() as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            if ($column->hasSortDefault() === true) {
                $sortDefaults = $column->getSortDefault();

                $sortConditions[$sortDefaults['priority']] = [
                    'column' => $column,
                    'sortDirection' => $sortDefaults['sortDirection'],
                ];

                $column->setSortActive($sortDefaults['sortDirection']);
            }
        }

        ksort($sortConditions);

        return $sortConditions;
    }

    /**
     * Set filters explicit (e.g. from a custom form).
     *
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters)
    {
        foreach ($filters as $filter) {
            if (! $filter instanceof Filter) {
                throw new InvalidArgumentException('Filter have to be an instanceof ZfcDatagrid\Filter');
            }
        }

        $this->filters = $filters;

        return $this;
    }

    /**
     * @return Filter[]
     * @throws \Exception
     */
    public function getFilters()
    {
        if (is_array($this->filters)) {
            return $this->filters;
        }

        if ($this->isExport() === true && $this->getCacheFilters() !== false) {
            // Export renderer should always retrieve the filters from cache!
            $this->filters = $this->getCacheFilters();

            return $this->filters;
        }

        $this->filters = $this->getFiltersDefault();

        return $this->filters;
    }

    /**
     * Get the default filter conditions defined for the columns.
     *
     * @return Filter[]
     */
    public function getFiltersDefault()
    {
        $filters = [];

        foreach ($this->getColumns() as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            if ($column->hasFilterDefaultValue() === true) {
                $filter = new Filter();
                $filter->setFromColumn($column, $column->getFilterDefaultValue());
                $filters[] = $filter;

                $column->setFilterActive($filter->getDisplayColumnValue());
            }
        }

        return $filters;
    }

    /**
     * Set the current page number.
     *
     * @param int $page
     *
     * @return $this
     */
    public function setCurrentPageNumber($page)
    {
        $this->currentPageNumber = (int) $page;

        return $this;
    }

    /**
     * Should be implemented for each renderer itself (just default).
     *
     * @return int
     */
    public function getCurrentPageNumber()
    {
        if (null === $this->currentPageNumber) {
            $this->currentPageNumber = 1;
        }

        return (int) $this->currentPageNumber;
    }

    /**
     * Should be implemented for each renderer itself (just default).
     *
     * @return int
     */
    public function getItemsPerPage($defaultItems = 25)
    {
        if ($this->isExport() === true) {
            return (int) - 1;
        }

        return $defaultItems;
    }

    /**
     * VERY UGLY DEPENDECY...
     * @todo Refactor :-)
     *
     * @see  \ZfcDatagrid\Renderer\RendererInterface::prepareViewModel()
     *
     * @param \ZfcDatagrid\Datagrid $grid
     *
     * @return $this
     * @throws \Exception
     */
    public function prepareViewModel(Datagrid $grid)
    {
        $viewModel = $this->getViewModel();

        $viewModel->setVariable('gridId', $grid->getId());
        $viewModel->setVariable('title', $this->getTitle());
        $viewModel->setVariable('parameters', $grid->getParameters());
        $viewModel->setVariable('overwriteUrl', $grid->getUrl());

        $viewModel->setVariable('templateToolbar', $this->getToolbarTemplate());
        foreach ($this->getToolbarTemplateVariables() as $key => $value) {
            $viewModel->setVariable($key, $value);
        }
        $viewModel->setVariable('rendererName', $this->getName());

        $options = $this->getOptions();
        $generalParameterNames = $options['generalParameterNames'];
        $viewModel->setVariable('generalParameterNames', $generalParameterNames);

        $viewModel->setVariable('columns', $this->getColumns());

        $viewModel->setVariable('rowStyles', $grid->getRowStyles());

        $viewModel->setVariable('paginator', $this->getPaginator());
        $viewModel->setVariable('data', $this->getData());
        $viewModel->setVariable('filters', $this->getFilters());

        $viewModel->setVariable('rowClickAction', $grid->getRowClickAction());
        $viewModel->setVariable('massActions', $grid->getMassActions());

        $viewModel->setVariable('isUserFilterEnabled', $grid->isUserFilterEnabled());

        /*
         * renderer specific parameter names
         */
        $optionsRenderer = $this->getOptionsRenderer();
        $viewModel->setVariable('optionsRenderer', $optionsRenderer);
        if ($this->isExport() === false) {
            $parameterNames = $optionsRenderer['parameterNames'];
            $viewModel->setVariable('parameterNames', $parameterNames);

            $activeParameters = [];
            $activeParameters[$parameterNames['currentPage']] = $this->getCurrentPageNumber();
            {
                $sortColumns = [];
                $sortDirections = [];
            foreach ($this->getSortConditions() as $sortCondition) {
                $sortColumns[] = $sortCondition['column']->getUniqueId();
                $sortDirections[] = $sortCondition['sortDirection'];
            }

                $activeParameters[$parameterNames['sortColumns']] = implode(',', $sortColumns);
                $activeParameters[$parameterNames['sortDirections']] = implode(',', $sortDirections);
            }
            $viewModel->setVariable('activeParameters', $activeParameters);
        }

        $viewModel->setVariable('exportRenderers', $grid->getExportRenderers());

        return $this;
    }

    /**
     * Return the name of the renderer.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Determine if the renderer is for export.
     *
     * @return bool
     */
    abstract public function isExport();

    /**
     * Determin if the renderer is HTML
     * It can be export + html -> f.x.
     * printing for HTML.
     *
     * @return bool
     */
    abstract public function isHtml();

    /**
     * Execute all...
     *
     * @return ViewModel Response\Stream
     */
    abstract public function execute();
}
