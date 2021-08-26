<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

class ZipBombScanner
{
    /**
     * Max allowable items in archive
     */
    const MAX_ARCHIVE_CONTENTS = 4096;

    /**
     * Returns TRUE of the "true" size of the zip is within a limit
     *
     * @param string $filepath
     * @param int $limitInMb Size limit in megabytes
     * @return bool
     */
    public function isUnderSizeLimit($filepath, $limitInMb)
    {
        $zip = new \ZipArchive();
        $zip->open($filepath);

        $i = 0;
        $size = 0;

        while ($idx = $zip->statIndex($i++)) {
            if ($i > self::MAX_ARCHIVE_CONTENTS) {
                return false;
            }

            $size += $idx['size'];
        }

        $zip->close();

        if ($size === 0) {
            return true;
        }

        $sizeMb = ($size / 1024) / 1024;

        return ($sizeMb <= $limitInMb);
    }
}
