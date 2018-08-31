<?php

namespace ZfcDatagrid\Column\Type;

/**
 * Class PhpString
 *
 * @package ZfcDatagrid\Column\Type
 */
class PhpString extends AbstractType
{
    /**
     * @return string
     */
    public function getTypeName()
    {
        return 'string';
    }
}
