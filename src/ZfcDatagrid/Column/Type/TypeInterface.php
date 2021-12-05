<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Type;

use Exception;

interface TypeInterface
{
    /**
     * Get the type name.
     */
    public function getTypeName(): string;

    /**
     * the default filter operation.
     */
    public function getFilterDefaultOperation(): string;

    /**
     * @return $this
     * @throws Exception
     */
    public function setFilterDefaultOperation(string $operator): self;
}
