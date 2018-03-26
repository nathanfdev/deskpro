<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

/**
 * Utility methods working with numbers / math.
 */
class MathUtils
{
    /**
     * @param string $val    The string to parse. Examples: 1B, 4M, 4MB
     * @param mixed  $errVal The return value when the size is invalid. 0, null, or the special string 'exception' to throw an exception
     *
     * @return int
     */
    public static function parseByteSize($val, $errVal = 0)
    {
        if (is_int($val)) {
            return $val;
        }

        if ($val === '-1') {
            return $val;
        }

        if (!is_string($val)) {
            if ($errVal === 'exception') {
                throw new \InvalidArgumentException('Invalid value, expected string');
            }

            return $errVal;
        }

        $val = strtoupper($val);
        $val = preg_replace('#[^a-zA-Z0-9\.\-]#', '', $val);

        if ($val === '') {
            return 0;
        }

        $m = null;
        if (!preg_match('#^(?P<num>\-?\d+(?:\.\d+)?)(?P<unit>\w*)$#', $val, $m)) {
            if ($errVal === 'exception') {
                throw new \InvalidArgumentException('Could not parse value');
            }

            return $errVal;
        }

        $num  = (float) $m['num'];
        $unit = $m['unit'];

        // unitless, means bytes
        if (empty($unit)) {
            return floor($num);
        }

        // Only 1 char, probably a INI-type size like "M" or "G"
        if ($unit !== 'B' && !isset($unit[1])) {
            $unit .= 'B';
        }

        try {
            return self::convertByteUnits($num, $unit);
        } catch (\InvalidArgumentException $e) {
            if ($errVal === 'exception') {
                throw $e;
            }

            return $errVal;
        }
    }

    /**
     * Converts bytes from various units.
     *
     * Known units: B, kB, MB, GB, TB, PB. For binary (base2), use KiB, MiB, GiB, TiB, PiB.
     *
     * @param int    $val
     * @param string $inUnit  The input unit
     * @param string $outUnit The desired output unit TODO only 'B' is supported right now
     *
     * @return int
     */
    public static function convertByteUnits($val, $inUnit, $outUnit = 'B')
    {
        static $pows = [
            'B'  => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8,
        ];

        $inUnit  = strtoupper($inUnit);
        $outUnit = strtoupper($outUnit);

        $base = 1000;
        if ($inUnit[1] === 'I') {
            $base   = 1024;
            $inUnit = $inUnit[0].substr($inUnit, 2);
        }

        if (!isset($pows[$inUnit])) {
            throw new \InvalidArgumentException('Unknown outUnit');
        }

        $bytes = floor($val * pow($base, $pows[$inUnit]));

        if ($outUnit === 'B') {
            return $bytes;
        }

        //TODO support others
    }

    /**
     * @param string $hex
     *
     * @return string
     */
    public static function normalizeHex($hex)
    {
        $hex = strtolower($hex);
        $hex = preg_replace('#[^0-9a-f]#', '', $hex);

        if (strlen($hex) > 2 && strlen($hex) < 6) {
            $hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
        }

        return $hex;
    }

    /**
     * @see https://www.w3.org/TR/AERT#color-contrast
     *
     * @param string $hex
     *
     * @return bool
     */
    public static function isDarkBg($hex)
    {
        $hex = self::normalizeHex($hex);

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $o = round((($r * 299) + ($g * 587) + ($b * 114)) / 1000);

        return $o < 125;
    }
}
