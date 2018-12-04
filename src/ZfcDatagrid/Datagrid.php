<?php
namespace ZfcDatagrid;

use ArrayIterator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Zend\Cache;
use Zend\Console\Request as ConsoleRequest;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Stdlib\RequestInterface;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\Mvc\MvcEvent;
use Zend\Paginator\Paginator;
use Zend\Router\RouteStackInterface;
use Zend\Session\Container as SessionContainer;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\DataSource\DataSourceInterface;
use function md5;
use function preg_replace;
use function is_array;
use function func_get_args;
use function count;
use function class_exists;
use function ucfirst;
use function method_exists;
use function in_array;
use function call_user_func_array;
use function call_user_func;
use function ksort;
use function is_object;
use function sprintf;
use function get_class;


class Datagrid
{
    const DEFAULT_POSITION = 1;

    /** @var array */
    protected $options = [];

    /** @var SessionContainer|null */
    protected $session;

    /** @var Cache\Storage\StorageInterface|null */
    protected $cache;

    /** @var string */
    protected $cacheId;

    /** @var array */
    protected $parameters = [];

    /** @var string */
    protected $url = '';

    /** @var RequestInterface */
    protected $request;

    /**
     * View or Response.
     *
     * @var \Zend\Http\Response\Stream|\Zend\View\Model\ViewModel
     */
    protected $response;

    /** @var Renderer\AbstractRenderer */
    protected $renderer;

    /** @var TranslatorInterface|null */
    protected $translator;

    /** @var RouteStackInterface */
    protected $router;

    /** @var string */
    protected $id;

    /**
     * The grid title.
     *
     * @var string
     */
    protected $title = '';

    /** @var DataSource\DataSourceInterface */
    protected $dataSource = null;

    /** @var int */
    protected $defaulItemsPerPage = 25;

    /** @var array */
    protected $columns = [];

    /** @var array */
    protected $positions = [];

    /** @var Style\AbstractStyle[] */
    protected $rowStyles = [];

    /** @var Column\Action\AbstractAction */
    protected $rowClickAction;

    /** @var Action\Mass[] */
    protected $massActions = [];

    /**
     * The prepared data.
     *
     * @var array
     */
    protected $preparedData = [];

    /** @var bool */
    protected $isUserFilterEnabled = true;

    /** @var Paginator */
    protected $paginator = null;

    /** @var array */
    protected $exportRenderers;

    /** @var string|null */
    protected $toolbarTemplate;

    /** @var array */
    protected $toolbarTemplateVariables = [];

    /** @var ViewModel */
    protected $viewModel;

    /** @var bool */
    protected $isInit = false;

    /** @var bool */
    protected $isDataLoaded = false;

    /** @var bool */
    protected $isRendered = false;

    /** @var string */
    protected $forceRenderer;

    /** @var Renderer\AbstractRenderer|null */
    protected $rendererService;

    /** @var string[] */
    protected $specialMethods = [
        'filterSelectOptions',
        'rendererParameter',
        'replaceValues',
        'select',
        'sortDefault',
    ];

