<?php

namespace DeskPRO\Bundle\AppBundle\Zippy;

class ZipBombScanner
{
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
            $size += $idx['size'] ? ($idx['size'] / 1024) / 1024 : 0;

            if ($size > $limitInMb) {
                $zip->close();
                return false;
            }
        }

        $zip->close();

        return true;
    }
}
