<?php
namespace ZfcDatagrid\Column\Type;

use DateTime as PhpDateTime;
use DateTimeZone;
use IntlDateFormatter;
use Locale;
use ZfcDatagrid\Filter;
use function date_default_timezone_get;

class DateTime extends AbstractType
{
    /** @var string */
    protected $filterDefaultOperation = Filter::GREATER_EQUAL;

    /** @var bool */
    protected $daterangePickerEnabled = false;

    /** @var string */
    protected $sourceDateTimeFormat = 'Y-m-d H:i:s';

    /** @var int */
    protected $outputDateType = IntlDateFormatter::MEDIUM;

    /** @var int */
    protected $outputTimeType = IntlDateFormatter::NONE;

    /**
     * Locale to use instead of the default.
     *
     * @var string|null
     */
    protected $locale;

    /**
     * Timezone to use.
     *
     * @var string
     */
    protected $sourceTimezone = 'UTC';

    /**
     * Timezone to use.
     *
     * @var string|null
     */
    protected $outputTimezone;

    /**
     * @var string|null
     */
    protected $outputPattern;

    /**
     * @param string $sourceDateTimeFormat
     *                                     PHP DateTime format
     * @param int    $outputDateType
     * @param int    $outputTimeType
     * @param string $locale
     * @param string $sourceTimezone
     * @param string $outputTimezone
     */
    public function __construct(
        $sourceDateTimeFormat = 'Y-m-d H:i:s',
        $outputDateType = IntlDateFormatter::MEDIUM,
        $outputTimeType = IntlDateFormatter::NONE,
        $locale = null,
        $sourceTimezone = 'UTC',
        $outputTimezone = null
    ) {
        $this->setSourceDateTimeFormat($sourceDateTimeFormat);
        $this->setOutputDateType($outputDateType);
        $this->setOutputTimeType($outputTimeType);
        $this->setLocale($locale);
        $this->setSourceTimezone($sourceTimezone);
        $this->setOutputTimezone($outputTimezone);
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        return 'dateTime';
    }

    /**
     * Set Daterange Filter enabled true/false.
     *
     * @param bool $val
     */
    public function setDaterangePickerEnabled(bool $val = true)
    {
        $this->daterangePickerEnabled = $val;
    }

    /**
     * Check if the Daterange Filter is enabled.
     */
    public function isDaterangePickerEnabled(): bool
    {
        return $this->daterangePickerEnabled;
    }

    /**
     * @param string $format
     */
    public function setSourceDateTimeFormat(string $format = 'Y-m-d H:i:s')
    {
        $this->sourceDateTimeFormat = $format;
    }

    /**
     * @return string
     */
    public function getSourceDateTimeFormat(): string
    {
        return $this->sourceDateTimeFormat;
    }

    /**
     * @param int $dateType
     */
    public function setOutputDateType(int $dateType = IntlDateFormatter::MEDIUM)
    {
        $this->outputDateType = $dateType;
    }

    /**
     * @return int
     */
    public function getOutputDateType(): int
    {
        return $this->outputDateType;
    }

    /**
     * @param int $timeType
     */
    public function setOutputTimeType(int $timeType = IntlDateFormatter::NONE)
    {
        $this->outputTimeType = $timeType;
    }

    /**
     * @return int
     */
    public function getOutputTimeType(): int
    {
        return $this->outputTimeType;
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
     * @param string $timezone
     */
    public function setSourceTimezone(string $timezone = 'UTC')
    {
        $this->sourceTimezone = $timezone;
    }

    /**
     * @return string
     */
    public function getSourceTimezone(): string
    {
        return $this->sourceTimezone;
    }

    /**
     * @param null|string $timezone
     */
    public function setOutputTimezone(?string $timezone = null)
    {
        $this->outputTimezone = $timezone;
    }

    /**
     * @return string
     */
    public function getOutputTimezone(): string
    {
        if (null === $this->outputTimezone) {
            $this->outputTimezone = date_default_timezone_get();
        }

        return $this->outputTimezone;
    }

    /**
     * ATTENTION: IntlDateTimeFormatter FORMAT!
     *
     * @param string $pattern
     */
    public function setOutputPattern(?string $pattern = null)
    {
        $this->outputPattern = $pattern;
    }

    /**
     * @return null|string
     */
    public function getOutputPattern(): ?string
    {
        return $this->outputPattern;
    }

    /**
     * @param string $val
     *
     * @return string
     */
    public function getFilterValue(string $val): string
    {
        $formatter = new IntlDateFormatter(
            $this->getLocale(),
            $this->getOutputDateType(),
            $this->getOutputTimeType(),
            $this->getOutputTimezone(),
            IntlDateFormatter::GREGORIAN,
            $this->getOutputPattern()
        );
        $timestamp = $formatter->parse($val);

        $date = new PhpDateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new DateTimeZone($this->getSourceTimezone()));

        return $date->format($this->getSourceDateTimeFormat());
    }

    /**
     * Convert the value from the source to the value, which the user will see in the column.
     *
     * @param mixed $val
     *
     * @return mixed
     */
    public function getUserValue($val)
    {
        if ('' == $val) {
            return '';
        }

        if ($val instanceof PhpDateTime) {
            $date = $val;
            $date->setTimezone(new DateTimeZone($this->getSourceTimezone()));
            $date->setTimezone(new DateTimeZone($this->getOutputTimezone()));
        } else {
            $date = PhpDateTime::createFromFormat(
                $this->getSourceDateTimeFormat(),
                $val,
                new DateTimeZone($this->getSourceTimezone())
            );
            if (false === $date) {
                return '';
            }
            $date->setTimezone(new DateTimeZone($this->getOutputTimezone()));
        }
        $formatter = new IntlDateFormatter(
            $this->getLocale(),
            $this->getOutputDateType(),
            $this->getOutputTimeType(),
            $this->getOutputTimezone(),
            IntlDateFormatter::GREGORIAN,
            $this->getOutputPattern()
        );

        return $formatter->format($date->getTimestamp());
    }
}
