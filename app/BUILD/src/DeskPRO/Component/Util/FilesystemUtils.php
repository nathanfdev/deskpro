<?php

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
