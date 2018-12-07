<?php
namespace ZfcDatagrid\Renderer;

use InvalidArgumentException;
use Zend\Cache;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use Zend\Stdlib\RequestInterface;
use ZfcDatagrid\Datagrid;
use ZfcDatagrid\Filter;
use function implode;
use function is_array;
use function array_key_exists;

abstract class AbstractRenderer implements RendererInterface
{
    /** @var array */
    protected $options = [];

    /** @var string */
    protected $title = '';

    /** @var Cache\Storage\StorageInterface|null */
    protected $cache;

    /** @var string|null */
    protected $cacheId;

    /** @var Paginator|null */
    protected $paginator;

    /** @var \ZfcDatagrid\Column\AbstractColumn[] */
    protected $columns = [];

    /** @var \ZfcDataGrid\Column\Style\AbstractStyle[] */
    protected $rowStyles = [];

    /** @var array */
    protected $sortConditions = [];

    /** @var Filter[] */
    protected $filters = [];

    /** @var int|null */
    protected $currentPageNumber = null;

    /** @var array */
    protected $data = [];

    /** @var ViewModel|null */
    protected $viewModel;

    /** @var null|string */
    protected $template;

    /** @var null|string */
    protected $templateToolbar;

    /** @var array */
    protected $toolbarTemplateVariables = [];

    /** @var TranslatorInterface|null */
    protected $translator;

    /** var RequestInterface */
    protected $request;

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getOptionsRenderer(): array
    {
        $options = $this->getOptions();

        return $options['renderer'][$this->getName()] ?? [];
    }

    /**
     * @param ViewModel $viewModel
     *
     * @return $this
     */
    public function setViewModel(ViewModel $viewModel): self
    {
        $this->viewModel = $viewModel;

        return $this;
    }

    /**
     * @return null|ViewModel
     */
    public function getViewModel(): ?ViewModel
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
    public function setTemplate(string $name): self
    {
        $this->template = (string) $name;

        return $this;
    }

    /**
     * Get the view template name.
     *
     * @return string
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
    private function getTemplatePathDefault(string $type = 'layout'): string
    {
        $optionsRenderer = $this->getOptionsRenderer();
        if (isset($optionsRenderer['templates'][$type])) {
            return $optionsRenderer['templates'][$type];
        }

        if ('layout' === $type) {
            return 'zfc-datagrid/renderer/' . $this->getName() . '/' . $type;
        } elseif ('toolbar' === $type) {
            return 'zfc-datagrid/toolbar/toolbar';
        }

        throw new \Exception('Unknown type: "' . $type . '"');
    }

    /**
     * Set the toolbar view template name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setToolbarTemplate(string $name): self
    {
        $this->templateToolbar = $name;

        return $this;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getToolbarTemplate(): string
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
    public function setToolbarTemplateVariables(array $variables): self
    {
        $this->toolbarTemplateVariables = $variables;

        return $this;
    }

    /**
     * Get the toolbar template variables.
     *
     * @return array
     */
    public function getToolbarTemplateVariables(): array
    {
        return $this->toolbarTemplateVariables;
    }

