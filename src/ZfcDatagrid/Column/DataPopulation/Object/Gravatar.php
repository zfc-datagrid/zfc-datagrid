<?php

namespace ZfcDatagrid\Column\DataPopulation\Object;

use ZfcDatagrid\Column\DataPopulation\ObjectAwareInterface;

/**
 * Class Gravatar
 *
 * @package ZfcDatagrid\Column\DataPopulation\Object
 */
class Gravatar implements ObjectAwareInterface
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     * @throws \Exception
     */
    private function setParameter($name, $value)
    {
        switch ($name) {
            case 'email':
                $this->email = (string)$value;
                break;

            default:
                throw new \InvalidArgumentException('Not allowed parameter: ' . $name);
                break;
        }

        return $this;
    }

    /**
     * @see \ZfcDatagrid\Column\DataPopulation\ObjectAwareInterface::setParameterFromColumn()
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     * @throws \Exception
     */
    public function setParameterFromColumn($name, $value)
    {
        $this->setParameter($name, $value);

        return $this;
    }

    /**
     * @see \ZfcDatagrid\Column\DataPopulation\ObjectAwareInterface::toString()
     *
     * @return string
     */
    public function toString()
    {
        $hash = '';
        if ($this->email != '') {
            $hash = md5($this->email);
        }

        return 'http://www.gravatar.com/avatar/' . $hash;
    }
}
