<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
     * @param int    $index  The index to return, same rules. Or if -1 or null, all matches.
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
}
