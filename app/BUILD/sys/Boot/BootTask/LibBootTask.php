<?php

namespace DpSys\Boot\BootTask;

use DeskPRO\Component\Filesystem\SafeFile;
use Doctrine\ORM\UnitOfWork;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Debug\Debug;
use Zend\Code\Reflection\MethodReflection;

/**
 * This makes sure the require lib files are included and basic env stuff is set.
 */
class LibBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        // We include this here because a PHP warning to do with `continue 2` is emitted
        // which normally in the debug loader is a fatal error.
        // So we do it manually here ourselves so we can ignore the warning
        foreach ([
            MethodReflection::class,
            UnitOfWork::class
        ] as $className) {
            @class_exists($className);
        }

        if ($env->isDebug()) {
            Debug::enable(-1, true);
        } else {
            set_error_handler(['DpSys\LowError\SystemErrorHandler', 'handleError'], E_ALL);
            set_exception_handler(['DpSys\LowError\SystemErrorHandler', 'handleException']);
            SystemErrorHandler::enableFatalErrorHandler();

            $bugsnagSettings = $env->getConfig('settings.bugsnag');
            if ($bugsnagSettings && @$bugsnagSettings['backend_api_key']) {
                SystemErrorHandler::setBugsnagConfig($bugsnagSettings);
            }
        }

        error_reporting(E_ALL & ~E_DEPRECATED);

        if ($env->isDebug() || php_sapi_name() === 'cli') {
            ini_set('display_errors', 1);
        } else {
            ini_set('display_errors', 0);
        }

        if (function_exists('newrelic_disable_autorum')) {
            @newrelic_disable_autorum();
        }

        // Normalise some env
        @setlocale(LC_CTYPE, 'C');
        @date_default_timezone_set('UTC');
        @ini_set('default_charset', 'UTF-8');
        @ini_set('zlib.output_compression', '0');
        @ini_set('xdebug.max_nesting_level', 1000000);

        // legacy
        require DP_APP_DIR.'/sys/load_config.php';

        \Orb\Util\Strings::setPhpUtf8Dir(DP_APP_DIR.'/vendor-src/php-utf8');

        // Set mpdf temp dirs
        if (!defined('_MPDF_TEMP_PATH')) {
            define('_MPDF_TEMP_PATH', $this->getTmpDir($env, 'mpdf').DIRECTORY_SEPARATOR);
        }
        if (!defined('_MPDF_TTFONTDATAPATH')) {
            define('_MPDF_TTFONTDATAPATH', $this->getTmpDir($env, 'mpdf_ttffontdata').DIRECTORY_SEPARATOR);
        }

        require DP_APP_DIR.'/src/DeskPRO/Component/Filesystem/SafeFile.php';
        SafeFile::setEmitWarningsOption(true);
        SafeFile::addBlacklistDir($env->getDpRoot().DIRECTORY_SEPARATOR.'config');
        SafeFile::addBlacklistDir($env->getUserBackupsDir());
        SafeFile::addBlacklistDir($env->getUserFilesDir());
        SafeFile::addBlacklistDir('/etc');
        SafeFile::addBlacklistDir('/dev');
        SafeFile::addBlacklistDir('/proc');
        SafeFile::addBlacklistDir('/run');
        SafeFile::addBlacklistDir('/var/log');

        if ($env->getConfig('env.load_lib_fn')) {
            call_user_func($env->getConfig('env.load_lib_fn'), $env, $GLOBALS['DP_AUTOLOADER']);
        }

        // Auto-load code plugins that have an auto-init file
        if (is_dir($env->getBuildDirRoot().'/scripts/codeplugins')) {
            $dir = new \DirectoryIterator($env->getBuildDirRoot().'/scripts/codeplugins');
            foreach ($dir as $f) {
                if ($f->isDir() && !$f->isDot() && file_exists($f->getPathname().'/config/setup.php')) {
                    $DP_ENV = $env;
                    $loader = $GLOBALS['DP_AUTOLOADER'];
                    require_once($f->getPathname().'/config/setup.php');
                }
            }
        }
    }

    /**
     * @param \DpRun\DpEnv $env
     * @param              $dir
     *
     * @return string
     */
    private function getTmpDir(\DpRun\DpEnv $env, $dir)
    {
        $tmpDir = $env->getUserTmpDir().DIRECTORY_SEPARATOR.$dir;
        if (!is_dir($tmpDir)) {
            if (!@mkdir($tmpDir, 0777, true)) {
                $tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.$dir;
                if (!is_dir($tmpDir)) {
                    @mkdir($tmpDir, 0777, true);
                }
            }
        }

        return $tmpDir;
    }
}
