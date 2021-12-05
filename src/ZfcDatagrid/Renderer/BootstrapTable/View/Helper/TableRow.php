<?php

declare(strict_types=1);

namespace ZfcDatagrid\Renderer\BootstrapTable\View\Helper;

use Exception;
use InvalidArgumentException;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Helper\AbstractHelper;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Action\AbstractAction;

use function array_merge;
use function get_class;
use function implode;
use function print_r;

/**
 * View Helper.
 */
class TableRow extends AbstractHelper
{
    /** @var TranslatorInterface|null */
    private $translator;

    /**
     * @return self
     */
    public function setTranslator(?TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    private function translate(string $message): string
    {
        if (null === $this->translator) {
            return $message;
        }

        return $this->translator->translate($message);
    }

    /**
     * @param array $row
     */
    private function getTr(array $row, bool $open = true): string
    {
        if ($open !== true) {
            return '</tr>';
        } else {
            if (isset($row['idConcated'])) {
                return '<tr id="' . $row['idConcated'] . '">';
            } else {
                return '<tr>';
            }
        }
    }

    /**
     * @param array $attributes
     */
    private function getTd(string $dataValue, array $attributes = []): string
    {
        $attr = [];
        foreach ($attributes as $name => $value) {
            if ($value != '') {
                $attr[] = $name . '="' . $value . '"';
            }
        }

        $attr = implode(' ', $attr);

        return '<td ' . $attr . '>' . $dataValue . '</td>';
    }

    /**
     * @param array          $row
     * @param array          $cols
     * @param array          $rowStyles
     * @throws Exception
     */
    public function __invoke(
        array $row,
        array $cols,
        ?AbstractAction $rowClickAction = null,
        array $rowStyles = [],
        bool $hasMassActions = false
    ): string {
        $return = $this->getTr($row);

        if (true === $hasMassActions) {
            $return .= '<td><input type="checkbox" name="massActionSelected[]" value="' . $row['idConcated'] . '" /></td>';
        }

        foreach ($cols as $col) {
            /** @var Column\AbstractColumn $col */
            if (true === $col->isHide()) {
                continue;
            }

            $value = $row[$col->getUniqueId()];

            $cssStyles = [];
            $classes   = [];

            if (true === $col->isHidden()) {
                $classes[] = 'hidden';
            }

            switch (get_class($col->getType())) {
                case Column\Type\Number::class:
                    $cssStyles[] = 'text-align: right';
                    break;

                case Column\Type\PhpArray::class:
                    $value = '<pre>' . print_r($value, true) . '</pre>';
                    break;
            }

            $styles = array_merge($rowStyles, $col->getStyles());
            foreach ($styles as $style) {
                /** @var Column\Style\AbstractStyle $style */
                if ($style->isApply($row) === true) {
                    switch (get_class($style)) {
                        case Column\Style\Bold::class:
                            $cssStyles[] = 'font-weight: bold';
                            break;

                        case Column\Style\Italic::class:
                            $cssStyles[] = 'font-style: italic';
                            break;

                        case Column\Style\Color::class:
                            $cssStyles[] = 'color: #' . $style->getRgbHexString();
                            break;

                        case Column\Style\BackgroundColor::class:
                            $cssStyles[] = 'background-color: #' . $style->getRgbHexString();
                            break;

                        case Column\Style\Align::class:
                            $cssStyles[] = 'text-align: ' . $style->getAlignment();
                            break;

                        case Column\Style\Strikethrough::class:
                            $value = '<s>' . $value . '</s>';
                            break;

                        case Column\Style\CSSClass::class:
                            $classes[] = $style->getClass();
                            break;

                        case Column\Style\Html::class:
                            // do NOTHING! just pass the HTML!
                            break;

                        default:
                            throw new InvalidArgumentException('Not defined style: "' . get_class($style) . '"');
                            break;
                    }
                }
            }

            if ($col instanceof Column\Action) {
                /** @var Column\Action $col */
                $actions = [];
                foreach ($col->getActions() as $action) {
                    /** @var Column\Action\AbstractAction $action */
                    if ($action->isDisplayed($row) === true) {
                        $action->setTitle($this->translate($action->getTitle()));

                        if ($action->getRoute()) {
                            $action->setLink($this->view->url($action->getRoute(), $action->getRouteParams()));
                        }

                        $actions[] = $action->toHtml($row);
                    }
                }

                $value = implode(' ', $actions);
            }

            // "rowClick" action
            if (
                $col instanceof Column\Select
                && $rowClickAction instanceof AbstractAction
                && $col->isRowClickEnabled()
            ) {
                $value = '<a href="' . $rowClickAction->getLinkReplaced($row) . '">' . $value . '</a>';
            }

            $attributes = [
                'class'               => implode(' ', $classes),
                'style'               => implode(';', $cssStyles),
                'data-columnUniqueId' => $col->getUniqueId(),
            ];

            $return .= $this->getTd($value, $attributes);
        }

        $return .= $this->getTr($row, false);

        return $return;
    }
}
