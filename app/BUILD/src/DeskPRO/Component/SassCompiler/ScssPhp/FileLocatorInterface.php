<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\ScssPhp;

interface FileLocatorInterface
{
    /**
     * Given a requested path, return the real path (as it will be passed to loaders).
     *
     * These are used to resolve include paths etc.
     *
     * @param string $file
     *
     * @return string|null
     */
    public function locateFile($path);
}
