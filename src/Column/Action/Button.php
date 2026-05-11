<?php
namespace ZfcDatagrid\Column\Action;

use Exception;
use InvalidArgumentException;
use Laminas\I18n\Translator\TranslatorInterface;
use ZfcDatagrid\Column\AbstractColumn;

class Button extends AbstractAction
{
    /** @var string|AbstractColumn */
    protected $label = '';

    /** @var string */
    protected $icon = '';

    /**
     * Button constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addClass('btn');
        $this->addClass('btn-default');
    }

    /**
     * @param string|AbstractColumn $name
     *
     * @return $this
     */
    public function setLabel($name): self
    {
        $this->label = $name;

        return $this;
    }

    /**
     * @return string|AbstractColumn
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     * @return Button
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    protected function getHtmlType(): string
    {
        throw new Exception('not needed...since we have toHtml() here directly!');
    }

    /**
     * @param array $row
     *
     * @return string
     */
    public function toHtml(array $row, ?TranslatorInterface $translator): string
    {
        if ('' === $this->getLabel()) {
            throw new InvalidArgumentException(
                'A label is required for this action type, please call $action->setLabel()!'
            );
        }

        $label = $this->getLabel();
        if ($label instanceof AbstractColumn) {
            $label = $row[$label->getUniqueId()];
        } elseif ($translator !== null) {
            $label = $translator->translate($label);
        }

        $icon = $this->getIcon();
        if ($icon !== '') {
            $icon = '<i class="' . $icon . '"></i> ';
        }

        return sprintf(
            '<a %s>%s%s</a>',
            $this->getAttributesString($row),
            $icon,
            $label
        );
    }
}
