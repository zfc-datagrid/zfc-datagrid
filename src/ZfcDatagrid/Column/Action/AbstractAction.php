<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Action;

use InvalidArgumentException;
use ZfcDatagrid\Column;
use ZfcDatagrid\Filter;

use function implode;
use function str_replace;
use function strpos;

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

    public function __construct()
    {
        $this->setLink('#');
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
     */
    public function getRowIdPlaceholder(): string
    {
        return self::ROW_ID_PLACEHOLDER;
    }

    /**
     * Set a HTML attributes.
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
     * @return string
     */
    public function getAttribute(string $name)
    {
        return $this->htmlAttributes[$name] ?? '';
    }

    /**
     * Removes an HTML attribute.
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
     */
    protected function getAttributesString(array $row): string
    {
        $attributes = [];
        foreach ($this->getAttributes() as $attrKey => $attrValue) {
            if ('href' === $attrKey) {
                $attrValue = $this->getLinkReplaced($row);
            }
            $attributes[] = $attrKey . '="' . $attrValue . '"';
        }

        return implode(' ', $attributes);
    }

    /**
     * Set the title attribute.
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
     */
    public function getTitle(): string
    {
        return $this->getAttribute('title');
    }

    /**
     * Add a css class.
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
     * @return $this
     */
    public function setShowOnValueOperator(string $operator = 'OR'): self
    {
        if ($operator != 'AND' && $operator != 'OR') {
            throw new InvalidArgumentException('not allowed operator: "' . $operator . '" (AND / OR is allowed)');
        }

        $this->showOnValueOperator = (string) $operator;

        return $this;
    }

    /**
     * Get the show on value operator, e.g.
     * OR, AND.
     */
    public function getShowOnValueOperator(): string
    {
        return $this->showOnValueOperator;
    }

    /**
     * Show this action only on the values defined.
     *
     * @param Column\AbstractColumn|string $value
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

    public function hasShowOnValues(): bool
    {
        return ! empty($this->showOnValues);
    }

    /**
     * Display this action on this row?
     *
     * @param array $row
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
     */
    abstract protected function getHtmlType(): string;

    /**
     * @param array $row
     */
    public function toHtml(array $row): string
    {
        return '<a ' . $this->getAttributesString($row) . '>' . $this->getHtmlType() . '</a>';
    }
}
