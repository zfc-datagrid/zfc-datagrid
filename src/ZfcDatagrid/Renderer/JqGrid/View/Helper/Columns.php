<?php

declare(strict_types=1);

namespace ZfcDatagrid\Renderer\JqGrid\View\Helper;

use Exception;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Helper\AbstractHelper;
use ZfcDatagrid\Column;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Filter;

use function get_class;
use function implode;
use function is_array;
use function is_bool;
use function json_encode;
use function sprintf;
use function stripos;

/**
 * View Helper.
 */
class Columns extends AbstractHelper
{
    /** @var TranslatorInterface|null */
    private $translator;

    const STYLE_BOLD = 'cellvalue = \'<span style="font-weight: bold;">\' + cellvalue + \'</span>\';';

    const STYLE_ITALIC = 'cellvalue = \'<span style="font-style: italic;">\' + cellvalue + \'</span>\';';

    const STYLE_STRIKETHROUGH =
        'cellvalue = \'<span style="text-decoration: line-through;">\' + cellvalue + \'</span>\';';

    /**
     * @return $this
     */
    public function setTranslator(?TranslatorInterface $translator): self
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
     * @param array $columns
     */
    public function __invoke(array $columns): string
    {
        $return = [];

        foreach ($columns as $column) {
            /** @var Column\AbstractColumn $column */

            $options = [
                'name'     => (string) $column->getUniqueId(),
                'index'    => (string) $column->getUniqueId(),
                'label'    => $this->translate((string) $column->getLabel()),
                'width'    => $column->getWidth(),
                'hidden'   => (bool) $column->isHidden(),
                'sortable' => (bool) $column->isUserSortEnabled(),
                'search'   => (bool) $column->isUserFilterEnabled(),
            ];

            /*
             * Formatting
             */
            $formatter = $this->getFormatter($column);
            if ($formatter != '') {
                $options['formatter'] = (string) $formatter;
            }

            $alignAlreadyDefined = false;
            if ($column->hasStyles()) {
                foreach ($column->getStyles() as $style) {
                    /** @var Column\Style\Align $style */
                    if (get_class($style) == Column\Style\Align::class) {
                        $options['align']    = $style->getAlignment();
                        $alignAlreadyDefined = true;
                        break;
                    }
                }
            }

            if (! $alignAlreadyDefined && $column->getType() instanceof Type\Number) {
                $options['align'] = Column\Style\Align::RIGHT;
            }

            /*
             * Cellattr
             */
            $rendererParameters = $column->getRendererParameters('jqGrid');
            if (isset($rendererParameters['cellattr'])) {
                $options['cellattr'] = (string) $rendererParameters['cellattr'];
            }
            if (isset($rendererParameters['classes'])) {
                $options['classes'] = (string) $rendererParameters['classes'];
            }

            /*
             * Filtering
             */
            $searchoptions                = [];
            $searchoptions['clearSearch'] = false;
            if ($column->hasFilterSelectOptions()) {
                $options['stype']       = 'select';
                $searchoptions['value'] = $column->getFilterSelectOptions();

                if ($column->hasFilterDefaultValue()) {
                    $searchoptions['defaultValue'] = $column->getFilterDefaultValue();
                } else {
                    $searchoptions['defaultValue'] = '';
                }
            } elseif ($column->hasFilterDefaultValue()) {
                $filter = new Filter();
                $filter->setFromColumn($column, $column->getFilterDefaultValue());

                $searchoptions['defaultValue'] = $filter->getDisplayColumnValue();
            }

            if (! empty($searchoptions)) {
                $options['searchoptions'] = $searchoptions;
            }

            /**
             * Because with json_encode we get problems, it's custom made!
             */
            $colModel = [];
            foreach ($options as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_bool($value)) {
                    if (true === $value) {
                        $value = 'true';
                    } else {
                        $value = 'false';
                    }
                } elseif ('formatter' == $key) {
                    if (
                        stripos($value, 'formatter') === false &&
                        stripos($value, 'function') === false
                    ) {
                        $value = '"' . $value . '"';
                    }
                } elseif ('cellattr' == $key) {
                    // SKIP THIS
                } else {
                    $value = '"' . $value . '"';
                }

                $colModel[] = (string) $key . ': ' . $value;
            }

            $return[] = '{' . implode(',', $colModel) . '}';
        }

        return '[' . implode(',', $return) . ']';
    }

    private function getFormatter(Column\AbstractColumn $column): string
    {
        /*
         * User defined formatter
         */
        $rendererParameters = $column->getRendererParameters('jqGrid');
        if (isset($rendererParameters['formatter'])) {
            return $rendererParameters['formatter'];
        }

        /*
         * Formatter based on column options + styles
         */
        $formatter = '';

        $formatter .= implode(' ', $this->getStyles($column));

        switch (get_class($column->getType())) {
            case Type\PhpArray::class:
                $formatter .= 'cellvalue = \'<pre>\' + cellvalue.join(\'<br />\') + \'</pre>\';';
                break;
        }

        if ($column instanceof Column\Action) {
            $formatter .= ' cellvalue = cellvalue; ';
        }

        if ($formatter != '') {
            $prefix = 'function (cellvalue, options, rowObject) {';
            $suffix = ' return cellvalue; }';

            $formatter = $prefix . $formatter . $suffix;
        }

        return $formatter;
    }

    /**
     * @throws Exception
     * @return array
     */
    private function getStyles(Column\AbstractColumn $col): array
    {
        $styleFormatter = [];

        /*
         * First all based on value (only one works) @todo
         */
        foreach ($col->getStyles() as $style) {
            $prepend = '';
            $append  = '';

            /** @var Column\Style\AbstractStyle $style */
            foreach ($style->getByValues() as $rule) {
                $colString = $rule['column']->getUniqueId();
                switch ($rule['operator']) {
                    case Filter::EQUAL:
                        $operator = '==';
                        break;

                    case Filter::NOT_EQUAL:
                        $operator = '!=';
                        break;

                    default:
                        throw new Exception('Currently not supported filter operation: "' . $rule['operator'] . '"');
                }

                $prepend = 'if (rowObject.' . $colString . ' ' . $operator . ' \'' . $rule['value'] . '\') {';
                $append .= '}';
            }

            $styleString = '';
            switch (get_class($style)) {
                case Column\Style\Bold::class:
                    $styleString = self::STYLE_BOLD;
                    break;

                case Column\Style\Italic::class:
                    $styleString = self::STYLE_ITALIC;
                    break;

                case Column\Style\Strikethrough::class:
                    $styleString = self::STYLE_STRIKETHROUGH;
                    break;

                case Column\Style\Color::class:
                    $styleString = sprintf(
                        'cellvalue = \'<span style="color: #%s;">\' + cellvalue + \'</span>\';',
                        $style->getRgbHexString()
                    );
                    break;

                case Column\Style\CSSClass::class:
                    $styleString = 'cellvalue = \'<span class="' . $style->getClass() . '">\' + cellvalue + \'</span>\';';
                    break;

                case Column\Style\BackgroundColor::class:
                    // do NOTHING! this is done by loadComplete event...
                    // At this stage jqgrid haven't created the columns...
                    break;

                case Column\Style\Html::class:
                    // do NOTHING! just pass the HTML!
                    break;

                case Column\Style\Align::class:
                    // do NOTHING! we have to add the align style in the gridcell and not in a span!
                    break;

                default:
                    throw new Exception('Not defined style: "' . get_class($style) . '"');
                    break;
            }

            $styleFormatter[] = $prepend . $styleString . $append;
        }

        return $styleFormatter;
    }
}
