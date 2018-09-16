<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

/**
 * Utility functions that work with numbers.
 *
 * @static
 */
class Numbers
{
    const ROUND_MULTIPLE_NEAR = 1;
    const ROUND_MULTIPLE_UP   = 2;
    const ROUND_MULTIPLE_DOWN = 3;

    /**
     * Check if a value is an integer value. This is like is_int() but also passes
     * strings that are integer form.
     *
     * You can't use ctype_digit or is_numeric because they'll pass other numeric
     * forms, not just integers.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function isInteger($value)
    {
        if (!is_scalar($value) or is_array($value)) {
            return false;
        }

        if (is_int($value) or ((string) ((int) $value)) == (string) $value) {
            return true;
        }

        return false;
    }

    /**
     * Take a number, and force it to be within the range of $min and $max.
     * This will make it $min if it's smaller than $min, and $max if it's larger
     * than $max.
     *
     * @param int $num The number to work with
     * @param int $min The minumum integer
     * @param int $max The maximum integer
     *
     * @return int
     */
    public static function bound($num, $min, $max)
    {
        if ($num < $min) {
            $num = $min;
        }
        if ($num > $max) {
            $num = $max;
        }

        return $num;
    }

    /**
     * Check if something is somewhere within the range of two numbers.
     *
     * @param int $what The thing to check
     * @param int $min  The minimum number (inclusive)
     * @param int $max  The maximum number (inclusive)
     *
     * @return bool
     */
    public static function inRange($what, $min = 0, $max = 10)
    {
        if ($what >= $min && $what <= $max) {
            return true;
        }

        return false;
    }

    /**
     * Turn a number into roman numerals.
     *
     * <code>
     * echo Orb_Num::romanNumerals(42); // XLII
     * </code>
     *
     * @param int $num The number to romanize
     *
     * @return string
     */
    public static function romanNumerals($num)
    {
        static $map = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1,
        ];

        $num = intval($num);
        $res = '';

        foreach ($map as $roman => $value) {
            $res .= str_repeat($roman, (int) $num / $value);
            $num %= $value;
        }

