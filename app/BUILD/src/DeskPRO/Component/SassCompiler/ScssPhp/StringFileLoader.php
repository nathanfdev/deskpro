<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\SassCompiler\ScssPhp;

class StringFileLoader implements FileLoaderInterface, FileLocatorInterface
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @param array $files
     */
    public function __construct(array $files)
    {
        foreach ($files as $path => $f) {
            $m = null;
            if (preg_match('#^@alias:(.*?)$#', trim($f), $m)) {
                $this->aliases[$path] = $m[1];
            } else {
                $this->files[$path] = $f;
            }
        }
    }

    /**
     * Given a requested path, load the file.
     *
     * This should return a string when successful, or NULL if the file could not be loaded.
     *
     * @param string $file
     *
     * @return string|null
     */
    public function loadFile($path)
    {
        if (isset($this->files[$path])) {
            return $this->files[$path];
        }

        return;
    }

    /**
     * Given a requested path, return the real path (as it will be passed to loaders).
     *
     * These are used to resolve include paths etc.
     *
     * @param string $file
     *
     * @return string|null
     */
    public function locateFile($path)
    {
        $path = preg_replace('#\.scss$#', '', $path).'.scss';

        if (isset($this->aliases[$path])) {
            return $this->aliases[$path];
        }

        return isset($this->files[$path]) ? $path : null;
    }
}
