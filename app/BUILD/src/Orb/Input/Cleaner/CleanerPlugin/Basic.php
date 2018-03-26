<?php

/**
 * Orb.
 *
 * @category Input
 */

namespace Orb\Input\Cleaner\CleanerPlugin;

use Orb\Input\Cleaner\Cleaner;
use Orb\Util\Strings;

/**
 * A cleaner plugin registeres typenames and callbacks.
 */
class Basic implements CleanerPlugin
{
    /** @var bool */
    protected $use_utf_funcs = false;

    public function getCleanerId()
    {
        return 'basic';
    }

    public function enableUtfHandling()
    {
        $this->use_utf_funcs = true;
    }

    public function getCleanerTypes()
    {
        return [
            'discard',
            'raw',
            'bool',
            'boolean',
            'bool_int',
            'ibool',
            'int',
            'integer',
            'uint',
            'float',
            'ufloat',
            'num',
            'number',
            'unum',
            'str',
            'string',
            'str_notrim',
            'str_nohtml',
            'nohtml',
            'str_striphtml',
            'striphtml',
            'str_simple',
            'simplestr',
            'str_key',
            'str_raw',
            'rawstr',
            'rawstring',
            'array',
        ];
    }

    /**
     * Clean a value.
     *
     * @param mixed $value   The value to clean
     * @param int   $type    The type to cast to
     * @param mixed $options Options for the type
     *
     * @return mixed The cleaned value
     */
    public function cleanValue($value, $type, array $options, Cleaner $cleaner)
    {
        //----------------------------------------
        // Do the cleaning
        //----------------------------------------

        switch ($type) {
            case 'bool':
                $value = (bool) $value;
                break;

            case 'bool_int':
            case 'ibool':
                $value = (int) ((bool) $value);
                break;

            case 'int':
            case 'integer':
                $value = (int) $value;
                break;

            case 'uint':
                $value = (int) $value;

                if ($value < 0) {
                    $value = 0;
                }
                break;

            case 'num':
            case 'number':
                $value = ((string) $value) + 0;
                break;

            case 'unum':
                $value = ((string) $value) + 0;

                if ($value < 0) {
                    $value = 0;
                }
                break;

            case 'float':
                $value = (float) $value;
                break;

            case 'ufloat':
                $value = (float) $value;
                if ($value < 0) {
                    $value = 0.0;
                }
                break;

            case 'str':
            case 'string':
                if (!is_scalar($value)) {
                    $value = '';
                }

                $value = str_replace("\xc2\xa0", ' ', $value);
                $value = $this->cleanString($value);
                $value = trim($value);

                break;

            case 'str_notrim':
                if (!is_scalar($value)) {
                    $value = '';
                }
                $value = (string) $this->cleanString($value);
                break;

            case 'str_nohtml':
            case 'nohtml':
                if (!is_scalar($value)) {
                    $value = '';
                }
                $value = htmlspecialchars(trim($this->cleanString($value)));
                break;

            case 'str_striphtml':
            case 'striphtml':
                if (!is_scalar($value)) {
                    $value = '';
                }
                $value = strip_tags(trim($this->cleanString($value)));
                break;

            case 'str_simple':
            case 'str_key':
                if (!is_scalar($value)) {
                    $value = '';
                }
                $value = preg_replace('#[^a-zA-Z0-9 _\-\.:]#', '', trim($this->cleanString($value)));
                break;

            case 'str_raw':
                if (!is_scalar($value)) {
                    $value = '';
                }
                $value = (string) $value;
                break;

            case 'array':
                $value = (array) $value;
                break;
        }

        return $value;
    }

    /**
     * If a string has mb characters, this will ensure the string is well-formed and
     * fix it if it's not (most secure thing to do).
     *
     * This uses phputf8 from sourceforge through the Strings util class which acts
     * as the loader.
     *
     * @param string|array $string The string to work on, or an array to go through
     *
     * @return string
     */
    public function cleanString($string)
    {
        //-------------------------
        // Recursively clean arrays
        //-------------------------

        if (is_array($string)) {
            foreach ($string as $k => $v) {
                $k = $this->cleanString($k);
                $v = $this->cleanString($v);

                $string[$k] = $v;
            }

            return $string;
        }

        //-------------------------
        // Clean normal strings
        //-------------------------

        if (!is_string($string)) {
            return $string;
        }

        $string = Strings::removeInvisibleCharacters($string);

        if ($this->use_utf_funcs) {
            $string = Strings::utf8_bad_strip($string);
        }

        return $string;
    }
}
