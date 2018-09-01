<?php

namespace ZfcDatagrid\Column;

use ZfcDatagrid\Column\Formatter\AbstractFormatter;
use ZfcDatagrid\Filter;

/**
 * Class AbstractColumn
 *
 * @package ZfcDatagrid\Column
 */
abstract class AbstractColumn
{
    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var
     */
    protected $uniqueId;

    /**
     * @var Type\AbstractType
     */
    protected $type = null;

    /**
     * @var array
     */
    protected $styles = [];

    /**
     * @var int
     */
    protected $width = 5;

    /**
     * @var bool
     */
    protected $isHidden = false;

    /**
     * @var bool
     */
    protected $isIdentity = false;

    /**
     * @var bool
     */
    protected $userSortEnabled = true;

    /**
     * @var array
     */
    protected $sortDefault = [];

    /**
     * @var string|null
     */
    protected $sortActive;

    /**
     * @var string|null
     */
    protected $filterDefaultValue;

    /**
     * @var string|null
     */
    protected $filterDefaultOperation;

    /**
     * @var null array
     */
    protected $filterSelectOptions;

    /**
     * @var bool|null
     */
    protected $filterActive;

    /**
     * @var string
     */
    protected $filterActiveValue = '';

    /**
     * @var bool
     */
    protected $userFilterEnabled = true;

    /**
     * @var bool
     */
    protected $translationEnabled = false;

    /**
     * @var array
     */
    protected $replaceValues = [];

    /**
     * @var bool
     */
    protected $notReplacedGetEmpty = true;

    /**
     * @var bool
     */
    protected $rowClickEnabled = true;

    /**
     * @var array
     */
    protected $rendererParameter = [];

    /**
     * @var AbstractFormatter[]
     */
    protected $formatters = [];

    /**
     * @param $name
     *
     * @return $this
     */
    public function setLabel($name)
    {
        $this->label = (string) $name;

        return $this;
    }

    /**
     * Get the label.
     *
     * @return string null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setUniqueId($id)
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

    /**
     * Set the width in "percent"
     * It will be calculated to 100% dependend on what is displayed
     * If it's a different output mode like Excel it's dependend on the papersize/orientation.
     *
     * @param number $percent
     *
     * @return $this
     */
    public function setWidth($percent)
    {
        $this->width = (float) $percent;

        return $this;
    }

    /**
     * Get the width.
     *
     * @return number
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Hide or show the column.
     *
     * @param bool $mode
     *
     * @return $this
     */
    public function setHidden($mode = true)
    {
        $this->isHidden = (bool) $mode;

        return $this;
    }

    /**
     * Is this column hidden?
     *
     * @return bool
     */
    public function isHidden()
    {
        return (bool) $this->isHidden;
    }

    /**
     * Set this column as primaryKey column.
     *
     * @param bool $mode
     *
     * @return $this
     */
    public function setIdentity($mode = true)
    {
        $this->isIdentity = (bool) $mode;

        // Because IDs are normally hidden
        $this->setHidden($mode);

        return $this;
    }

    /**
     * Is this a primaryKey column?
     *
     * @return bool
     */
    public function isIdentity()
    {
        return (bool) $this->isIdentity;
    }

