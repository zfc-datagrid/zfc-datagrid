<?php
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

    /**
     * @var AbstractFormatter[]
     */
    protected $formatters = [];

    /**
     * @param string $name
     */
    public function setLabel(string $name)
    {
        $this->label = (string) $name;
    }

    /**
     * Get the label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param $id
     */
    public function setUniqueId($id)
    {
        $this->uniqueId = $id;
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return int|null
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @param int|null $position
     * @return AbstractColumn
     */
    public function setPosition(?int $position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set the width in "percent"
     * It will be calculated to 100% dependend on what is displayed
     * If it's a different output mode like Excel it's dependend on the papersize/orientation.
     *
     * @param float $percent
     */
    public function setWidth(float $percent)
    {
        $this->width = (float) $percent;
    }

    /**
     * Get the width.
     *
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * Hide or show the column.
     *
     * @param bool $mode
     */
    public function setHidden(bool $mode = true)
    {
        $this->isHidden = $mode;
    }

    /**
     * Is this column hidden?
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * Set this column as primaryKey column.
     *
     * @param bool $mode
     */
    public function setIdentity(bool $mode = true)
    {
        $this->isIdentity = $mode;

        // Because IDs are normally hidden
        $this->setHidden($mode);
    }

    /**
     * Is this a primaryKey column?
     *
     * @return bool
     */
    public function isIdentity(): bool
    {
        return $this->isIdentity;
    }

    /**
     * Set the column type.
     *
     * @param Type\AbstractType $type
     */
    public function setType(Type\AbstractType $type)
    {
        if ($type instanceof Type\Image && $this->hasFormatters() === false) {
            $this->addFormatter(new Formatter\Image());
            $this->setRowClickDisabled(true);
        }

        $this->type = $type;
    }

    /**
     * @return Type\AbstractType
     */
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
     * @param array $styles
     */
    public function setStyles(array $styles)
    {
        $this->styles = [];

        foreach ($styles as $style) {
            $this->addStyle($style);
        }
    }

    /**
     * @param Style\AbstractStyle $style
     */
    public function addStyle(Style\AbstractStyle $style)
    {
        $this->styles[] = $style;
    }

    /**
     * @return Style\AbstractStyle[]
     */
    public function getStyles(): array
    {
        return $this->styles;
    }

    /**
     * @return bool
     */
    public function hasStyles(): bool
    {
        return !empty($this->styles);
    }

    /**
     * Is the user allowed to do sort on this column?
     *
     * @param bool $mode
     */
    public function setUserSortDisabled(bool $mode = true)
    {
        $this->userSortEnabled = ! $mode;
    }

    /**
     * Is user sort enabled?
     *
     * @return bool
     */
    public function isUserSortEnabled(): bool
    {
        return $this->userSortEnabled;
    }

    /**
     * The data will get sorted by this column (by default)
     * If will be changed by the user per request (POST,GET....).
     *
     * @param int    $priority
     * @param string $direction
     */
    public function setSortDefault(int $priority = 1, string $direction = 'ASC')
    {
        $this->sortDefault = [
            'priority'      => $priority,
            'sortDirection' => $direction,
        ];
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
     *
     * @return bool
     */
    public function hasSortDefault(): bool
    {
        return !empty($this->sortDefault);
    }

    /**
     * Set that the data is getting sorted by this columns.
     *
     * @param string $direction
     */
    public function setSortActive(string $direction = 'ASC')
    {
        $this->sortActive = $direction;
    }

    /**
     * @return bool
     */
    public function isSortActive(): bool
    {
        return null !== $this->sortActive;
    }

    /**
     * @return string
     */
    public function getSortActiveDirection(): ?string
    {
        return $this->sortActive;
    }

    /**
     * @param bool $mode
     */
    public function setUserFilterDisabled(bool $mode = true)
    {
        $this->userFilterEnabled = ! $mode;
    }

    /**
     * Set the default filterung value (used as long no user filtering getting applied)
     * Examples
     * $grid->setFilterDefaultValue('something');
     * $grid->setFilterDefaultValue('>20');.
     *
     * OPERATORS are ALLOWED (like for the user)
     *
     * @param string $value
     */
    public function setFilterDefaultValue(string $value)
    {
        if ($value != '') {
            $this->filterDefaultValue = (string) $value;
        }
    }

    /**
     * @return string|null
     */
    public function getFilterDefaultValue(): ?string
    {
        return $this->filterDefaultValue;
    }

    /**
     * @return bool
     */
    public function hasFilterDefaultValue(): bool
    {
        return $this->filterDefaultValue != '';
    }

    /**
     * @param string $operation
     */
    public function setFilterDefaultOperation(string $operation = Filter::LIKE)
    {
        $this->filterDefaultOperation = $operation;
    }

    /**
     * @return string
     */
    public function getFilterDefaultOperation(): string
    {
        if ($this->filterDefaultOperation != '') {
            return $this->filterDefaultOperation;
        }

        return $this->getType()->getFilterDefaultOperation();
    }

    /**
     * @param array $options
     * @param bool  $noSelect
     */
    public function  setFilterSelectOptions(array $options = null, bool $noSelect = true)
    {
        // This work also with options with integer based array index such as
        // array(0 => 'zero', 1 => 'once', 2 => 'double', 3 => 'triple'....)

        if (true === $noSelect) {
            $options = ['' => '-'] + $options;
            $this->setFilterDefaultValue('');
        }

        $this->filterSelectOptions = $options;
    }

    /**
     * @return array
     */
    public function getFilterSelectOptions(): array
    {
        return $this->filterSelectOptions;
    }

    /**
     * @return bool
     */
    public function hasFilterSelectOptions(): bool
    {
        return !empty($this->filterSelectOptions);
    }

    /**
     * @param mixed $value
     */
    public function setFilterActive(string $value = '')
    {
        $this->filterActive      = (bool) true;
        $this->filterActiveValue = $value;
    }

    /**
     * @return bool
     */
    public function isFilterActive(): bool
    {
        return $this->filterActive;
    }

    /**
     * @return string
     */
    public function getFilterActiveValue(): string
    {
        return $this->filterActiveValue;
    }

    /**
     * @return bool
     */
    public function isUserFilterEnabled(): bool
    {
        return (bool) $this->userFilterEnabled;
    }

    /**
     * Enable data translation.
     *
     * @param bool $mode
     */
    public function setTranslationEnabled(bool $mode = true)
    {
        $this->translationEnabled = (bool) $mode;
    }

    /**
     * Is data translation enabled?
     *
     * @return bool
     */
    public function isTranslationEnabled(): bool
    {
        return (bool) $this->translationEnabled;
    }

    /**
     * Replace the column values with the applied values.
     *
     * @param array $values
     * @param bool  $notReplacedGetEmpty
     */
    public function setReplaceValues(array $values, bool $notReplacedGetEmpty = true)
    {
        $this->replaceValues       = $values;
        $this->notReplacedGetEmpty = (bool) $notReplacedGetEmpty;

        $this->setFilterDefaultOperation(Filter::EQUAL);
        $this->setFilterSelectOptions($values);
    }

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function notReplacedGetEmpty(): bool
    {
        return $this->notReplacedGetEmpty;
    }

    /**
     * Set parameter for a specific renderer (currently only supported for jqGrid).
     *
     * @param string $name
     * @param mixed  $value
     * @param string $rendererType
     */
    public function setRendererParameter(string $name, $value, string $rendererType = 'jqGrid')
    {
        if (! isset($this->rendererParameter[$rendererType])) {
            $this->rendererParameter[$rendererType] = [];
        }

        $parameters        = $this->rendererParameter[$rendererType];
        $parameters[$name] = $value;

        $this->rendererParameter[$rendererType] = $parameters;
    }

    /**
     * @param string $rendererName
     *
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
     */
    public function setFormatters(array $formatters)
    {
        $this->formatters = $formatters;
    }

    /**
     * add a template formatter in the list.
     *
     * @param AbstractFormatter $formatter
     */
    public function addFormatter(AbstractFormatter $formatter)
    {
        $this->formatters[] = $formatter;
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

    /**
     * @return bool
     */
    public function hasFormatters(): bool
    {
        return !empty($this->formatters);
    }

    /**
     * @param bool $mode
     */
    public function setRowClickDisabled(bool $mode = true)
    {
        $this->rowClickEnabled = ! $mode;
    }

    /**
     * @return bool
     */
    public function isRowClickEnabled(): bool
    {
        return $this->rowClickEnabled;
    }
}
