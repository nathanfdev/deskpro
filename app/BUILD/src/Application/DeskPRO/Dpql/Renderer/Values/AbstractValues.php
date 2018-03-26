<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Dpql\Renderer\Values;

use Application\DeskPRO\App;

/**
 * Handlers formatting values to a specific type (text, html, etc) for the
 * DPQL renderer. This allows escaping/output preparation to be abstracted
 * from the actual renderer type.
 */
abstract class AbstractValues
{
    /**
     * Renders a null value.
     *
     * @return string
     */
    abstract protected function _renderNull();

    /**
     * Renders a boolean value.
     *
     * @param bool $value
     *
     * @return string
     */
    abstract protected function _renderBoolean($value);

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
     *
     * @return string
     */
    public function renderValue($value, $format)
    {
        if ($value === null) {
            return $this->_renderNull();
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
                return $this->_renderBoolean($value);

            case 'datetime':
            case 'date':
            case 'time':
                $settingMap = [
                    'datetime' => 'core.date_fulltime',
                    'date'     => 'core.date_full',
                    'time'     => 'core.date_time',
                ];

                $tz = App::getCurrentPerson()->getTimezone();
                try {
                    if ($value instanceof \DateTime) {
                        $date = clone $value;
                        $date->setTimezone(new \DateTimeZone($tz));
                    } else {
                        $date = new \DateTime($value, new \DateTimeZone($tz));
                    }

                    return $this->escapeValue($date->format(App::getSetting($settingMap[$format])));
                } catch (\Exception $e) {
                    return $this->escapeValue($value);
                }

            case 'percent':
                return sprintf('%.2f', $value);
                break;

            case 'string':
            default:
                return $this->escapeValue($value);
        }
    }
}
