<?php
namespace ZfcDatagrid\Column\Action;

/**
 * @todo Checkbox for multi row actions...
 */
class Checkbox extends AbstractAction
{
    /** @var string */
    private $name = 'rowSelections';

    /**
     * Checkbox constructor.
     *
     * @param string $name
     */
    public function __construct(string $name = 'rowSelections')
    {
        parent::__construct();

        $this->name = $name;
    }

    /**
     * @return string
     */
    protected function getHtmlType(): string
    {
        return '';
    }

    /**
     * @see \ZfcDatagrid\Column\Action\AbstractAction::toHtml()
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
}
