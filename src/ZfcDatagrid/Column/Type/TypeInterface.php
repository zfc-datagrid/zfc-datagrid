<?php
namespace ZfcDatagrid\Column\Type;

interface TypeInterface
{
    /**
     * Get the type name.
     *
     * @return string
     */
    public function getTypeName(): string;

    /**
     * the default filter operation.
     *
     * @return string
     */
    public function getFilterDefaultOperation(): string;

    /**
     * @param string $operator
     *
     * @return $this
     * @throws \Exception
     */
    public function setFilterDefaultOperation(string $operator): self;
}