    /**
     * Init method is called automatically with the service creation.
     *
     * @return $this
     */
    public function init(): self
    {
        if (null === $this->getCache()) {
            $options = $this->getOptions();
            $this->setCache(Cache\StorageFactory::factory($options['cache']));
        }

        $this->isInit = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInit(): bool
    {
        return $this->isInit;
    }

    /**
     * Set the options from config.
     *
     * @param array $config
     *
     * @return $this
     */
    public function setOptions(array $config): self
    {
        $this->options = $config;

        return $this;
    }

    /**
     * Get the config options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the grid id.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId(?string $id = null): self
    {
        if ($id !== null) {
            $id = preg_replace("/[^a-z0-9_\\\d]/i", '_', $id);

            $this->id = (string) $id;
        }

        return $this;
    }

    /**
     * Get the grid id.
     *
     * @return string
     */
    public function getId(): string
    {
        if (null === $this->id) {
            $this->id = 'defaultGrid';
        }

        return $this->id;
    }

    /**
     * Set the session.
     *
     * @param SessionContainer $session
     *
     * @return $this
     */
    public function setSession(SessionContainer $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session container.
     *
     * Instantiate session container if none currently exists
     *
     * @return SessionContainer
     */
    public function getSession(): SessionContainer
    {
        if (null === $this->session) {
            // Using fully qualified name, to ensure polyfill class alias is used
            $this->session = new SessionContainer($this->getId());
        }

        return $this->session;
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
     * @return Cache\Storage\StorageInterface
     */
    public function getCache(): ?Cache\Storage\StorageInterface
    {
        return $this->cache;
    }

    /**
     * Set the cache id.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setCacheId(string $id): self
    {
        $this->cacheId = $id;

        return $this;
    }

    /**
     * Get the cache id.
     *
     * @return string
     */
    public function getCacheId(): string
    {
        if (null === $this->cacheId) {
            $this->cacheId = md5($this->getSession()
                ->getManager()
                ->getId() . '_' . $this->getId());
        }

        return $this->cacheId;
    }

    /**
     * @return RequestInterface|null
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
     * Set the translator.
     *
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
     * @return bool
     */
    public function hasTranslator(): bool
    {
        return null !== $this->translator;
    }

    /**
     * @param RouteStackInterface $router
     *
     * @return $this
     */
    public function setRouter(RouteStackInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    /**
     * @return RouteStackInterface
     */
    public function getRouter(): RouteStackInterface
    {
        return $this->router;
    }

    /**
     * @return bool
     */
    public function hasRouter()
    {
        return null !== $this->router;
    }

    /**
     * Set the data source.
     *
     * @param mixed $data
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setDataSource($data): self
    {
        if ($data instanceof DataSource\DataSourceInterface) {
            $this->dataSource = $data;
        } elseif (is_array($data)) {
            $this->dataSource = new DataSource\PhpArray($data);
        } elseif ($data instanceof QueryBuilder) {
            $this->dataSource = new DataSource\Doctrine2($data);
        } elseif ($data instanceof ZendSelect) {
            $args = func_get_args();
            if (count($args) === 1 ||
                (! $args[1] instanceof \Zend\Db\Adapter\Adapter && ! $args[1] instanceof \Zend\Db\Sql\Sql)
            ) {
                throw new \InvalidArgumentException(
                    'For "Zend\Db\Sql\Select" also a "Zend\Db\Adapter\Sql" or "Zend\Db\Sql\Sql" is needed.'
                );
            }
            $this->dataSource = new DataSource\ZendSelect($data);
            $this->dataSource->setAdapter($args[1]);
        } elseif ($data instanceof Collection) {
            $args = func_get_args();
            if (count($args) === 1 || ! $args[1] instanceof \Doctrine\ORM\EntityManager) {
                throw new \InvalidArgumentException(
                    'If providing a Collection, also the Doctrine\ORM\EntityManager is needed as a second parameter'
                );
            }
            $this->dataSource = new DataSource\Doctrine2Collection($data);
            $this->dataSource->setEntityManager($args[1]);
        } else {
            throw new \InvalidArgumentException(
                '$data must implement the interface ZfcDatagrid\DataSource\DataSourceInterface'
            );
        }

        return $this;
    }

    /**
     * @return DataSourceInterface
     */
    public function getDataSource(): ?DataSourceInterface
    {
        return $this->dataSource;
    }

    /**
     * Datasource defined?
     *
     * @return bool
     */
    public function hasDataSource(): bool
    {
        return null !== $this->dataSource;
    }

    /**
     * Set default items per page (-1 for unlimited).
     *
     * @param int $count
     *
     * @return $this
     */
    public function setDefaultItemsPerPage(int $count = 25): self
    {
        $this->defaulItemsPerPage = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultItemsPerPage(): int
    {
        return $this->defaulItemsPerPage;
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
     * Add a external parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addParameter(string $name, $value): self
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * These parameters are handled to the view + over all grid actions.
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Has parameters?
     *
     * @return bool
     */
    public function hasParameters(): bool
    {
        return (bool) $this->getParameters();
    }

    /**
     * Set the base url.
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set the export renderers (overwrite the config).
     *
     * @param array $renderers
     *
     * @return $this
     */
    public function setExportRenderers(array $renderers = []): self
    {
        $this->exportRenderers = $renderers;

        return $this;
    }

    /**
     * Get the export renderers.
     *
     * @return array
     */
    public function getExportRenderers(): array
    {
        if (null === $this->exportRenderers) {
            $options               = $this->getOptions();
            $this->exportRenderers = $options['settings']['export']['formats'];
        }

        return $this->exportRenderers;
    }

    /**
     * Create a column from array instanceof.
     *
     * @param array $config
     *
     * @return Column\AbstractColumn
     */
    private function createColumn(array $config): Column\AbstractColumn
    {
        $colType = isset($config['colType']) ? $config['colType'] : 'Select';
        if (class_exists($colType, true)) {
            $class = $colType;
        } elseif (class_exists('ZfcDatagrid\\Column\\' . $colType, true)) {
            $class = 'ZfcDatagrid\\Column\\' . $colType;
        } else {
            throw new \InvalidArgumentException(sprintf('Column type: "%s" not found!', $colType));
        }

        if ('ZfcDatagrid\\Column\\Select' == $class) {
            if (! isset($config['select']['column'])) {
                throw new \InvalidArgumentException(
                    'For "ZfcDatagrid\Column\Select" the option select[column] must be defined!'
                );
            }
            $table = isset($config['select']['table']) ? $config['select']['table'] : null;

            $instance = new $class($config['select']['column'], $table);
        } else {
            $instance = new $class();
        }

        foreach ($config as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($instance, $method)) {
                if (in_array($key, $this->specialMethods)) {
                    if (! is_array($value)) {
                        $value = [
                            $value,
                        ];
                    }
                    call_user_func_array([
                        $instance,
                        $method,
                    ], $value);
                } else {
                    call_user_func([
                        $instance,
                        $method,
                    ], $value);
                }
            }
        }

        return $instance;
    }

    /**
     * Set multiple columns by array (willoverwrite all existing).
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setColumns(array $columns): self
    {
        $useColumns = [];

        foreach ($columns as $col) {
            if (!$col instanceof Column\AbstractColumn) {
                $col = $this->createColumn($col);
            }
            $useColumns[$col->getUniqueId()] = $col;
        }

        $this->columns = $useColumns;

        return $this;
    }

    /**
     * Add a column by array config or instanceof Column\AbstractColumn.
     *
     * @param array|Column\AbstractColumn $col
     *
     * @return $this
     */
    public function addColumn($col): self
    {
        if (!$col instanceof Column\AbstractColumn) {
            $col = $this->createColumn($col);
        }

        if (null === $col->getPosition()) {
            $col->setPosition(self::DEFAULT_POSITION);
        }

        $this->columns[$col->getUniqueId()] = $col;
        $this->positions[$col->getPosition()][$col->getUniqueId()] = $col;

        return $this;
    }

    /**
     * @return \ZfcDatagrid\Column\AbstractColumn[]
     */
    public function sortColumns(): array
    {
        ksort($this->positions);

        $columns = [];
        foreach ($this->positions as $position => $column) {
            $columns += $column;
        }

        return $this->columns = $columns;
    }

    /**
     * @return \ZfcDatagrid\Column\AbstractColumn[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $id
     *
     * @return Column\AbstractColumn|null
     */
    public function getColumnByUniqueId($id)
    {
        return $this->columns[$id] ?? null;
    }

    /**
     * @param Style\AbstractStyle $style
     *
     * @return $this
     */
    public function addRowStyle(Style\AbstractStyle $style): self
    {
        $this->rowStyles[] = $style;

        return $this;
    }

    /**
     * @return Style\AbstractStyle[]
     */
    public function getRowStyles(): array
    {
        return $this->rowStyles;
    }

    /**
     * @return bool
     */
    public function hasRowStyles(): bool
    {
        return (bool) $this->rowStyles;
    }

    /**
     * If disabled, the toolbar filter will not be shown to the user.
     *
     * @param bool $mode
     *
     * @return $this
     */
    public function setUserFilterDisabled(bool $mode = true): self
    {
        $this->isUserFilterEnabled = ! $mode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUserFilterEnabled(): bool
    {
        return (bool) $this->isUserFilterEnabled;
    }

    /**
     * Set the row click action - identity will be automatically appended!
     *
     * @param Column\Action\AbstractAction $action
     *
     * @return $this
     */
    public function setRowClickAction(Column\Action\AbstractAction $action): self
    {
        $this->rowClickAction = $action;

        return $this;
    }

    /**
     * @return null|Column\Action\AbstractAction
     */
    public function getRowClickAction(): ?Column\Action\AbstractAction
    {
        return $this->rowClickAction;
    }

    /**
     * @return bool
     */
    public function hasRowClickAction(): bool
    {
        return is_object($this->rowClickAction);
    }

    /**
     * Add a mass action.
     *
     * @param Action\Mass $action
     *
     * @return $this
     */
    public function addMassAction(Action\Mass $action): self
    {
        $this->massActions[] = $action;

        return $this;
    }

    /**
     * @return Action\Mass[]
     */
    public function getMassActions(): array
    {
        return $this->massActions;
    }

    /**
     * @return bool
     */
    public function hasMassAction(): bool
    {
        return (bool) $this->massActions;
    }

    /**
     * Overwrite the render
     * F.x.
     * if you want to directly render a PDF.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setRendererName(?string $name = null): self
    {
        $this->forceRenderer = $name;

        return $this;
    }

    /**
     * Get the current renderer name.
     *
     * @return string
     */
    public function getRendererName(): string
    {
        $options       = $this->getOptions();
        $parameterName = $options['generalParameterNames']['rendererType'];

        if ($this->forceRenderer !== null) {
            // A special renderer was given -> use is
            $rendererName = $this->forceRenderer;
        } else {
            // DEFAULT
            if ($this->getRequest() instanceof ConsoleRequest) {
                $rendererName = $options['settings']['default']['renderer']['console'];
            } else {
                $rendererName = $options['settings']['default']['renderer']['http'];
            }
        }

        // From request
        if ($this->getRequest() instanceof HttpRequest && $this->getRequest()->getQuery($parameterName) != '') {
            $rendererName = $this->getRequest()->getQuery($parameterName);
        }

        return $rendererName;
    }

    /**
     * Return the current renderer.
     *
     * @throws \Exception
     *
     * @return Renderer\AbstractRenderer
     */
    public function getRenderer(): Renderer\AbstractRenderer
    {
        if (null === $this->renderer) {
            if (null !== $this->rendererService) {
                $renderer = $this->rendererService;
                if (! $renderer instanceof Renderer\AbstractRenderer) {
                    throw new \Exception(
                        'Renderer service must implement "ZfcDatagrid\Renderer\AbstractRenderer"'
                    );
                }
                $renderer->setOptions($this->getOptions());
                $renderer->setRequest($this->getRequest());
                if ($this->getToolbarTemplate() !== null) {
                    $renderer->setToolbarTemplate($this->getToolbarTemplate());
                }
                $renderer->setToolbarTemplateVariables($this->getToolbarTemplateVariables());
                $renderer->setViewModel($this->getViewModel());
                if ($this->hasTranslator()) {
                    $renderer->setTranslator($this->getTranslator());
                }
                $renderer->setTitle($this->getTitle());
                $renderer->setColumns($this->getColumns());
                $renderer->setRowStyles($this->getRowStyles());
                $renderer->setCache($this->getCache());
                $renderer->setCacheId($this->getCacheId());

                $this->renderer = $renderer;
            } else {
                throw new \Exception(
                    sprintf(
                        'Renderer service was not found, please register it: "zfcDatagrid.renderer.%s"',
                        $this->getRendererName()
                    )
                );
            }
        }

        return $this->renderer;
    }

    /**
     * @return bool
     */
    public function isDataLoaded(): bool
    {
        return (bool) $this->isDataLoaded;
    }

    /**
     * Load the data.
     */
    public function loadData()
    {
        if (true === $this->isDataLoaded) {
            return true;
        }

        if ($this->isInit() !== true) {
            throw new \Exception('The init() method has to be called, before you can call loadData()!');
        }

        if ($this->hasDataSource() === false) {
            throw new \Exception('No datasource defined! Please call "setDataSource()" first"');
        }

        $this->sortColumns();

        /**
         * Apply cache.
         */
        $renderer = $this->getRenderer();

        /*
         * Step 1) Apply needed columns + filters + sort
         * - from Request (HTML View) -> and save in cache for export
         * - or from cache (Export PDF / Excel) -> same view like HTML (without LIMIT/Pagination)
         */
        {
            /*
             * Step 1.1) Only select needed columns (performance)
             */
            $this->getDataSource()->setColumns($this->getColumns());

            /*
             * Step 1.2) Sorting
             */
            foreach ($renderer->getSortConditions() as $condition) {
                $this->getDataSource()->addSortCondition($condition['column'], $condition['sortDirection']);
            }

                /*
                 * Step 1.3) Filtering
                 */
            foreach ($renderer->getFilters() as $filter) {
                $this->getDataSource()->addFilter($filter);
            }
        }

        /*
         * Step 2) Load the data (Paginator)
         */
        {
            $this->getDataSource()->execute();
            $paginatorAdapter = $this->getDataSource()->getPaginatorAdapter();

            \Zend\Paginator\Paginator::setDefaultScrollingStyle('Sliding');

            $this->paginator = new Paginator($paginatorAdapter);
            $this->paginator->setCurrentPageNumber($renderer->getCurrentPageNumber());
            $this->paginator->setItemCountPerPage($renderer->getItemsPerPage($this->getDefaultItemsPerPage()));

            /* @var $currentItems \ArrayIterator */
            $data = $this->paginator->getCurrentItems();
        if (! is_array($data)) {
            if ($data instanceof \Zend\Db\ResultSet\ResultSet) {
                $data = $data->toArray();
            } elseif ($data instanceof ArrayIterator) {
                $data = $data->getArrayCopy();
            } else {
                if (is_object($data)) {
                    $add = get_class($data);
                } else {
                    $add = '[no object]';
                }
                throw new \Exception(
                    sprintf(
                        'The paginator returned an unknown result: %s ' .
                        '(allowed: \ArrayIterator or a plain php array)',
                        $add
                    )
                );
            }
        }
        }

        /*
         * check if the export is enabled
         * Save cache
         */
        if ($this->getOptions()['settings']['export']['enabled'] && $renderer->isExport() === false) {
            $cacheData = [
                'sortConditions' => $renderer->getSortConditions(),
                'filters'        => $renderer->getFilters(),
                'currentPage'    => $this->getPaginator()->getCurrentPageNumber(),
            ];
            $success = $this->getCache()->setItem($this->getCacheId(), $cacheData);
            if ($success !== true) {
                /** @var \Zend\Cache\Storage\Adapter\FilesystemOptions $options */
                $options = $this->getCache()->getOptions();
                throw new \Exception(
                    sprintf(
                        'Could not save the datagrid cache. Does the directory "%s" ' .
                        'exists and is writeable? CacheId: %s',
                        $options->getCacheDir(),
                        $this->getCacheId()
                    )
                );
            }
        }

        /*
         * Step 3) Format the data - Translate - Replace - Date / time / datetime - Numbers - ...
         */
        $prepareData = new PrepareData($data, $this->getColumns());
        if ($this->getRouter() instanceof RouteStackInterface) {
            $prepareData->setRouter($this->getRouter());
        }

        $prepareData->setRendererName($this->getRendererName());
        if ($this->hasTranslator()) {
            $prepareData->setTranslator($this->getTranslator());
        }
        $prepareData->prepare();
        $this->preparedData = $prepareData->getData();

        $this->isDataLoaded = true;
    }

    /**
     * Render the grid.
     */
    public function render()
    {
        if (false === $this->isDataLoaded()) {
            $this->loadData();
        }

        /**
         * Step 4) Render the data to the defined output format (HTML, PDF...)
         * - Styling the values based on column (and value).
         */
        $renderer = $this->getRenderer();
        $renderer->setTitle($this->getTitle());
        $renderer->setPaginator($this->getPaginator());
        $renderer->setData($this->getPreparedData());
        $renderer->prepareViewModel($this);

        $this->response = $renderer->execute();

        $this->isRendered = true;
    }

    /**
     * Is already rendered?
     *
     * @return bool
     */
    public function isRendered(): bool
    {
        return $this->isRendered;
    }

    /**
     * @throws \Exception
     *
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        if (null === $this->paginator) {
            throw new \Exception('Paginator is only available after calling "loadData()"');
        }

        return $this->paginator;
    }

    /**
     * @return array
     */
    protected function getPreparedData(): array
    {
        return $this->preparedData;
    }

    /**
     * Set the toolbar view template.
     *
     * @param null|string $name
     *
     * @return $this
     */
    public function setToolbarTemplate(?string $name): self
    {
        $this->toolbarTemplate = $name;

        return $this;
    }

    /**
     * Get the toolbar template name
     * Return null if nothing custom set.
     *
     * @return null|string
     */
    public function getToolbarTemplate(): ?string
    {
        return $this->toolbarTemplate;
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
     * Set a custom ViewModel...generally NOT necessary!
     *
     * @param ViewModel $viewModel
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setViewModel(ViewModel $viewModel): self
    {
        if (null !== $this->viewModel) {
            throw new \Exception(
                'A viewModel is already set. Did you already called ' .
                '$grid->render() or $grid->getViewModel() before?'
            );
        }

        $this->viewModel = $viewModel;

        return $this;
    }

    /**
     * @return ViewModel
     */
    public function getViewModel(): ViewModel
    {
        if (null === $this->viewModel) {
            $this->viewModel = new ViewModel();
        }

        return $this->viewModel;
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface|\Zend\Http\Response\Stream|\Zend\View\Model\ViewModel
     */
    public function getResponse()
    {
        if (! $this->isRendered()) {
            $this->render();
        }

        return $this->response;
    }

    /**
     * Is this a HTML "init" response?
     * YES: loading the HTML for the grid
     * NO: AJAX loading OR it's an export.
     *
     * @return bool
     */
    public function isHtmlInitReponse(): bool
    {
        return ! $this->getResponse() instanceof JsonModel && ! $this->getResponse() instanceof ResponseInterface;
    }

    /**
     * @param Renderer\AbstractRenderer $rendererService
     *
     * @return self
     */
    public function setRendererService(Renderer\AbstractRenderer $rendererService)
    {
        $this->rendererService = $rendererService;

        return $this;
    }
}
