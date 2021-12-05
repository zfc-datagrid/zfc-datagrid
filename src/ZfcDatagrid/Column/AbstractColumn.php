<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column;

use ZfcDatagrid\Column\Formatter\AbstractFormatter;
use ZfcDatagrid\Filter;

abstract class AbstractColumn
{
    /** @var string */
    protected $label = '';

    /** @var null|string */
    protected $uniqueId;

    /** @var int|null */
    protected $position;

    /** @var Type\AbstractType|null */
    protected $type;

    /** @var Style\AbstractStyle[] */
    protected $styles = [];

    /** @var float */
    protected $width = 5.;

    /** @var bool */
    protected $isHidden = false;

    /** @var bool */
    protected $isHide = false;

    /** @var bool */
    protected $isIdentity = false;

    /** @var bool */
    protected $userSortEnabled = true;

    /** @var array */
    protected $sortDefault = [];

    protected $sortActive;

    /** @var string|null */
    protected $filterDefaultValue;

    protected $filterDefaultOperation;

    /** @var array */
    protected $filterSelectOptions = [];

    protected $filterActive = false;

    protected $filterActiveValue = '';

    protected $userFilterEnabled = true;

    protected $translationEnabled = false;

    protected $replaceValues = [];

    protected $notReplacedGetEmpty = true;

    protected $rowClickEnabled = true;

    protected $rendererParameter = [];

    /** @var AbstractFormatter[] */
    protected $formatters = [];

    /**
     * @return $this
     */
    public function setLabel(string $name): self
    {
        $this->label = (string) $name;

        return $this;
    }

    /**
     * Get the label.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setUniqueId($id): self
    {
        $this->uniqueId = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @return $this
     */
    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set the width in "percent"
     * It will be calculated to 100% dependend on what is displayed
     * If it's a different output mode like Excel it's dependend on the papersize/orientation.
     *
     * @return $this
     */
    public function setWidth(float $percent): self
    {
        $this->width = (float) $percent;

        return $this;
    }

    /**
     * Get the width.
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * Hide or show the column.
     *
     * @return $this
     */
    public function setHidden(bool $mode = true): self
    {
        $this->isHidden = $mode;

        return $this;
    }

    /**
     * Is this column hidden?
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @param bool $isHide
     * @return $this
     */
    public function setHide(bool $hide): self
    {
        $this->isHide = $hide;
        return $this;
    }

    /**
     * Set this column as primaryKey column.
     *
     * @return $this
     */
    public function setIdentity(bool $mode = true): self
    {
        $this->isIdentity = $mode;

        // Because IDs are normally hidden
        $this->setHidden($mode);

        return $this;
    }

    /**
     * Is this a primaryKey column?
     */
    public function isIdentity(): bool
    {
        return $this->isIdentity;
    }

    /**
     * Set the column type.
     *
     * @return $this
     */
    public function setType(Type\AbstractType $type): self
    {
        if ($type instanceof Type\Image && $this->hasFormatters() === false) {
            $this->addFormatter(new Formatter\Image());
            $this->setRowClickDisabled(true);
        }

        $this->type = $type;

        return $this;
    }

    public function getType(): Type\AbstractType
    {
        if (null === $this->type) {
            $this->type = new Type\PhpString();
        }

        return $this->type;
    }

