<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * Orb
 *
 * @package Orb
 * @category Util
 */

namespace Orb\Util;

/**
 * Helper files for working with files
 *
 * @static
 */
class Files
{
    private function __construct() {}


    /**
     * @param  string|resource   $file_or_fp Path to a filename or an existing file resource
     * @param  int               $size       How many bytes from the end to read
     * @return string
     * @throws \RuntimeException
     */
    public static function readFromEnd($file_or_fp, $size)
    {
        if (is_resource($file_or_fp)) {
            $fp = $file_or_fp;
            $did_open = false;
        } else {
            $fp = @fopen($file_or_fp, 'r');
            $did_open = true;

            if (!$fp) {
                throw new \RuntimeException("Failed to open file");
            }
        }

        fseek($fp, -$size, \SEEK_END);

        $result = @fread($fp, $size);

        if ($did_open) @fclose($fp);

        if ($result === false) {
            throw new \RuntimeException("Failed to seek");
        }

        return $result;
    }
}
