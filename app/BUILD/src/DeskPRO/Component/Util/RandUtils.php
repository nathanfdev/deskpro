<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

/**
 * Utility methods for generating random data.
 */
class RandUtils
{
    private function __construct()
    {
    }

    /**#@+
     * Strings of some common character ranges.
     * @see Strings::randomString()
     */
    const CHARS_ALPHANUM    = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const CHARS_ALPHANUM_I  = '0123456789abcdefghijklmnopqrstuvwxyz';
    const CHARS_ALPHANUM_IU = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_NUM         = '0123456789';
    const CHARS_ALPHA       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const CHARS_ALPHA_I     = 'abcdefghijklmnopqrstuvwxyz';
    const CHARS_ALPHA_IU    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHARS_SECURE      = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$%^&*()-_=+{}|[]:;,./<>?';
    /**#@-*/

    /**
     * Generate a random string.
     *
     * $chars is a string of possible characters. See the CHARS_* presets. You can specify a preset
     * by just specifying the name after the CHARS_ prefix. E.g., the string 'alphanum' is the same as using
     * the constant RandUtils::CHARS_ALPHANUM.
     *
     * If a falsy value is provided, then CHARS_ALPHANUM is used by default.
     *
     * @param int    $len
     * @param string $chars
     *
     * @return string
     */
    public static function randomString($len = 8, $chars = null)
    {
        if ($len < 1) {
            throw new \InvalidArgumentException('Length must be at least 1 character');
        }

        if (!$chars) {
            $chars = self::CHARS_ALPHANUM;
        } else {
            switch (strtoupper($chars)) {
                case 'ALPHANUM':    $chars = self::CHARS_ALPHANUM; break;
                case 'ALPHANUM_I':  $chars = self::CHARS_ALPHANUM_I; break;
                case 'ALPHANUM_IU': $chars = self::CHARS_ALPHANUM_IU; break;
                case 'NUM':         $chars = self::CHARS_NUM; break;
                case 'ALPHA':       $chars = self::CHARS_ALPHA; break;
                case 'ALPHA_I':     $chars = self::CHARS_ALPHA_I; break;
                case 'ALPHA_IU':    $chars = self::CHARS_ALPHA_IU; break;
                case 'SECURE':      $chars = self::CHARS_SECURE; break;
            }
        }

        $string    = '';
        $max_range = strlen($chars) - 1;

        for ($i = 0; $i < $len; ++$i) {
            $string .= $chars[mt_rand(0, $max_range)];
        }

        return $string;
    }

    /**
     * @param string $body   The body to make sure the string is unique in
     * @param string $format The format of the random string format
     *
     * @return string
     */
    public static function randomBodyToken($body, $format = '%h')
    {
        do {
            $tok = self::randomStringFormat($format);
        } while (strpos($body, $tok) !== false);

        return $tok;
    }

    /**
     * Generate a random string in a given format.
     *
     * $format can use these string specifiers:
     * - %A: Uppercase letter
     * - %a: Lowercase letter
     * - %c: Upper or lowercase letter
     * - %n: Number
     * - %An: Uppercase letter or number
     * - %an: Lowercase letter or number
     * - %cn: Upper or lowercase letter or number
     * - %g: Grammar character (!@#$%^&*()-_=+{}|[]:;,./<>?)
     * - %h: 40-character sha1 hash of random bits
     *
     * You can specify a number before the letter for length: %4n -- 4 numbers
     *
     * @param string $format
     *
     * @return string
     */
    public static function randomStringFormat($format)
    {
        if (!$format) {
            return '';
        }

        return preg_replace_callback('#%(\d*)(An|an|cn|A|a|c|n|g|h)#', function ($m) {
            $l = (int) $m[1] ?: 1;
            switch ($m[2]) {
                case 'A':  return RandUtils::randomString($l, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                case 'a':  return RandUtils::randomString($l, 'abcdefghijklmnopqrstuvwxyz');
                case 'c':  return RandUtils::randomString($l, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz');
                case 'n':  return RandUtils::randomString($l, '0123456789');
                case 'An': return RandUtils::randomString($l, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
                case 'an': return RandUtils::randomString($l, 'abcdefghijklmnopqrstuvwxyz0123456789');
                case 'cn': return RandUtils::randomString($l, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789');
                case 'g':  return RandUtils::randomString($l, '!@#$%^&*()-_=+{}|[]:;,./<>?');
                case 'h':  return sha1(time().mt_rand(1000, 9999).mt_rand(1000, 9999).mt_rand(1000, 9999).mt_rand(1000, 9999));
            }

            return $m[0];
        }, $format);
    }

    /**
     * @link http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid/15875555#15875555
     */
    public static function uuidV4()
    {
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
