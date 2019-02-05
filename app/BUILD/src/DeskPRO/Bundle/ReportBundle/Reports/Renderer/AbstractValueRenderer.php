<?php

namespace DeskPRO\Bundle\ReportBundle\Reports\Renderer;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\ReportBundle\Reports\ResultMetadata;

/**
 * Handlers formatting values to a specific type (text, html, etc) for the
 * DPQL renderer. This allows escaping/output preparation to be abstracted
 * from the actual renderer type.
 */
abstract class AbstractValueRenderer
{
    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(SettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * Renders a null value.
     *
     * @return string
     */
    abstract protected function renderNull();

    /**
     * Renders a boolean value.
     *
     * @param bool $value
     *
     * @return string
     */
    abstract protected function renderBoolean($value);

    /**
     * Escapes the value for direct output.
     *
     * @param string $value
     *
     * @return string
     */
    abstract public function escapeValue($value);

    /**
     * Renders a value, ready to be output. Note that the value may still needed
     * to be wrapped to be valid (quotes, td html tag, etc).
     *
     * @param string          $value
     * @param string|\Closure $format
     * @param ResultMetadata  $metadata
     *
     * @return string
     */
    public function renderValue($value, $format, ResultMetadata $metadata)
    {
        if ($value === null) {
            return $this->renderNull();
        }

        $format = strtolower($format);

        switch ($format) {
            case 'year':
                return $this->escapeValue($value);

            case 'number':
            case 'numberraw':
            case 'id':
                if (preg_match('/^(\d*)\.(\d+)$/', $value, $match)) {
                    // float
                    $decimals = min(1, strlen($match[2]));
                } else {
                    // integer
                    $decimals = 0;
                }

                if (in_array($format, ['numberraw', 'id'])) {
                    $thousands = '';
                } else {
                    $thousands = ',';
                }

                $value = round($value, $decimals);

                return $this->escapeValue(number_format($value, $decimals, '.', $thousands));

            case 'boolean':
                return $this->renderBoolean($value);

            case 'datetime':
            case 'date':
            case 'time':
                $settingMap = [
                    'datetime' => 'core.date_fulltime',
                    'date'     => 'core.date_full',
                    'time'     => 'core.date_time',
                ];

                $tz     = $metadata->getContext() ? $metadata->getContext()->getTimezone()->getName() : 'UTC';
                $offset = $metadata->getContext() ? $metadata->getContext()->getTimezoneOffsetSeconds() : '0';
                try {
                    if ($value instanceof \DateTime) {
                        $date = clone $value;
                        $date->setTimezone(new \DateTimeZone($tz));
                    } else {
                        $date = new \DateTime($value, new \DateTimeZone($tz));
                    }

                    $date->modify(($offset >= 0 ? '+'.$offset : $offset).' seconds');

                    return $this->escapeValue($date->format($this->settingsResolver->getGlobalSettings()->get($settingMap[$format])));
                } catch (\Exception $e) {
                    return $this->escapeValue($value);
                }

            case 'percentfull':
                return sprintf('%f', $value);
                break;

            case 'percent':
                return sprintf('%.2f', $value);
                break;

            case 'string':
            default:
                return $this->escapeValue($value);
        }
    }
}
