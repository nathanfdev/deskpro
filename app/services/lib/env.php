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

if (php_sapi_name() != 'cli') {
    echo "This script must only be run from the CLI.\n";
    echo "Contact support@deskpro.com if you require assistance.\n";
    exit(1);
}

#------------------------------
# Normalize env
#------------------------------

if (!defined('DP_ROOT')) {
    define('DP_ROOT', realpath(__DIR__.'/../../'));
}

if (!defined('DP_WEB_ROOT')) {
    define('DP_WEB_ROOT', realpath(DP_ROOT.'/../'));
}

if (!defined('DP_CONFIG_FILE')) {
    define('DP_CONFIG_FILE', DP_WEB_ROOT.'/config.php');
}

@setlocale(LC_CTYPE, 'C');
@date_default_timezone_set('UTC');
@ini_set('default_charset', 'UTF-8');
@ini_set('zlib.output_compression', '0');
@ini_set('xdebug.max_nesting_level', 1000000);
@ini_set('memory_limit', -1);
@set_time_limit(0);

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

#------------------------------
# Attempt to set error log file if unset
#------------------------------

@ini_set('log_errors', true);
@ini_set('display_errors', '1');

define('DP_REAL_ERROR_LOG', @ini_get('error_log'));
if (!DP_REAL_ERROR_LOG) {
    if (defined('DP_BOOT_MODE') && (DP_BOOT_MODE == 'cron' || DP_BOOT_MODE == 'cli')) {
        @ini_set('error_log', dp_get_log_dir().'/server-phperr-cli.log');
    } else {
        @ini_set('error_log', dp_get_log_dir().'/server-phperr-web.log');
    }
}

#------------------------------
# Autoloader
#------------------------------

require DP_ROOT.'/vendor/symfony/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require DP_ROOT.'/src/Orb/Util/ClassLoader.php';
require DP_ROOT.'/sys/autoload.php';
require DP_ROOT.'/sys/DpShutdown.php';

#------------------------------
# Lib
#------------------------------

require __DIR__.'/db_functions.php';
