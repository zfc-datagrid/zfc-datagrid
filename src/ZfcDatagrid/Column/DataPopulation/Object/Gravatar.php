<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\DataPopulation\Object;

use Exception;
use InvalidArgumentException;
use ZfcDatagrid\Column\DataPopulation\ObjectAwareInterface;

use function md5;

class Gravatar implements ObjectAwareInterface
{
    /** @var string */
    protected $email = '';

    /**
     * @param mixed  $value
     * @throws Exception
     */
    private function setParameter(string $name, $value)
    {
        switch ($name) {
            case 'email':
                $this->email = (string) $value;
                break;

            default:
                throw new InvalidArgumentException('Not allowed parameter: ' . $name);
        }
    }

    /**
     * (non-PHPdoc).
     *
     * @see \ZfcDatagrid\Column\DataPopulation\ObjectAwareInterface::setParameterFromColumn()
     */
    public function setParameterFromColumn(string $name, $value): ObjectAwareInterface
    {
        $this->setParameter($name, $value);

        return $this;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \ZfcDatagrid\Column\DataPopulation\ObjectAwareInterface::toString()
     */
    public function toString(): string
    {
        $hash = '';
        if ('' !== $this->email) {
            $hash = md5($this->email);
        }

        return 'http://www.gravatar.com/avatar/' . $hash;
    }
}
