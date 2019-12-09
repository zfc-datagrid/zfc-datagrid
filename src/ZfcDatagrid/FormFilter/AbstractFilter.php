<?php

namespace ZfcDatagrid\FormFilter;

use ZfcDatagrid\Column\Type;

abstract class AbstractFilter
{
    protected $label = '';

    protected $width = 5;
    
    /** @var int|null */
    protected $position;

    protected $uniqueId;
    
    protected $filterActive = null;
    
    protected $filterActiveValue;
    
    /**
     * @var Type\AbstractType
     */
    protected $type = null;
    
    /**
     * @param $name
     */
    public function setLabel($name)
    {
        $this->label = (string) $name;
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
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * 
     * @param int|null $position
     * @return \ZfcDatagrid\FormFilter\AbstractFilter
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Set the width in "percent"
     * It will be calculated to 100% dependend on what is displayed
     * If it's a different output mode like Excel it's dependend on the papersize/orientation.
     *
     * @param number $percent
     */
    public function setWidth($percent)
    {
        $this->width = (float) $percent;
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
    
    
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;   
    }
    
    public function getUniqueId()
    {
        return $this->uniqueId;
    }
    
    /**
     * @param mixed $value
     */
    public function setFilterActive($value = '')
    {
        $this->filterActive = (bool) true;
        $this->filterActiveValue = $value;
    }
    
    /**
     * @return string
     */
    public function getFilterActiveValue()
    {
        return $this->filterActiveValue;
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
}