<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Component\Filesystem;

use DeskPRO\Component\Util\RandUtils;

class TmpDir
{
    /**
     * @var string
     */
    private $path;

    /**
     * Create a tmp dir and return the path.
     *
     * @param $base_path
     *
     * @return string
     */
    public static function makeTmpDir($base_path)
    {
        $tmp = new self($base_path);

        return $tmp->getPath();
    }

    /**
     * @param string|null $base_path
     */
    public function __construct($base_path = null)
    {
        if (!$base_path) {
            $base_path = sys_get_temp_dir();
        }

        $base_path = @realpath($base_path);

        if (!$base_path || !is_writable($base_path)) {
            throw new \RuntimeException("Base tmp dir is not writable: $base_path");
        }

        do {
            $path = $base_path.DIRECTORY_SEPARATOR.'tmp_'.date('YmdHis').'_'.RandUtils::randomString(10, 'alpha_iu');
        } while (file_exists($path));

        @mkdir($path);
        if (!is_dir($path)) {
            throw new \RuntimeException('Could not create tmp dir: '.$path.' ('.error_get_last().')');
        }

        $this->path = $path;

        register_shutdown_function([$this, 'cleanup']);
    }

    /**
     * Cleans up the temporary directory.
     */
    public function cleanup()
    {
        if (!$this->path) {
            return;
        }

        $rm = function ($files) use (&$rm) {
            foreach ($files as $p) {
                if (is_dir($p) && !is_link($p)) {
                    $rm(new \FilesystemIterator($p));
                    @rmdir($p);
                } else {
                    @unlink($p);
                }
            }
        };
        if (is_dir($this->path)) {
            $rm(new \FilesystemIterator($this->path));
            @rmdir($this->path);
        }

        $this->path = null;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
