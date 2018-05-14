<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Util;

/**
 * Regex utils.
 */
class RegexUtils
{
    private function __construct()
    {
    }

    /**
     * Executes regex on a string and returns the match at index $index.
     *
     * If no matches were found, or if the index doesn't exist, null is returned.
     *
     * @param string $regex  Regex to run
     * @param string $string The string to run it on
     * @param int    $index  The index to return, same rules. Or if -1 or null, all matches
     * @param int    $flags  Flags to pass to preg_match
     * @param int    $offset Offset to pass to preg_match
     *
     * @return string|null
     */
    public static function getMatch($regex, $string, $index = 1, $flags = 0, $offset = 0)
    {
        $matches = null;
        if (!preg_match($regex, $string, $matches, $flags, $offset)) {
            return;
        }

        if ($index === -1 or $index === null) {
            return $matches;
        }

        return isset($matches[$index]) ? $matches[$index] : null;
    }

    /**
     * @param string $regex  Regex to run
     * @param string $string The string to run it on
     * @param int    $flags  Flags to pass to preg_match
     * @param int    $offset Offset to pass to preg_match
     *
     * @return string|null
     */
    public static function getMatches($regex, $string, $flags = 0, $offset = 0)
    {
        return self::getMatch($regex, $string, -1, $flags, $offset);
    }

    /**
     * @param string $regex
     * @param string $string
     * @param int    $offset
     *
     * @return null|array
     */
    public static function getAllMatchSets($regex, $string, $offset = 0)
    {
        $matches = null;
        if (!preg_match_all($regex, $string, $matches, \PREG_SET_ORDER, $offset)) {
            return;
        }

        return $matches;
    }

    /**
     * Same as preg_match but it is safe to use with user-supplied expressions.
     *
     * A malicious user might construct a regex pattern that can consume high amounts of CPU
     * by making the engine backtrack too many times.
     *
     * This function attempts to mitigate that by reducing the pcre.backtrack_limit setting
     * to something that won't be harmful. On the other hand, with a lower backtrack limit,
     * this means that the pattern might fail on larger strings that might otherwise have matched.
     *
     * @see https://www.owasp.org/index.php/Regular_expression_Denial_of_Service_-_ReDoS
     *
     * @param string $pattern
     * @param string $subject
     * @param array  $matches
     * @param int    $flags
     * @param int    $offset
     * @param int    $backtrackLimit
     *
     * @return int
     */
    public static function safePregMatch($pattern, $subject, &$matches = null, $flags = 0, $offset = 0, $backtrackLimit = 20000)
    {
        $iniLimit = (int) ini_get('pcre.backtrack_limit') ?: 1000000;
        ini_set('pcre.backtrack_limit', $backtrackLimit);

        $res = preg_match($pattern, $subject, $matches, $flags, $offset);

        ini_set('pcre.backtrack_limit', $iniLimit);

        return $res;
    }

    /**
     * @param string $pattern
     * @param string $subject
     * @param mixed  $matches
     * @param int    $flags
     * @param int    $offset
     * @param int    $backtrackLimit
     *
     * @return mixed
     */
    public static function safePregMatchAll($pattern, $subject, &$matches = null, $flags = 0, $offset = 0, $backtrackLimit = 20000)
    {
        $iniLimit = (int) ini_get('pcre.backtrack_limit') ?: 1000000;
        ini_set('pcre.backtrack_limit', $backtrackLimit);

        $res = preg_match_all($pattern, $subject, $matches, $flags, $offset);

        ini_set('pcre.backtrack_limit', $iniLimit);

        return $res;
    }

    /**
     * @param string $pattern
     * @param mixed  $replacement
     * @param mixed  $subject
     * @param int    $limit
     * @param null   $count
     * @param int    $backtrackLimit
     *
     * @see https://www.owasp.org/index.php/Regular_expression_Denial_of_Service_-_ReDoS
     *
     * @return mixed
     */
    public static function safePregReplace($pattern, $replacement, $subject, $limit = -1, &$count = null, $backtrackLimit = 20000)
    {
        $iniLimit = (int) ini_get('pcre.backtrack_limit') ?: 1000000;
        ini_set('pcre.backtrack_limit', $backtrackLimit);

        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);

        ini_set('pcre.backtrack_limit', $iniLimit);

        return $result;
    }
}
