<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpSys\Boot\BootTask;

use DeskPRO\Component\Filesystem\SafeFile;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Debug\Debug;

/**
 * This makes sure the require lib files are included and basic env stuff is set.
 */
class LibBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        if ($env->isDebug()) {
            Debug::enable(-1, true);
        } else {
            set_error_handler([SystemErrorHandler::class, 'handleError'], E_ALL);
            set_exception_handler([SystemErrorHandler::class, 'handleException']);
            SystemErrorHandler::enableFatalErrorHandler();

            $bugsnagSettings = $env->getConfig('settings.bugsnag');
            if ($bugsnagSettings && @$bugsnagSettings['backend_api_key']) {
                SystemErrorHandler::setBugsnagConfig($bugsnagSettings);
            }
        }

        error_reporting(E_ALL);

        if ($env->isDebug() || php_sapi_name() === 'cli') {
            ini_set('display_errors', 1);
        } else {
            ini_set('display_errors', 0);
        }

        if (!defined('DP_BUILD_TIME')) {
            if (file_exists(DP_APP_DIR.'/sys/config/build-time.php')) {
                require DP_APP_DIR.'/sys/config/build-time.php';
            } else {
                define('DP_BUILD_TIME', 1323444089); // would be used by someone who hasnt built yet
            }
        }

        if (!defined('DP_BUILD_NUM')) {
            if (file_exists(DP_APP_DIR.'/sys/config/build-num.php')) {
                require DP_APP_DIR.'/sys/config/build-num.php';
            } else {
                define('DP_BUILD_NUM', 0); // would be used by someone who isnt using default distro
            }
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
