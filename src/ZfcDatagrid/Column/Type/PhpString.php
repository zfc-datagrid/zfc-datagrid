<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Type;

class PhpString extends AbstractType
{
    public function getTypeName(): string
    {
        return 'string';
    }
}
