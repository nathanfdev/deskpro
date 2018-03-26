<?php

/**
 * Orb.
 *
 * @category Util
 */

namespace Orb\Util;

/**
 * Helper files for working with files.
 *
 * @static
 */
class Files
{
    private function __construct()
    {
    }

    /**
     * @param string|resource $file_or_fp Path to a filename or an existing file resource
     * @param int             $size       How many bytes from the end to read
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function readFromEnd($file_or_fp, $size)
    {
        if (is_resource($file_or_fp)) {
            $fp       = $file_or_fp;
            $did_open = false;
        } else {
            $fp       = @fopen($file_or_fp, 'r');
            $did_open = true;

            if (!$fp) {
                throw new \RuntimeException('Failed to open file');
            }
        }

        fseek($fp, -$size, \SEEK_END);

        $result = @fread($fp, $size);

        if ($did_open) {
            @fclose($fp);
        }

        if ($result === false) {
            throw new \RuntimeException('Failed to seek');
        }

        return $result;
    }
}
