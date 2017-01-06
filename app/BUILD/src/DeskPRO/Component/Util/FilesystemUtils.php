<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Component\Util;

class FilesystemUtils
{
    private function __construct()
    {
    }

    /**
     * Check if a directory is empty.
     *
     * @param string $dir
     *
     * @return bool
     */
    public static function isDirEmpty($dir)
    {
        if (!is_dir($dir)) {
            throw new \RuntimeException('Not a directory');
        }
        if (!is_readable($dir)) {
            throw new \RuntimeException('Cannot read directory');
        }

        $h = dir($dir);
        while (($f = $h->read()) !== false) {
            if ($f !== '.' && $f !== '..') {
                $h->close();

                return false;
            }
        }

        $h->close();

        return true;
    }

    /**
     * Given one or more params of strings or arrays, concat them all into a path
     * using DIRECTORY_SEPERATOR.
     *
     * @param array $parts
     *
     * @return string
     */
    public static function concatPath($parts)
    {
        $all = [];

        $parts = func_get_args();
        foreach ($parts as $part) {
            $part = (array) $part;
            foreach ($part as $p) {
                $all[] = $p;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $all);
    }
}
