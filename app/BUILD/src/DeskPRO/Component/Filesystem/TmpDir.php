<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Component\Filesystem;

use DeskPRO\Component\Util\RandUtils;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Filesystem\Filesystem;

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
    public static function makeTmpDir()
    {
        $tmp = new self();

        return $tmp->getPath();
    }

    /**
     * Create a new tmp file name
     *
     * @return string
     */
    public static function makeTmpFile()
    {
        $tmp = new self();
        $name = uniqid('f', true);

        return $tmp->getPath() . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @param string|null $base_path
     */
    public function __construct()
    {
        do {
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'tmp_'.date('YmdHis').'_'.RandUtils::randomString(20, 'alpha_iu');
        } while (file_exists($path));

        @mkdir($path, 0600, true);
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

        $fs = new Filesystem();

        try {
            $fs->remove($this->path);
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);

            // fallback to just making it unreadable
            @chmod($this->path, 0);
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
