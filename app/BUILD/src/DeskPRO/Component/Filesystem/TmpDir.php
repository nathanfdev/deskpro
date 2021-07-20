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
     * @var bool
     */
    private $isInit = false;

    /**
     * A shared tmp dir for the current process. This is a small performance
     * thing; random uses of temp files (e.g. tempName) can use the same
     * dir and we can avoid lots of io.
     *
     * @return TmpDir
     */
    public static function getSharedTempNameDir()
    {
        static $tmpdir;

        if (!$tmpdir) {
            $tmpdir = new self(false);
        }

        return $tmpdir;
    }

    /**
     * Create a tmp dir and return the path.
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
     * @param string $ext Optionally specify a file ext
     * @return string
     */
    public static function makeTmpFile($ext = null)
    {
        return self::getSharedTempNameDir()->tempName($ext);
    }

    /**
     * @return string
     */
    public static function getSysTempDir()
    {
        return sys_get_temp_dir();
    }

    /**
     * @param bool $initNow True to create the directory immediately, false will wait until a method is called
     */
    public function __construct($initNow = true)
    {
        do {
            $path = self::getSysTempDir().DIRECTORY_SEPARATOR.'tmp_'.date('YmdHis').'_'.RandUtils::randomString(20, 'alpha_iu');
        } while (file_exists($path));

        $this->path = $path;

        if ($initNow) {
            $this->initNow();
        }

        register_shutdown_function([$this, 'cleanup']);
    }

    private function initNow()
    {
        if ($this->isInit) {
            return;
        }

        $this->isInit = true;

        @mkdir($this->path, 0600, true);
        if (!is_dir($this->path)) {
            throw new \RuntimeException('Could not create tmp dir ('.error_get_last().')');
        }
    }

    /**
     * @return string
     */
    public function tempName($ext)
    {
        $this->initNow();

        $tmp = new self();
        $name = uniqid('f', true);
        $path = $tmp->getPath() . DIRECTORY_SEPARATOR . $name . ($ext ? '.'.$ext : '');
        @touch($path);
        @chmod($path, 0600);

        return $path;
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
        $this->initNow();

        return $this->path;
    }
}
