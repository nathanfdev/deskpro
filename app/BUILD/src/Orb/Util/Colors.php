<?php

/**
 * DeskPRO.
 */

namespace Orb\Util;

class Colors
{
    private function __construct()
    {
    }

    /**
     * Get colors (eg for a legend) for keys.
     *
     * @param array $keys
     *
     * @return array
     */
    public static function getColorsForKeys(array $keys)
    {
        $count = count($keys);
        $step  = 1.0 / $count;

        $segs = [];
        $tmp  = 0;
        for ($x = 0; $x < $count; ++$x) {
            $tmp += $step;
            $rgb    = \Orb\Util\Colors::getColorFromValue($tmp);
            $segs[] = \Orb\Util\Colors::rgbToHexcode($rgb);
        }
        shuffle($segs);

        $group_keys = [];
        foreach ($keys as $k) {
            $group_keys[$k] = array_pop($segs);
        }

        return $group_keys;
    }

    /**
     * Get a color from 0 to 1 which will be a reasonable color in the spectrum.
     *
     * @param int $value
     *
     * @return array
     */
    public static function getColorFromValue($value)
    {
        if ($value < 0) {
            $value = 0;
        } elseif ($value > 1.0001) {
            $value = 1.0;
        }

        $sat   = 230;
        $a     = 0.18;
        $light = 100 * $value;

        if ($value <= 0.25) {
            $i = [$light, $sat, round($sat * ($value) / $a)];
        } elseif ($value <= 0.5) {
            $i = [round($sat - $sat * ($value - 0.25) / $a), $light, $sat];
        } elseif ($value <= 0.75) {
            $i = [$sat, round($sat * ($value - 0.5) / $a), $light];
        } else {
            $i = [$sat, round($sat - $sat * ($value - 0.75) / $a), $light];
        }

        $rgb = [
            'red'   => abs($i[0]),
            'green' => abs($i[1]),
            'blue'  => abs($i[2]),
        ];

        return $rgb;
    }

    /**
     * Safely convert rgb to hex. Error corrects numbers out-of-range.
     *
     * @param array $rgb
     *
     * @return string
     */
    public static function rgbToHexcode(array $rgb)
    {
        $r = (int) $rgb['red'];
        $g = (int) $rgb['green'];
        $b = (int) $rgb['blue'];

        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

        $color = (strlen($r) < 2 ? '0' : '').$r;
        $color .= (strlen($g) < 2 ? '0' : '').$g;
        $color .= (strlen($b) < 2 ? '0' : '').$b;

        return $color;
    }

    /**
     * Turn a hex string into an array with 'red', 'green' and 'blue' items.
     *
     * @param $hex
     *
     * @return array
     */
    public static function hex2rgb($hex)
    {
        $hex = preg_replace('/[^0-9A-Fa-f]/', '', $hex);
        $rgb = [];
        if (strlen($hex) == 6) {
            $color_val    = hexdec($hex);
            $rgb['red']   = 0xFF & ($color_val >> 0x10);
            $rgb['green'] = 0xFF & ($color_val >> 0x8);
            $rgb['blue']  = 0xFF & $color_val;
        } elseif (strlen($hex) == 3) {
            $rgb['red']   = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $rgb['green'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $rgb['blue']  = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            return false;
        }

        return $rgb;
    }
}
