<?php
namespace ZfcDatagrid\Column\Action;

use Laminas\I18n\Translator\TranslatorInterface;
use function sprintf;

/**
 * @todo Checkbox for multi row actions...
 */
class Checkbox extends AbstractAction
{
    /** @var string */
    protected $name = 'rowSelections';

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
     * @inheritdoc
     */
    public function toHtml(array $row, ?TranslatorInterface $translator): string
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

    /**
     * @return string
     */
    protected function getHtmlType(): string
    {
        return '';
    }
}
