<?php

namespace Orb\Util;

use Orb\Data\BadWords;

class DpStrings
{
    public static function hasBadString($string)
    {
        $string = strtolower($string);
        foreach (BadWords::$words as $word => $k) {
            if (false !== strpos($string, $word)) {
                return true;
            }
        }

        return false;
    }

    public static function random($len = 8, $chars = null)
    {
        do {
            $str = Strings::random($len, $chars);
        } while (self::hasBadString($str));

        return $str;
    }

    public static function randomPronounceable($len = 10, $dash_len = 0)
    {
        do {
            $str = Strings::randomPronounceable($len, $dash_len);
        } while (self::hasBadString($str));

        return $str;
    }
}
