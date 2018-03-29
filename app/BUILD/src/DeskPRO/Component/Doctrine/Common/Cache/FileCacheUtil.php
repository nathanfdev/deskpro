<?php

namespace DeskPRO\Component\Doctrine\Common\Cache;

class FileCacheUtil
{
    private function __construct()
    {
    }

    /**
     * Similar to Doctrine\Common\Cache::getFilename except we always return the hash, never the
     * filename bin2hex.
     *
     * This is important because we run the build on Linux, but we have
     * customers using Windows where long filenames break. So the solution is to just ALWAYS
     * use a hash filename.
     *
     * @param string $id
     * @param string $directory
     * @param string $extension
     *
     * @return string
     */
    public static function getFilename($id, $directory, $extension)
    {
        $hash     = hash('sha256', $id);
        $filename = '_'.$hash;

        return $directory
        .DIRECTORY_SEPARATOR
        .substr($hash, 0, 2)
        .DIRECTORY_SEPARATOR
        .$filename
        .$extension;
    }
}