    /**
     * Paginator is here to retreive the totalItemCount, count pages, current page
     * NOT FOR THE ACTUAL DATA!!!!
     *
     * @param Paginator $paginator
     *
     * @return $this
     */
    public function setPaginator(Paginator $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * @return null|Paginator
     */
    public function getPaginator(): ?Paginator
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
    public function setColumns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get all columns.
     *
     * @return \ZfcDatagrid\Column\AbstractColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param \ZfcDataGrid\Column\Style\AbstractStyle[] $rowStyles
     *
     * @return $this
     */
    public function setRowStyles(array $rowStyles = []): self
    {
        $this->rowStyles = $rowStyles;

        return $this;
    }

    /**
     * @return \ZfcDataGrid\Column\Style\AbstractStyle[]
     */
    public function getRowStyles(): array
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
    protected function calculateColumnWidthPercent(array $columns): self
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
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array|null
     */
    public function getCacheData(): ?array
    {
        return $this->getCache()->getItem($this->getCacheId());
    }

    /**
     * @throws \Exception
     *
     * @return array|false
     */
    private function getCacheSortConditions(): ?array
    {
        $cacheData = $this->getCacheData();

        return $cacheData['sortConditions'] ?? null;
    }

    /**
     * @throws \Exception
     *
     * @return array|false
     */
    private function getCacheFilters(): ?array
    {
        $cacheData = $this->getCacheData();

        return $cacheData['filters'] ?? null;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param TranslatorInterface $translator
     *
     * @return $this
     */
    public function setTranslator(TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * @return TranslatorInterface|null
     */
    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translate(string $string): string
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
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param Cache\Storage\StorageInterface $cache
     *
     * @return $this
     */
    public function setCache(Cache\Storage\StorageInterface $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return Cache\Storage\StorageInterface|null
     */
    public function getCache(): ?Cache\Storage\StorageInterface
    {
        return $this->cache;
    }

    /**
     * @param string $cacheId
     *
     * @return $this
     */
    public function setCacheId(string $cacheId): self
    {
        $this->cacheId = $cacheId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCacheId(): ?string
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
    public function setSortConditions(array $sortConditions): self
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
     */
    public function getSortConditions(): array
    {
        if (!empty($this->sortConditions)) {
            return $this->sortConditions;
        }

        if ($this->isExport() === true && null !== $this->getCacheSortConditions()) {
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
    public function getSortConditionsDefault(): array
    {
        $sortConditions = [];
        foreach ($this->getColumns() as $column) {
            /* @var $column \ZfcDatagrid\Column\AbstractColumn */
            if ($column->hasSortDefault() === true) {
                $sortDefaults = $column->getSortDefault();

                $sortConditions[$sortDefaults['priority']] = [
                    'column'        => $column,
                    'sortDirection' => $sortDefaults['sortDirection'],
                ];

                $column->setSortActive($sortDefaults['sortDirection']);
            }
        }

        ksort($sortConditions);

        return $sortConditions;
    }

    /**
     * Set filters explicit (e.g.
     * from a custom form).
     *
     * @param Filter[] $filters
     *
     * @return $this
     */
    public function setFilters(array $filters): self
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
     */
    public function getFilters(): array
    {
        if (!empty($this->filters)) {
            return $this->filters;
        }

        if ($this->isExport() === true && null !== $this->getCacheFilters()) {
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
    public function getFiltersDefault(): array
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
    public function setCurrentPageNumber(int $page): self
    {
        $this->currentPageNumber = $page;

        return $this;
    }

    /**
     * Should be implemented for each renderer itself (just default).
     *
     * @return int
     */
    public function getCurrentPageNumber(): int
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
    public function getItemsPerPage($defaultItems = 25): int
    {
        if (true === $this->isExport()) {
            return (int) - 1;
        }

        return $defaultItems;
    }

    /**
     * VERY UGLY DEPENDECY...
     *
     * @todo Refactor :-)
     *
     * @see \ZfcDatagrid\Renderer\RendererInterface::prepareViewModel()
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

        $options               = $this->getOptions();
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

            $activeParameters                                 = [];
            $activeParameters[$parameterNames['currentPage']] = $this->getCurrentPageNumber();
            {
                $sortColumns    = [];
                $sortDirections = [];
            foreach ($this->getSortConditions() as $sortCondition) {
                $sortColumns[]    = $sortCondition['column']->getUniqueId();
                $sortDirections[] = $sortCondition['sortDirection'];
            }

                $activeParameters[$parameterNames['sortColumns']]    = implode(',', $sortColumns);
                $activeParameters[$parameterNames['sortDirections']] = implode(',', $sortDirections);
            }
            $viewModel->setVariable('activeParameters', $activeParameters);
        }

        $viewModel->setVariable('exportRenderers', $grid->getExportRenderers());
    }

    /**
     * Return the name of the renderer.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Determine if the renderer is for export.
     *
     * @return bool
     */
    abstract public function isExport(): bool;

    /**
     * Determin if the renderer is HTML
     * It can be export + html -> f.x.
     * printing for HTML.
     *
     * @return bool
     */
    abstract public function isHtml(): bool;

    /**
     * Execute all...
     *
     * @return ViewModel Response\Stream
     */
    abstract public function execute();
}
