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

namespace DeskPRO\Component\Filesystem;

/**
 * Class BigFile.
 */
class BigFile
{
    /**
     * Get the size of file, platform- and architecture-independant.
     * This function supports 32bit and 64bit architectures and works fith large files > 2 GB.
     * The return value type depends on platform/architecture: (float) when PHP_INT_SIZE < 8 or (int) otherwise.
     *
     * @see http://us.php.net/manual/en/function.filesize.php#115792
     *
     * @param string $filename
     *
     * @return int|float|false File size on success or (bool) FALSE on error
     */
    public static function getFileSize($filename)
    {
        $return = false;

        $fp = @fopen($filename, 'r');
        if (is_resource($fp)) {
            if (PHP_INT_SIZE < 8) {
                // 32bit
                if (0 === fseek($fp, 0, SEEK_END)) {
                    $return = 0.0;
                    $step   = 0x7FFFFFFF;
                    while ($step > 0) {
                        if (0 === fseek($fp, -$step, SEEK_CUR)) {
                            $return += floatval($step);
                        } else {
                            $step >>= 1;
                        }
                    }
                }
            } elseif (0 === fseek($fp, 0, SEEK_END)) {
                // 64bit
                $return = ftell($fp);
            }
        }

        return $return;
    }
}