    /**
     * Set the column type.
     *
     * @param Type\AbstractType $type
     *
     * @return $this
     */
    public function setType(Type\AbstractType $type)
    {
        if ($type instanceof Type\Image && $this->hasFormatters() === false) {
            $this->addFormatter(new Formatter\Image());
            $this->setRowClickDisabled(true);
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @return Type\AbstractType
     */
    public function getType()
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
     *
     * @return $this
     */
    public function setStyles(array $styles)
    {
        $this->styles = [];

        foreach ($styles as $style) {
            $this->addStyle($style);
        }

        return $this;
    }

    /**
     * @param Style\AbstractStyle $style
     *
     * @return $this
     */
    public function addStyle(Style\AbstractStyle $style)
    {
        $this->styles[] = $style;

        return $this;
    }

    /**
     * @return Style\AbstractStyle[]
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @return bool
     */
    public function hasStyles()
    {
        if (count($this->styles) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Is the user allowed to do sort on this column?
     *
     * @param bool $mode
     *
     * @return $this
     */
    public function setUserSortDisabled($mode = true)
    {
        $this->userSortEnabled = (bool) ! $mode;

        return $this;
    }

    /**
     * Is user sort enabled?
     *
     * @return bool
     */
    public function isUserSortEnabled()
    {
        return (bool) $this->userSortEnabled;
    }

    /**
     * The data will get sorted by this column (by default)
     * If will be changed by the user per request (POST,GET....).
     *
     * @param int    $priority
     * @param string $direction
     *
     * @return $this
     */
    public function setSortDefault($priority = 1, $direction = 'ASC')
    {
        $this->sortDefault = [
            'priority' => $priority,
            'sortDirection' => $direction,
        ];

        return $this;
    }

    /**
     * Get the sort defaults.
     *
     * @return array
     */
    public function getSortDefault()
    {
        return $this->sortDefault;
    }

    /**
     * Does this column has sort defaults?
     *
     * @return bool
     */
    public function hasSortDefault()
    {
        if (count($this->sortDefault) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Set that the data is getting sorted by this columns.
     *
     * @param string $direction
     *
     * @return $this
     */
    public function setSortActive($direction = 'ASC')
    {
        $this->sortActive = $direction;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSortActive()
    {
        if ($this->sortActive !== null) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSortActiveDirection()
    {
        return $this->sortActive;
    }

    /**
     * @param bool $mode
     *
     * @return $this
     */
    public function setUserFilterDisabled($mode = true)
    {
        $this->userFilterEnabled = (bool) ! $mode;

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
     * @param string $value
     *
     * @return $this
     */
    public function setFilterDefaultValue($value = null)
    {
        if ($value != '') {
            $this->filterDefaultValue = (string) $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterDefaultValue()
    {
        return $this->filterDefaultValue;
    }

    /**
     * @return bool
     */
    public function hasFilterDefaultValue()
    {
        if ($this->filterDefaultValue != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $operation
     *
     * @return $this
     */
    public function setFilterDefaultOperation($operation = Filter::LIKE)
    {
        $this->filterDefaultOperation = $operation;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilterDefaultOperation()
    {
        if ($this->filterDefaultOperation != '') {
            return $this->filterDefaultOperation;
        }

        return $this->getType()->getFilterDefaultOperation();
    }

    /**
     * @param array $options
     * @param bool  $noSelect
     *
     * @return $this
     */
    public function setFilterSelectOptions(array $options = null, $noSelect = true)
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
     * Unset the filter select options (normal search).
     */
    public function unsetFilterSelectOptions()
    {
        $this->filterSelectOptions = null;
    }

    /**
     * @return array null
     */
    public function getFilterSelectOptions()
    {
        return $this->filterSelectOptions;
    }

    /**
     * @return bool
     */
    public function hasFilterSelectOptions()
    {
        if (is_array($this->filterSelectOptions)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setFilterActive($value = '')
    {
        $this->filterActive = (bool) true;
        $this->filterActiveValue = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFilterActive()
    {
        return $this->filterActive;
    }

    /**
     * @return string
     */
    public function getFilterActiveValue()
    {
        return $this->filterActiveValue;
    }

    /**
     * @return bool
     */
    public function isUserFilterEnabled()
    {
        return (bool) $this->userFilterEnabled;
    }

    /**
     * Enable data translation.
     *
     * @param bool $mode
     *
     * @return $this
     */
    public function setTranslationEnabled($mode = true)
    {
        $this->translationEnabled = (bool) $mode;

        return $this;
    }

    /**
     * Is data translation enabled?
     *
     * @return bool
     */
    public function isTranslationEnabled()
    {
        return (bool) $this->translationEnabled;
    }

    /**
     * Replace the column values with the applied values.
     *
     * @param array $values
     * @param bool  $notReplacedGetEmpty
     *
     * @return $this
     */
    public function setReplaceValues(array $values, $notReplacedGetEmpty = true)
    {
        $this->replaceValues = $values;
        $this->notReplacedGetEmpty = (bool) $notReplacedGetEmpty;

        $this->setFilterDefaultOperation(Filter::EQUAL);
        $this->setFilterSelectOptions($values);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasReplaceValues()
    {
        return $this->replaceValues ? true : false;
    }

    /**
     * @return array
     */
    public function getReplaceValues()
    {
        return $this->replaceValues;
    }

    /**
     * @return bool
     */
    public function notReplacedGetEmpty()
    {
        return $this->notReplacedGetEmpty;
    }

    /**
     * Set parameter for a specific renderer (currently only supported for jqGrid).
     *
     * @param string $name
     * @param mixed  $value
     * @param string $rendererType
     *
     * @return $this
     */
    public function setRendererParameter($name, $value, $rendererType = 'jqGrid')
    {
        if (! isset($this->rendererParameter[$rendererType])) {
            $this->rendererParameter[$rendererType] = [];
        }

        $parameters = $this->rendererParameter[$rendererType];
        $parameters[$name] = $value;

        $this->rendererParameter[$rendererType] = $parameters;

        return $this;
    }

    /**
     * @param string $rendererName
     *
     * @return array
     */
    public function getRendererParameters($rendererName = 'jqGrid')
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
     *
     * @return $this
     */
    public function setFormatters(array $formatters)
    {
        $this->formatters = $formatters;

        return $this;
    }

    /**
     * Set a template formatter and overwrite other formatter.
     *
     * @param AbstractFormatter $formatter
     *
     * @deprecated please use setFormatters
     *
     * @return $this
     */
    public function setFormatter(AbstractFormatter $formatter)
    {
        trigger_error('Please use setFormatters()', E_USER_DEPRECATED);

        $this->setFormatters([$formatter]);

        return $this;
    }

    /**
     * add a template formatter in the list.
     *
     * @param AbstractFormatter $formatter
     *
     * @return $this
     */
    public function addFormatter(AbstractFormatter $formatter)
    {
        $this->formatters[] = $formatter;

        return $this;
    }

    /**
     * return a list of different formatter.
     *
     * @return AbstractFormatter[]
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * return a list of different formatter.
     *
     * @return AbstractFormatter[]
     *
     * @deprecated please use getFormatters
     */
    public function getFormatter()
    {
        trigger_error('Please use getFormatters()', E_USER_DEPRECATED);

        return $this->getFormatters();
    }

    /**
     * @return bool
     */
    public function hasFormatters()
    {
        if (count($this->formatters) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasFormatter()
    {
        trigger_error('Please use hasFormatters()', E_USER_DEPRECATED);

        return $this->hasFormatters();
    }

    /**
     * @param bool $mode
     *
     * @return $this
     */
    public function setRowClickDisabled($mode = true)
    {
        $this->rowClickEnabled = (bool) ! $mode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isRowClickEnabled()
    {
        return $this->rowClickEnabled;
    }
}
