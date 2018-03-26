<?php

namespace DeskPRO\Component\Filesystem;

use DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException;

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
     * @throws DbBackupException if can't open file
     *
     * @return int|float|false File size on success or (bool) FALSE on error
     */
    public static function getFileSize($filename)
    {
        $return = false;

        $fp = @fopen($filename, 'r');
        if (!$fp && $error = error_get_last()) {
            throw new DbBackupException($error['message'], DbBackupException::DUMP_ERROR_OPEN_FAILED);
        }
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
