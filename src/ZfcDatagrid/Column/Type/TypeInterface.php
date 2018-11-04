<?php
namespace ZfcDatagrid\Column\Type;

interface TypeInterface
{
    /**
     * Get the type name.
     *
     * @return string
     */
    public function getTypeName();

    /**
     * the default filter operation.
     *
     * @return string
     */
    public function getFilterDefaultOperation();

    /**
     * @param string $operator
     *
     * @return $this
     * @throws \Exception
     */
    public function setFilterDefaultOperation($operator);
}
