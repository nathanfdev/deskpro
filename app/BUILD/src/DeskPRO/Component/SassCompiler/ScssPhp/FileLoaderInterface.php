<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\ScssPhp;

interface FileLoaderInterface
{
    /**
     * Given a requested path, load the file.
     *
     * This should return a string when successful, or NULL if the file could not be loaded.
     *
     * @param string $file
     *
     * @return string|null
     */
    public function loadFile($path);
}
