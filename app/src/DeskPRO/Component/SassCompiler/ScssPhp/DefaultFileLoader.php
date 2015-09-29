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

class DefaultFileLoader implements FileLoaderInterface
{
    /**
     * @var array
     */
    private $whitelist_paths = array();

    /**
     * @param array|null $whitelist_paths Optionally supply a list of paths you can read from. Every other path will throw an exception.
     */
    public function __construct(array $whitelist_paths = null)
    {
        foreach ($whitelist_paths as $path) {
            $p = @realpath($path);
            if ($p) {
                $p                       = strtolower($p);
                $this->whitelist_paths[] = $p;
            }
        }

        // Passed options but none were real paths, so we
        // need to add a bogus path just so the rest of the class
        // works under the assumption that jailing is enabled
        if ($whitelist_paths && !$this->whitelist_paths) {
            $this->whitelist_paths[] = 'bogus_'.md5(uniqid('', true));
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
        if (file_exists($path)) {
            if ($this->whitelist_paths) {
                $realpath_l = strtolower(realpath($path));
                $ok         = false;
                foreach ($this->whitelist_paths as $p) {
                    if (strpos($realpath_l, $p) === 0) {
                        $ok = true;
                        break;
                    }
                }
                if (!$ok) {
                    throw new \InvalidArgumentException('Not in allowed paths');
                }
            }

            return file_get_contents($path);
        }

        return;
    }
}
