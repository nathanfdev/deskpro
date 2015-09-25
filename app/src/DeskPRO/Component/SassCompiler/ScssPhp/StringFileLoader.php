<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Component\SassCompiler\ScssPhp;

class StringFileLoader implements FileLoaderInterface, FileLocatorInterface
{
    /**
     * @var array
     */
    private $files = array();

    /**
     * @var array
     */
    private $aliases = array();

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
