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

namespace DpSys\Boot\BootTask;

use DeskPRO\Component\Filesystem\SafeFile;
use Symfony\Component\Debug\Debug;

/**
 * This makes sure the require lib files are included.
 */
class LibBootTask implements BootTaskInterface
{
    public function run(\DpEnv $env, array $resources)
    {
        if ($env->isDebug()) {
            Debug::enable(true, true);
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

        require DP_APP_DIR.'/sys/load_config.php';

        \Orb\Util\Strings::setPhpUtf8Dir(DP_APP_DIR.'/vendor-src/php-utf8');

        require DP_APP_DIR.'/src/DeskPRO/Component/Filesystem/SafeFile.php';
        SafeFile::setEmitWarningsOption(true);
        SafeFile::addBlacklistDir($env->getDpRoot().DIRECTORY_SEPARATOR.'config');
        SafeFile::addBlacklistDir($env->getUserBackupsDir());
        SafeFile::addBlacklistDir($env->getUserFilesDir());
    }
}
