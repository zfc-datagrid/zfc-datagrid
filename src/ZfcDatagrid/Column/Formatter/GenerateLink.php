<?php
namespace ZfcDatagrid\Column\Formatter;

use Zend\View\Renderer\RendererInterface;
use ZfcDatagrid\Column\AbstractColumn;

class GenerateLink extends AbstractFormatter
{
    /** @var string[] */
    protected $validRenderers = [
        'jqGrid',
        'bootstrapTable',
    ];

    /** @var string */
    protected $route = '';

    /** @var array */
    protected $routeParams = [];

    /** @var string|null */
    protected $routeKey;

    /** @var RendererInterface */
    protected $viewRenderer;

    /**
     * @param RendererInterface $viewRenderer
     * @param string            $route
     * @param null|string       $key
     * @param array             $params
     */
    public function __construct(RendererInterface $viewRenderer, string $route, ?string $key = null, array $params = [])
    {
        $this->setViewRenderer($viewRenderer);
        $this->setRoute($route);
        $this->setRouteParams($params);
        $this->setRouteKey($key);
    }

    /**
     * @param AbstractColumn $column
     *
     * @return string
     */
    public function getFormattedValue(AbstractColumn $column): string
    {
        $row = $this->getRowData();
        $value = $row[$column->getUniqueId()];
        $params = $this->routeParams;
        $params[$this->routeKey ?? $column->getUniqueId()] = $value;

        $url = (string) $this->viewRenderer->url($this->route, $params);

        return sprintf('<a href="%s">%s</a>', $url, $value);
    }

    /**
     * @param RendererInterface $viewRenderer
     *
     * @return self
     */
    public function setViewRenderer(RendererInterface $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer;

        return $this;
    }

    /**
     * @param string $route
     */
    public function setRoute(string $route)
    {
        $this->route = $route;
    }

    /**
     * @param array $routeParams
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;
    }

    /**
     * @param null|string $routeKey
     */
    public function setRouteKey(?string $routeKey)
    {
        $this->routeKey = $routeKey;
    }
}
