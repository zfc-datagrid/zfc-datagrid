<?php

namespace ZfcDatagrid\Column\Type;

use DateTime as PhpDateTime;
use DateTimeZone;
use IntlDateFormatter;
use Locale;
use ZfcDatagrid\Filter;

/**
 * Class DateTime
 *
 * @package ZfcDatagrid\Column\Type
 */
class DateTime extends AbstractType
{
    /**
     * @var bool
     */
    protected $daterangePickerEnabled = false;

    /**
     * @var string|null
     */
    protected $sourceDateTimeFormat;

    /**
     * @var int|null
     */
    protected $outputDateType;

    /**
     * @var int|null
     */
    protected $outputTimeType;

    /**
     * Locale to use instead of the default.
     *
     * @var string|null
     */
    protected $locale;

    /**
     * Timezone to use.
     *
     * @var string|null
     */
    protected $sourceTimezone;

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
     * @param string      $sourceDateTimeFormat
     *                                     PHP DateTime format
     * @param int         $outputDateType
     * @param int         $outputTimeType
     * @param string|null $locale
     * @param string      $sourceTimezone
     * @param string|null $outputTimezone
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
    public function getTypeName()
    {
        return 'dateTime';
    }

    /**
     * Set Daterange Filter enabled true/false.
     *
     * @param bool $val
     *
     * @return $this
     */
    public function setDaterangePickerEnabled($val = true)
    {
        $this->daterangePickerEnabled = $val;

        return $this;
    }

    /**
     * Check if the Daterange Filter is enabled.
     */
    public function isDaterangePickerEnabled()
    {
        return $this->daterangePickerEnabled;
    }

    /**
     * @param string $format
     *
     * @return $this
     */
    public function setSourceDateTimeFormat($format = 'Y-m-d H:i:s')
    {
        $this->sourceDateTimeFormat = $format;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSourceDateTimeFormat()
    {
        return $this->sourceDateTimeFormat;
    }

    /**
     * @param int $dateType
     *
     * @return $this
     */
    public function setOutputDateType($dateType = IntlDateFormatter::MEDIUM)
    {
        $this->outputDateType = $dateType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOutputDateType()
    {
        return $this->outputDateType;
    }

    /**
     * @param int $timeType
     *
     * @return $this
     */
    public function setOutputTimeType($timeType = IntlDateFormatter::NONE)
    {
        $this->outputTimeType = $timeType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getOutputTimeType()
    {
        return $this->outputTimeType;
    }

    /**
     * @param string|null $locale
     *
     * @return $this
     */
    public function setLocale($locale = null)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * @param string $timezone
     *
     * @return $this
     */
    public function setSourceTimezone($timezone = 'UTC')
    {
        $this->sourceTimezone = $timezone;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceTimezone()
    {
        return $this->sourceTimezone;
    }

    /**
     * @param string|null $timezone
     *
     * @return $this
     */
    public function setOutputTimezone($timezone = null)
    {
        $this->outputTimezone = $timezone;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputTimezone()
    {
        if (null === $this->outputTimezone) {
            $this->outputTimezone = date_default_timezone_get();
        }

        return $this->outputTimezone;
    }

    /**
     * ATTENTION: IntlDateTimeFormatter FORMAT!
     *
     * @param string|null $pattern
     *
     * @return $this
     */
    public function setOutputPattern($pattern = null)
    {
        $this->outputPattern = $pattern;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOutputPattern()
    {
        return $this->outputPattern;
    }

    /**
     * @return string
     */
    public function getFilterDefaultOperation()
    {
        return Filter::GREATER_EQUAL;
    }

    /**
     * @param string $val
     *
     * @return string
     */
    public function getFilterValue($val)
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
     * @return string
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
