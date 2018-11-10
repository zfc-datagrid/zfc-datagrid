<?php
namespace ZfcDatagrid\Column\Formatter;

use Zend\Router\RouteStackInterface;
use ZfcDatagrid\Column\AbstractColumn;
use function implode;
use function strpos;
use function sprintf;
use function str_replace;
use function rawurlencode;

class HtmlTag extends AbstractFormatter implements RouterInterface
{
    const ROW_ID_PLACEHOLDER = ':rowId:';

    /** @var string[] */
    protected $validRenderers = [
        'jqGrid',
        'bootstrapTable',
    ];

    /** @var string */
    protected $name = 'span';

    /** @var AbstractColumn[] */
    protected $linkColumnPlaceholders = [];

    /** @var array */
    protected $attributes = [];

    /** @var string */
    protected $route = '';

    /** @var array */
    protected $routeParams = [];

    /** @var RouteStackInterface */
    public $router;

    /**
     * @param RouteStackInterface $router
     *
     * @return void
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return null|RouteStackInterface
     */
    public function getRouter(): ?RouteStackInterface
    {
        return $this->router;
    }

    /**
     * @param $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set a HTML attributes.
     *
     * @param string $name
     * @param string $value
     */
    public function setAttribute(string $name, string $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Get a HTML attribute.
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttribute(string $name): string
    {
        return $this->attributes[$name] ?? '';
    }

    /**
     * Removes an HTML attribute.
     *
     * @param string $name
     */
    public function removeAttribute(string $name)
    {
        unset($this->attributes[$name]);
    }

    /**
     * Get all HTML attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the link.
     *
     * @param string $href
     */
    public function setLink(string $href)
    {
        $this->setAttribute('href', $href);
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->getAttribute('href');
    }

    /**
     * @param string $route
     */
    public function setRoute(string $route)
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param array $params
     */
    public function setRouteParams(array $params)
    {
        $this->routeParams = $params;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * Get the column row value placeholder
     * $fmt->setLink('/myLink/something/'.$fmt->getColumnValuePlaceholder($myCol));.
     *
     * @param AbstractColumn $col
     *
     * @return string
     */
    public function getColumnValuePlaceholder(AbstractColumn $col)
    {
        $this->linkColumnPlaceholders[] = $col;

        return ':' . $col->getUniqueId() . ':';
    }

    /**
     * @return AbstractColumn[]
     */
    public function getLinkColumnPlaceholders(): array
    {
        return $this->linkColumnPlaceholders;
    }

    /**
     * Returns the rowId placeholder.
     *
     * @return string
     */
    public function getRowIdPlaceholder(): string
    {
        return self::ROW_ID_PLACEHOLDER;
    }

    /**
     * @param AbstractColumn $col
     *
     * @return string
     */
    public function getFormattedValue(AbstractColumn $col): string
    {
        $row = $this->getRowData();

        return sprintf(
            '<%s %s>%s</%s>',
            $this->getName(),
            $this->getAttributesString($col),
            $row[$col->getUniqueId()],
            $this->getName()
        );
    }

    /**
     * Get the string version of the attributes.
     *
     * @param AbstractColumn $col
     *
     * @return string
     */
    protected function getAttributesString(AbstractColumn $col): string
    {
        $attributes = [];

        if ($this->getRoute() && $this->getRouter() instanceof RouteStackInterface) {
            $this->setLink($this->getRouter()->assemble(
                $this->getRouteParams(),
                ['name' => $this->getRoute()]
            ));
        }

        foreach ($this->getAttributes() as $attrKey => $attrValue) {
            if ('href' === $attrKey) {
                $attrValue = $this->getLinkReplaced($col);
            }
            $attributes[] = $attrKey . '="' . $attrValue . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * This is needed public for rowClickAction...
     *
     * @param AbstractColumn $col
     *
     * @return string
     */
    protected function getLinkReplaced(AbstractColumn $col): string
    {
        $row = $this->getRowData();

        $link = $this->getLink();
        if ($link == '') {
            return $row[$col->getUniqueId()];
        }

        // Replace placeholders
        if (strpos($link, self::ROW_ID_PLACEHOLDER) !== false) {
            $link = str_replace(self::ROW_ID_PLACEHOLDER, rawurlencode($row['idConcated'] ?? ''), $link);
        }

        foreach ($this->getLinkColumnPlaceholders() as $col) {
            $link = str_replace(':' . $col->getUniqueId() . ':', rawurlencode($row[$col->getUniqueId()]), $link);
        }

        return $link;
    }
}
