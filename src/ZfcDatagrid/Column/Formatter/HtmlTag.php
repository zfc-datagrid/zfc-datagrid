<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Formatter;

use Laminas\Router\RouteStackInterface;
use ZfcDatagrid\Column\AbstractColumn;

use function implode;
use function rawurlencode;
use function sprintf;
use function str_replace;
use function strpos;

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
     * @return $this
     */
    public function setRouter(RouteStackInterface $router): RouterInterface
    {
        $this->router = $router;

        return $this;
    }

    public function getRouter(): ?RouteStackInterface
    {
        return $this->router;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set a HTML attributes.
     *
     * @return $this
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Get a HTML attribute.
     */
    public function getAttribute(string $name): string
    {
        return $this->attributes[$name] ?? '';
    }

    /**
     * Removes an HTML attribute.
     *
     * @return $this
     */
    public function removeAttribute(string $name): self
    {
        unset($this->attributes[$name]);

        return $this;
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
     * @return $this
     */
    public function setLink(string $href): self
    {
        $this->setAttribute('href', $href);

        return $this;
    }

    public function getLink(): string
    {
        return $this->getAttribute('href');
    }

    /**
     * @return $this
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setRouteParams(array $params): self
    {
        $this->routeParams = $params;

        return $this;
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
     */
    public function getRowIdPlaceholder(): string
    {
        return self::ROW_ID_PLACEHOLDER;
    }

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
