<?php
namespace ZfcDatagrid\Column\Action;

use Laminas\I18n\Translator\TranslatorInterface;
use ZfcDatagrid\Column;
use ZfcDatagrid\Filter;
use function strpos;
use function str_replace;
use function implode;

abstract class AbstractAction
{
    const ROW_ID_PLACEHOLDER = ':rowId:';

    /** @var Column\AbstractColumn[] */
    protected $linkColumnPlaceholders = [];

    /** @var array */
    protected $htmlAttributes = [];

    /** @var string */
    protected $showOnValueOperator = 'OR';

    /** @var string */
    protected $route = '';

    /** @var array */
    protected $routeParams = [];

    /** @var array */
    protected $showOnValues = [];

    /**
     * AbstractAction constructor.
     */
    public function __construct()
    {
        $this->setLink('#');
    }

    /**
     * Set the link.
     *
     * @param string $href
     *
     * @return $this
     */
    public function setLink(string $href): self
    {
        $this->setAttribute('href', $href);

        return $this;
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
     *
     * @return $this
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
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
     *
     * @return $this
     */
    public function setRouteParams(array $params)
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
     * This is needed public for rowClickAction...
     *
     * @param array $row
     *
     * @return string
     */
    public function getLinkReplaced(array $row): string
    {
        $link = $this->getLink();

        // Replace placeholders
        if (strpos($this->getLink(), self::ROW_ID_PLACEHOLDER) !== false) {
            $link = str_replace(self::ROW_ID_PLACEHOLDER, $row['idConcated'] ?? '', $link);
        }

        foreach ($this->getLinkColumnPlaceholders() as $col) {
            $link = str_replace(':' . $col->getUniqueId() . ':', $row[$col->getUniqueId()], $link);
        }

        return $link;
    }

    /**
     * Get the column row value placeholder
     * $action->setLink('/myLink/something/id/'.$action->getRowIdPlaceholder().'/something/'.$action->getColumnRowPlaceholder($myCol));.
     *
     * @param Column\AbstractColumn $col
     *
     * @return string
     */
    public function getColumnValuePlaceholder(Column\AbstractColumn $col): string
    {
        $this->linkColumnPlaceholders[] = $col;

        return ':' . $col->getUniqueId() . ':';
    }

    /**
     * @return Column\AbstractColumn[]
     */
    public function getLinkColumnPlaceholders(): array
    {
        return $this->linkColumnPlaceholders;
    }

    /**
     * Returns the rowId placeholder
     * Can be used e.g.
     * $action->setLink('/myLink/something/id/'.$action->getRowIdPlaceholder());.
     *
     * @return string
     */
    public function getRowIdPlaceholder(): string
    {
        return self::ROW_ID_PLACEHOLDER;
    }

    /**
     * Set a HTML attributes.
     *
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute(string $name, string $value): self
    {
        $this->htmlAttributes[$name] = $value;

        return $this;
    }

    /**
     * Get a HTML attribute.
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttribute(string $name)
    {
        return $this->htmlAttributes[$name] ?? '';
    }

    /**
     * Removes an HTML attribute.
     *
     * @param string $name
     *
     * @return $this
     */
    public function removeAttribute(string $name): self
    {
        unset($this->htmlAttributes[$name]);

        return $this;
    }

    /**
     * Get all HTML attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->htmlAttributes;
    }

    /**
     * Get the string version of the attributes.
     *
     * @param array $row
     *
     * @return string
     */
    protected function getAttributesString(array $row): string
    {
        $attributes = [];

        foreach ($this->getAttributes() as $attrKey => $attrValue) {
            if ('href' === $attrKey) {
                $attrValue = $this->getLinkReplaced($row);
            } else {
                foreach ($this->getLinkColumnPlaceholders() as $col) {
                    $attrValue = str_replace(':' . $col->getUniqueId() . ':', $row[$col->getUniqueId()], $attrValue);
                }
            }

            $attributes[] = $attrKey . '="' . $attrValue . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Set the title attribute.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setTitle(string $name): self
    {
        $this->setAttribute('title', $name);

        return $this;
    }

    /**
     * Get the title attribute.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getAttribute('title');
    }

    /**
     * Add a css class.
     *
     * @param string $className
     *
     * @return $this
     */
    public function addClass(string $className): self
    {
        $attr = $this->getAttribute('class');
        if ($attr != '') {
            $attr .= ' ';
        }
        $attr .= (string) $className;

        $this->setAttribute('class', $attr);

        return $this;
    }

    /**
     * Display the values with AND or OR (if multiple showOnValues are defined).
     *
     * @param string $operator
     *
     * @return $this
     */
    public function setShowOnValueOperator(string $operator = 'OR'): self
    {
        if ($operator != 'AND' && $operator != 'OR') {
            throw new \InvalidArgumentException('not allowed operator: "' . $operator . '" (AND / OR is allowed)');
        }

        $this->showOnValueOperator = (string) $operator;

        return $this;
    }

    /**
     * Get the show on value operator, e.g.
     * OR, AND.
     *
     * @return string
     */
    public function getShowOnValueOperator(): string
    {
        return $this->showOnValueOperator;
    }

    /**
     * Show this action only on the values defined.
     *
     * @param Column\AbstractColumn        $col
     * @param Column\AbstractColumn|string $value
     * @param string                       $comparison
     *
     * @return $this
     */
    public function addShowOnValue(Column\AbstractColumn $col, $value = null, string $comparison = Filter::EQUAL): self
    {
        $this->showOnValues[] = [
            'column'     => $col,
            'value'      => $value,
            'comparison' => $comparison,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getShowOnValues(): array
    {
        return $this->showOnValues;
    }

    /**
     * @return bool
     */
    public function hasShowOnValues(): bool
    {
        return !empty($this->showOnValues);
    }

    /**
     * Display this action on this row?
     *
     * @param array $row
     *
     * @return bool
     */
    public function isDisplayed(array $row): bool
    {
        if (false === $this->hasShowOnValues()) {
            return true;
        }

        $isDisplayed = false;
        foreach ($this->getShowOnValues() as $rule) {
            $value = $row[$rule['column']->getUniqueId()] ?? '';

            if ($rule['value'] instanceof Column\AbstractColumn) {
                $ruleValue = $row[$rule['value']->getUniqueId()] ?? '';
            } else {
                $ruleValue = $rule['value'];
            }

            $isDisplayedMatch = Filter::isApply($value, $ruleValue, $rule['comparison']);
            if ($this->getShowOnValueOperator() == 'OR' && true === $isDisplayedMatch) {
                // For OR one match is enough
                return true;
            } elseif ($this->getShowOnValueOperator() == 'AND' && false === $isDisplayedMatch) {
                return false;
            } else {
                $isDisplayed = $isDisplayedMatch;
            }
        }

        return $isDisplayed;
    }

    /**
     * Get the HTML from the type.
     *
     * @return string
     */
    abstract protected function getHtmlType(): string;

    public function toHtml(array $row, ?TranslatorInterface $translator): string
    {
        return '<a ' . $this->getAttributesString($row) . '>' . $this->getHtmlType() . '</a>';
    }
}
