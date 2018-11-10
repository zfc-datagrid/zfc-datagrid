<?php
namespace ZfcDatagrid\Column\Type;

use Locale;
use NumberFormatter;
use ZfcDatagrid\Filter;
use function strlen;
use function substr;
use function strpos;

class Number extends AbstractType
{
    /** @var string */
    protected $filterDefaultOperation = Filter::EQUAL;

    /**
     * Locale to use instead of the default.
     *
     * @var string|null
     */
    protected $locale;

    /**
     * NumberFormat style to use.
     *
     * @var int
     */
    protected $formatStyle = NumberFormatter::DECIMAL;

    /**
     * NumberFormat type to use.
     *
     * @var int
     */
    protected $formatType = NumberFormatter::TYPE_DEFAULT;

    /** @var array */
    protected $attributes = [];

    /** @var string */
    protected $prefix = '';

    /** @var string */
    protected $suffix = '';

    /** @var null|string */
    protected $pattern;

    /**
     * Number constructor.
     * @param int $formatStyle
     * @param int $formatType
     * @param null $locale
     */
    public function __construct(
        int $formatStyle = NumberFormatter::DECIMAL,
        int $formatType = NumberFormatter::TYPE_DEFAULT,
        ?string $locale = null
    ) {
        $this->setFormatStyle($formatStyle);
        $this->setFormatType($formatType);
        $this->setLocale($locale);
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'number';
    }

    /**
     * @param int $style
     */
    public function setFormatStyle(int $style = NumberFormatter::DECIMAL)
    {
        $this->formatStyle = $style;
    }

    /**
     * @return int
     */
    public function getFormatStyle(): int
    {
        return $this->formatStyle;
    }

    /**
     * @param int $type
     */
    public function setFormatType(int $type = NumberFormatter::TYPE_DEFAULT)
    {
        $this->formatType = $type;
    }

    /**
     * @return int
     */
    public function getFormatType(): int
    {
        return $this->formatType;
    }

    /**
     * @param null|string $locale
     */
    public function setLocale(?string $locale = null)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        if (null === $this->locale) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * Set an attribute.
     *
     * @link http://www.php.net/manual/en/numberformatter.setattribute.php
     *
     * @param int $attr
     * @param int $value
     */
    public function addAttribute(int $attr, int $value)
    {
        $this->attributes[] = [
            'attribute' => $attr,
            'value'     => $value,
        ];
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string $string
     */
    public function setSuffix(string $string = '')
    {
        $this->suffix = $string;
    }

    /**
     * @return string
     */
    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @param string $string
     */
    public function setPrefix(string $string = '')
    {
        $this->prefix = $string;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param null|string $pattern
     */
    public function setPattern(?string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return null|string
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return NumberFormatter
     */
    protected function getFormatter(): NumberFormatter
    {
        $formatter = new NumberFormatter($this->getLocale(), $this->getFormatStyle());
        if (null !== $this->getPattern()) {
            $formatter->setPattern($this->getPattern());
        }
        foreach ($this->getAttributes() as $attribute) {
            $formatter->setAttribute($attribute['attribute'], $attribute['value']);
        }

        return $formatter;
    }

    /**
     * @param string $val
     *
     * @return string
     */
    public function getFilterValue(string $val): string
    {
        $formatter = $this->getFormatter();

        if (strlen($this->getPrefix()) > 0 && strpos($val, $this->getPrefix()) === 0) {
            $val = substr($val, strlen($this->getPrefix()));
        }
        if (strlen($this->getSuffix()) > 0 && strpos($val, $this->getSuffix()) > 0) {
            $val = substr($val, 0, -strlen($this->getSuffix()));
        }

        try {
            $formattedValue = $formatter->parse($val);
        } catch (\Exception $e) {
            return $val;
        }

        if (false === $formattedValue) {
            return $val;
        }

        return $formattedValue;
    }

    /**
     * Convert the value from the source to the value, which the user will see.
     *
     * @param mixed $val
     *
     * @return mixed
     */
    public function getUserValue($val)
    {
        $formatter = $this->getFormatter();

        $formattedValue = $formatter->format($val, $this->getFormatType());

        return $this->getPrefix() . $formattedValue . $this->getSuffix();
    }
}
