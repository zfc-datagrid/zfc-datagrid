<?php
namespace ZfcDatagrid\Column\Type;

class PhpString extends AbstractType
{
    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'string';
    }
}