        return $res;
    }

    /**
     * Display a filesize in bytes in the smallest unit.
     *
     * @param int    $bytes
     * @param string $mode  'auto' or 'si' (base 10) or 'iec' (base 2). Auto will try to detect the 'cleanest' number
     *
     * @return string
     */
    public static function filesizeDisplay($bytes, $mode = 'auto')
    {
        if ($mode == 'auto') {
            $parts           = self::getFilesizeDisplayParts($bytes, 'si');
            $parts['number'] = sprintf('%.2f', $parts['number']);
            if (!strpos($parts['number'], '.00')) {
                $parts = self::getFilesizeDisplayParts($bytes, 'iec');
            }
        } else {
            $parts = self::getFilesizeDisplayParts($bytes, $mode);
        }

        return sprintf('%.2f %s', $parts['number'], $parts['symbol']);
    }

    /**
     * From a filesize in bytes return an array of the largest unit symbol
     * and its size. If you want a string, use filesizeDisplay().
     *
     * @param        $bytes
     * @param string $mode
     *
     * @return array
     */
    public static function getFilesizeDisplayParts($bytes, $mode = 'si')
    {
        if (!$bytes or $bytes < 1) {
            return ['number' => 0, 'symbol' => 'B'];
        }

        $x = $mode == 'si' ? 1000 : 1024;

        $all_symbols = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $exp         = (int) floor(log($bytes) / log($x));
        $val         = $bytes / pow($x, $exp);

        $sym = '';
        if (isset($all_symbols[$exp])) {
            $sym = $all_symbols[$exp];
        }

        return [
            'number' => $val,
            'symbol' => $sym,
        ];
    }

    /**
     * Round a number to the nearest multiple.
     *
     * <code>
     * echo Orb_Num::roundToMultiple(26, 5); // 25
     * echo Orb_Num::roundToMultiple(26, 5, Orb_Num::ROUND_MULTIPLE_UP); // 30
     * echo Orb_Num::roundToMultiple(29, 5, Orb_Num::ROUND_MULTIPLE_DOWN); // 25
     * </code>
     *
     * @param int|float $number   The number to round
     * @param int       $multiple The multiple to round to
     * @param int       $mode     Rounding mode to use
     *
     * @return mixed
     */
    public static function roundToMultiple($number, $multiple, $mode = self::ROUND_MULTIPLE_NEAR)
    {
        if ($mode == self::ROUND_MULTIPLE_NEAR) {
            return round($number / $multiple) * $multiple;
        } elseif ($mode == self::ROUND_MULTIPLE_DOWN) {
            return floor(floor($number) / $multiple) * $multiple;
        } else {
            return ceil(ceil($number) / $multiple) * $multiple;
        }
    }

    /**
     * Get an array of pageinfo useful for building up paginination in templates.
     * You get an array with:
     * - prev: int of previous page, or false if no prev
     * - next: int of next page, or false if no next
     * - first: int of first page (1, obviously)
     * - last: int of last page
     * - pages: range() of page numbers useful in a loop
     * - curpage: The current page.
     *
     *
     * @param int $num_results The total number of results
     * @param int $page        The current page you're on
     * @param int $per_page    How many results per page
     * @param int $pad         How many page numbers around the current to show
     *
     * @return array
     */
    public static function getPaginationPages($num_results, $page, $per_page, $pad = 5)
    {
        $info = [];

        $num_pages = ceil($num_results / $per_page);
        if (!$num_pages) {
            $num_pages = 1;
        }

        if ($page > $num_pages) {
            $page = $num_pages;
        }

        $range_start = max(1, $page - floor(($pad - 1) / 2));
        $range_end   = max(min($num_pages, $page + floor(($pad - 1) / 2)), $pad);

        if ($range_end > $num_pages) {
            $range_end = $num_pages;
        }

        $info['per_page']      = $per_page;
        $info['pages']         = range($range_start, $range_end);
        $info['prev']          = ($page != 1) ? $page - 1 : false;
        $info['next']          = ($page < $num_pages) ? $page + 1 : false;
        $info['first']         = 1;
        $info['last']          = $num_pages;
        $info['curpage']       = $page;
        $info['total_results'] = $num_results;
        $info['first_result']  = (($page - 1) * $per_page) + 1;
        $info['last_result']   = (($page - 1) * $per_page) + $per_page;

        $info['curpage'] = self::bound($info['curpage'], 1, $info['last']);

        $info['cursor'] = $page;
        $info['limit']  = $per_page;

        return $info;
    }

    /**
     * Parses a filesize where the size may be expressed in php.ini shorthand notation with suffixes K, M or G.
     * The returned size is in bytes.
     *
     * @param $val
     *
     * @return int
     *
     * @internal param $size_string
     */
    public static function parseIniSize($val)
    {
        $val  = trim($val);
        $last = strtoupper($val[strlen($val) - 1]);

        // Already in bytes
        if (ctype_digit($last)) {
            return (int) $val;
        }

        $val = (int) $val;

        // Invalid values also means assume bytes
        if ($last != 'G' && $last != 'M' && $last != 'K') {
            return $val;
        }

        switch ($last) {
            case 'G':
                $val *= 1024;
            case 'M':
                $val *= 1024;
            case 'K':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * @deprecated use Colors instead
     *
     * @param $hex
     *
     * @return array
     */
    public static function hex2rgb($hex)
    {
        return Colors::hex2rgb($hex);
    }

    /**
     * Get the ordinal suffix for a number.
     *
     * @param $number
     *
     * @return string
     */
    public static function ordinalSuffix($number)
    {
        if (!$number) {
            return '';
        }

        if ($number % 100 > 10 && $number % 100 < 14) {
            $suffix = 'th';
        } else {
            switch (substr($number, -1, 1)) {
                case '1':
                    $suffix = 'st';
                    break;
                case '2':
                    $suffix = 'nd';
                    break;
                case '3':
                    $suffix = 'rd';
                    break;
                default:
                    $suffix = 'th';
            }
        }

        return $suffix;
    }

    /**
     * True if input looks like a unix timestamp.
     * "Looks like" means a positive integer that is no longer than 10 chars.
     *
     * @param string $input
     *
     * @return bool
     */
    public static function isTimestamp($input)
    {
        return self::isInteger($input) && strlen($input) <= 10 && ctype_digit($input);
    }
}
