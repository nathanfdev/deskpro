<?php

namespace DeskPRO\Component\Util;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Blob;

/**
 * Utility methods working with strings.
 */
class StringUtils
{
    private function __construct()
    {
    }

    /**
     * Simple string formatting. Use {varname} to replace that token with the value in the $vars array.
     * Use {varname:format} to specify sprintf-compatible formatting, such as {count:010d} (0 padded integer).
     *
     * If you need to use a literal {thing} sequence, use a var with that value as the replacement.
     *
     * @param string $format  The format string. Example: Hello, {name: 15s}
     * @param array  $vars
     * @param string $default The default value to use if a var is referenced in $format that does not exist in $vars
     *
     * @return string
     */
    public static function format($format, array $vars, $default = '__EXCEPTION__')
    {
        $values     = [];
        $realFormat = str_replace('%', '%%', $format);

        $realFormat = preg_replace_callback('#\{(?P<name>[a-zA-Z0-9\-_\.]+)(?::(?P<format>.*?))?\}#', function (array $match) use (&$values, $vars, $default) {
            $name = $match['name'];
            if (array_key_exists($name, $vars)) {
                $val = $vars[$name];
            } else {
                if ($default === '__EXCEPTION__') {
                    throw new \InvalidArgumentException('Unknown named argument');
                }
                $val = $default;
            }

            $values[] = $val;

            $format = '%s';
            if (!empty($match['format'])) {
                $format = '%'.$match['format'];
            }

            return $format;
        }, $realFormat);

        return vsprintf($realFormat, $values);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function toSnakeCase($string)
    {
        $string = preg_replace('/(.)([A-Z][a-z]+)/', '$1_$2', $string);
        $string = preg_replace('/(.)([0-9]+)/', '$1_$2', $string);
        $string = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $string);

        return ltrim(strtolower($string), '_');
    }

    /**
     * @param string $string
     * @param bool   $upper
     *
     * @return string
     */
    public static function toCamelCase($string, $upper = true)
    {
        $result = implode('', array_map('ucfirst', explode('_', $string)));

        return $upper ? $result : lcfirst($result);
    }

    /**
     * Remove a prefix from the string. If the string does not include the prefix
     * (i.e. it fails startsWith() check), then null is returned instead.
     *
     * <code>
     * $a = 'foo.bar.baz';
     * echo StringUtils::removeFromStart('foo.bar.', $a); // => baz
     *
     * $b = 'foo.bar.baz';
     * echo StringUtils::removeFromStart('loo.bar.', $b); // => null
     * </code>
     *
     * @param string $needle
     * @param string $haystack
     * @param bool   $ignoreCase
     *
     * @return string
     */
    public static function removeFromStart($needle, $haystack, $ignoreCase = false)
    {
        if (!self::startsWith($needle, $haystack, $ignoreCase)) {
            return null;
        }

        return substr($haystack, strlen($needle));
    }

    /**
     * Check if $needle is at the beginning of $haystack.
     *
     * @param string $needle     The string to search for
     * @param string $haystack   The string to search in
     * @param bool   $ignoreCase True to ignore case
     *
     * @return bool
     */
    public static function startsWith($needle, $haystack, $ignoreCase = false)
    {
        if ($needle === $haystack) {
            return true;
        }

        if ($needle === '' || $haystack === '') {
            return false;
        }

        if ($ignoreCase) {
            return stripos($haystack, $needle) === 0;
        } else {
            return strpos($haystack, $needle) === 0;
        }
    }

    /**
     * Check if $needle is at the end of $haystack.
     *
     * @param string $needle     The string to search for
     * @param string $haystack   The string to search in
     * @param bool   $ignoreCase True to ignore case
     *
     * @return bool
     */
    public static function endsWith($needle, $haystack, $ignoreCase = false)
    {
        if ($needle === $haystack) {
            return true;
        }

        if ($needle === '' || $haystack === '') {
            return false;
        }

        $haystackLen = strlen($haystack);
        $needleLen   = strlen($needle);

        if ($needleLen > $haystackLen) {
            return false;
        }

        return substr_compare($haystack, $needle, $haystackLen - $needleLen, $needleLen, $ignoreCase) === 0;
    }

