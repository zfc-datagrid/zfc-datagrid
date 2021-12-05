<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Action;

use function sprintf;

/**
 * @todo Checkbox for multi row actions...
 */
class Checkbox extends AbstractAction
{
    /** @var string */
    protected $name = 'rowSelections';

    public function __construct(string $name = 'rowSelections')
    {
        parent::__construct();

        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function toHtml(array $row): string
    {
        $this->removeAttribute('name');
        $this->removeAttribute('value');

        return sprintf(
            '<input type="checkbox" name="%s" value="%s" %s />',
            $this->name,
            $row['idConcated'],
            $this->getAttributesString($row)
        );
    }

    protected function getHtmlType(): string
    {
        return '';
    }
}
