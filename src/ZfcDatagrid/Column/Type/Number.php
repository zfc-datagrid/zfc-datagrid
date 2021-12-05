<?php

declare(strict_types=1);

namespace ZfcDatagrid\Column\Type;

use Exception;
use Locale;
use NumberFormatter;
use ZfcDatagrid\Filter;

use function strlen;
use function strpos;
use function substr;

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

    public function getTypeName(): string
    {
        return 'number';
    }

    /**
     * @return $this
     */
    public function setFormatStyle(int $style = NumberFormatter::DECIMAL): self
    {
        $this->formatStyle = $style;

        return $this;
    }

    public function getFormatStyle(): int
    {
        return $this->formatStyle;
    }

    /**
     * @return $this
     */
    public function setFormatType(int $type = NumberFormatter::TYPE_DEFAULT): self
    {
        $this->formatType = $type;

        return $this;
    }

    public function getFormatType(): int
    {
        return $this->formatType;
    }

    /**
     * @return $this
     */
    public function setLocale(?string $locale = null): self
    {
        $this->locale = $locale;

        return $this;
    }

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
     * @return $this
     */
    public function addAttribute(int $attr, int $value): self
    {
        $this->attributes[] = [
            'attribute' => $attr,
            'value'     => $value,
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return $this
     */
    public function setSuffix(string $string = ''): self
    {
        $this->suffix = $string;

        return $this;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    /**
     * @return $this
     */
    public function setPrefix(string $string = ''): self
    {
        $this->prefix = $string;

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return $this
     */
    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

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
        } catch (Exception $e) {
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
     * @return mixed
     */
    public function getUserValue($val)
    {
        $formatter = $this->getFormatter();

        $formattedValue = $formatter->format((float) $val, $this->getFormatType());

        return $this->getPrefix() . $formattedValue . $this->getSuffix();
    }
}