    /**
     * Set styles.
     *
     * @param Style\AbstractStyle[] $styles
     * @return $this
     */
    public function setStyles(array $styles): self
    {
        $this->styles = [];

        foreach ($styles as $style) {
            $this->addStyle($style);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function addStyle(Style\AbstractStyle $style): self
    {
        $this->styles[] = $style;

        return $this;
    }

    /**
     * @return Style\AbstractStyle[]
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    public function hasStyles(): bool
    {
        return ! empty($this->styles);
    }

    /**
     * Is the user allowed to do sort on this column?
     *
     * @return $this
     */
    public function setUserSortDisabled(bool $mode = true): self
    {
        $this->userSortEnabled = ! $mode;

        return $this;
    }

    /**
     * Is user sort enabled?
     */
    public function isUserSortEnabled(): bool
    {
        return $this->userSortEnabled;
    }

    /**
     * The data will get sorted by this column (by default)
     * If will be changed by the user per request (POST,GET....).
     *
     * @return $this
     */
    public function setSortDefault(int $priority = 1, string $direction = 'ASC'): self
    {
        $this->sortDefault = [
            'priority'      => $priority,
            'sortDirection' => $direction,
        ];

        return $this;
    }

    /**
     * Get the sort defaults.
     *
     * @return array
     */
    public function getSortDefault(): array
    {
        return $this->sortDefault;
    }

    /**
     * Does this column has sort defaults?
     */
    public function hasSortDefault(): bool
    {
        return ! empty($this->sortDefault);
    }

    /**
     * Set that the data is getting sorted by this columns.
     *
     * @return $this
     */
    public function setSortActive(string $direction = 'ASC'): self
    {
        $this->sortActive = $direction;

        return $this;
    }

    public function isSortActive(): bool
    {
        return null !== $this->sortActive;
    }

    public function getSortActiveDirection(): ?string
    {
        return $this->sortActive;
    }

    /**
     * @return $this
     */
    public function setUserFilterDisabled(bool $mode = true): self
    {
        $this->userFilterEnabled = ! $mode;

        return $this;
    }

    /**
     * Set the default filterung value (used as long no user filtering getting applied)
     * Examples
     * $grid->setFilterDefaultValue('something');
     * $grid->setFilterDefaultValue('>20');.
     *
     * OPERATORS are ALLOWED (like for the user)
     *
     * @return $this
     */
    public function setFilterDefaultValue(string $value): self
    {
        if ($value != '') {
            $this->filterDefaultValue = (string) $value;
        }

        return $this;
    }

    public function getFilterDefaultValue(): ?string
    {
        return $this->filterDefaultValue;
    }

    public function hasFilterDefaultValue(): bool
    {
        return $this->filterDefaultValue != '';
    }

    /**
     * @return $this
     */
    public function setFilterDefaultOperation(string $operation = Filter::LIKE): self
    {
        $this->filterDefaultOperation = $operation;

        return $this;
    }

    public function getFilterDefaultOperation(): string
    {
        if ($this->filterDefaultOperation != '') {
            return $this->filterDefaultOperation;
        }

        return $this->getType()->getFilterDefaultOperation();
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setFilterSelectOptions(?array $options = null, bool $noSelect = true): self
    {
        // This work also with options with integer based array index such as
        // array(0 => 'zero', 1 => 'once', 2 => 'double', 3 => 'triple'....)

        if (true === $noSelect) {
            $options = ['' => '-'] + $options;
            $this->setFilterDefaultValue('');
        }

        $this->filterSelectOptions = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilterSelectOptions(): array
    {
        return $this->filterSelectOptions;
    }

    public function hasFilterSelectOptions(): bool
    {
        return ! empty($this->filterSelectOptions);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFilterActive(string $value = ''): self
    {
        $this->filterActive      = (bool) true;
        $this->filterActiveValue = $value;

        return $this;
    }

    public function isFilterActive(): bool
    {
        return $this->filterActive;
    }

    public function getFilterActiveValue(): string
    {
        return $this->filterActiveValue;
    }

    public function isUserFilterEnabled(): bool
    {
        return (bool) $this->userFilterEnabled;
    }

    /**
     * Enable data translation.
     *
     * @return $this
     */
    public function setTranslationEnabled(bool $mode = true): self
    {
        $this->translationEnabled = (bool) $mode;

        return $this;
    }

    /**
     * Is data translation enabled?
     */
    public function isTranslationEnabled(): bool
    {
        return (bool) $this->translationEnabled;
    }

    /**
     * Replace the column values with the applied values.
     *
     * @param array $values
     * @return $this
     */
    public function setReplaceValues(array $values, bool $notReplacedGetEmpty = true): self
    {
        $this->replaceValues       = $values;
        $this->notReplacedGetEmpty = (bool) $notReplacedGetEmpty;

        $this->setFilterDefaultOperation(Filter::EQUAL);
        $this->setFilterSelectOptions($values);

        return $this;
    }

    public function hasReplaceValues(): bool
    {
        return $this->replaceValues ? true : false;
    }

    /**
     * @return array
     */
    public function getReplaceValues(): array
    {
        return $this->replaceValues;
    }

    public function notReplacedGetEmpty(): bool
    {
        return $this->notReplacedGetEmpty;
    }

    /**
     * Set parameter for a specific renderer (currently only supported for jqGrid).
     *
     * @param mixed  $value
     * @return $this
     */
    public function setRendererParameter(string $name, $value, string $rendererType = 'jqGrid'): self
    {
        if (! isset($this->rendererParameter[$rendererType])) {
            $this->rendererParameter[$rendererType] = [];
        }

        $parameters        = $this->rendererParameter[$rendererType];
        $parameters[$name] = $value;

        $this->rendererParameter[$rendererType] = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getRendererParameters(string $rendererName = 'jqGrid')
    {
        if (! isset($this->rendererParameter[$rendererName])) {
            $this->rendererParameter[$rendererName] = [];
        }

        return $this->rendererParameter[$rendererName];
    }

    /**
     * Set a template formatter and overwrite other formatter.
     *
     * @param AbstractFormatter[] $formatters
     * @return $this
     */
    public function setFormatters(array $formatters): self
    {
        $this->formatters = $formatters;

        return $this;
    }

    /**
     * add a template formatter in the list.
     *
     * @return $this
     */
    public function addFormatter(AbstractFormatter $formatter): self
    {
        $this->formatters[] = $formatter;

        return $this;
    }

    /**
     * return a list of different formatter.
     *
     * @return AbstractFormatter[]
     */
    public function getFormatters(): array
    {
        return $this->formatters;
    }

    public function hasFormatters(): bool
    {
        return ! empty($this->formatters);
    }

    /**
     * @return $this
     */
    public function setRowClickDisabled(bool $mode = true): self
    {
        $this->rowClickEnabled = ! $mode;

        return $this;
    }

    public function isRowClickEnabled(): bool
    {
        return $this->rowClickEnabled;
    }
}
