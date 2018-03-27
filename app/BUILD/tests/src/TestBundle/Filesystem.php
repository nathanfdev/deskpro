<?php

namespace DpTestSrc\TestBundle;

use Symfony\Component\Finder\Finder;

/**
 * Class Filesystem.
 */
class Filesystem
{
    /**
     * @param string $dir
     */
    public static function cleanDir($dir)
    {
        $fs     = new \Symfony\Component\Filesystem\Filesystem();
        $finder = Finder::create()->depth(0)->in($dir);
        foreach ($finder as $path) {
            $fs->remove((string) $path);
        }
    }
}