    /**
     * Standarize the end-of-line character in a string.
     *
     * @param string $string The string to work on
     * @param string $eol    The end of line character to use
     *
     * @return string
     */
    public static function standardEol($string, $eol = "\n")
    {
        return str_replace([
            "\r\n",
            "\r",
            "\n",
        ], $eol, $string);
    }

    /**
     * @param string $string
     *
     * @return array
     */
    public static function lines($string)
    {
        return explode("\n", self::standardEol($string));
    }

    /**
     * Maps a function to every line of a string, then returns the new string.
     *
     * @param string   $string The string
     * @param callable $fn     ($line) -> string
     */
    public static function mapLines($string, $fn = null)
    {
        return implode("\n", array_map($fn, self::lines($string)));
    }

    /**
     * Similar to format() except we use the string itself as a variable along with matches from a regex.
     * The string itself is stored as {.}.
     *
     * @param string      $string
     * @param string      $format Format. Use {} for the string itself, or if using regex to match parts,
     *                            use {name} (e.g. {1} etc) for each part. Use {{ or }} for literal braces
     * @param null|string $regex
     * @param array       $vars
     */
    public static function reformatString($string, $format, $regex = null, array $vars = [])
    {
        if ($regex) {
            $vars = array_merge(RegexUtils::getMatches($regex, $string), $vars);
        }

        $vars['.'] = $string;

        return self::format($format, $vars, '');
    }

    /**
     * Same as reformatString except its run on every line.
     *
     * @param string      $string
     * @param string      $format
     * @param null|string $regex
     * @param array       $vars
     */
    public static function reformatLines($string, $format, $regex = null, array $vars = [])
    {
        $lines = array_map(function ($l) use ($format, $regex, $vars) {
            return self::reformatString($l, $format, $regex, $vars);
        }, self::lines($string));

        return implode("\n", $lines);
    }

    /**
     * @param Blob   $blob
     * @param string $text
     *
     * @return int
     */
    public static function ensureAttachment(Blob $blob, $text)
    {
        $downloadUrl  = preg_quote($blob->getDownloadUrl(true), '#');
        $embedBlobA   = preg_quote('dp-embed-blob-a-'.$blob->getAuthId());
        $embedBlobImg = preg_quote('dp-embed-blob-img-'.$blob->getAuthId());

        $matches = RegexUtils::safePregMatch('#(<(img|video)[^>]+src=")'.$downloadUrl.'("[^>]*>)#i', $text);
        $matches = $matches ?: RegexUtils::safePregMatch('#(<a[^>]+href=")'.$downloadUrl.'("[^>]*>)#i', $text);
        $matches = $matches ?: RegexUtils::safePregMatch('#<a[^>]+'.$embedBlobA.'[^>]*>.*?</a>#', $text);
        $matches = $matches ?: RegexUtils::safePregMatch('#<img[^>]+'.$embedBlobImg.'[^>]>#', $text);

        return $matches;
    }

    /**
     * @param string       $text       html text which will be checked for blobs
     * @param string|false $urlPattern an url template for blobs
     *
     * @return array
     */
    public static function gatherInlineAttachments($text, $urlPattern = false)
    {
        $sets = [];
        $sets = array_merge($sets, RegexUtils::getAllMatchSets('#dp-embed-blob-a-([a-zA-Z0-9]+)#', $text) ?: []);
        $sets = array_merge($sets, RegexUtils::getAllMatchSets('#dp-embed-blob-img-([a-zA-Z0-9]+)#', $text) ?: []);
        if (!$urlPattern) {
            $urlPattern = App::get('router')->generate('serve_blob', ['blob_auth_id' => '00000', 'filename' => '11111'], true);
        }
        $url       = preg_quote($urlPattern);
        $url       = str_replace('00000', '([a-zA-Z0-9-]+)', $url);
        $url       = str_replace('11111', '[a-zA-Z0-9-_]+\.[a-z0-9A-Z]{1,4}', $url);
        $urlRegexp = '#'.$url.'#';
        $sets      = array_merge($sets, RegexUtils::getAllMatchSets($urlRegexp, $text) ?: []);

        return array_filter(array_map(function ($set) {
            return isset($set[1]) ? preg_quote($set[1]) : null;
        }, $sets), function ($match) {
            return $match == true;
        });
    }
}
