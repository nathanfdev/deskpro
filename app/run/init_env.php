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

# Normalize env
#------------------------------

@setlocale(LC_CTYPE, 'C');
@date_default_timezone_set('UTC');
@ini_set('default_charset', 'UTF-8');
@ini_set('zlib.output_compression', '0');
@ini_set('xdebug.max_nesting_level', 1000000);
libxml_disable_entity_loader(true);

#------------------------------
# Paths
#------------------------------

require __DIR__.'/lib/DpEnv.php';
$DP_ENV = new DpEnv();

define('DP_APP_DIR', $DP_ENV->getAppDir());
define('DP_ACTIVE_BUILD', $DP_ENV->getActiveBuild());
define('DP_ENV_ID', $DP_ENV->getEnvId());

#------------------------------
# Legacy path defs
#------------------------------

/*
 * @deprecated
 */
define('DP_ROOT', $DP_ENV->getAppDir());

/*
 * @deprecated
 */
define('DP_WEB_ROOT', $DP_ENV->getWwwDir());

#------------------------------
# Erorr handling
#------------------------------

@ini_set('log_errors', true);

// Attempt to set a log file if its not set
if (!@ini_get('error_log')) {
    @ini_set('error_log', $DP_PATHS->getLogDir().'/server-php.log');
}

// If DeskPRO is not installed yet, always enable display_errors
// so problems during an install process are not missed
if (!file_exists($DP_PATHS->getHdDataDir().DIRECTORY_SEPARATOR.'is_installed.dat') && !defined('DPC_IS_CLOUD')) {
    @ini_set('display_errors', '1');

// also show errors on the CLI all the time too
} elseif (php_sapi_name() === 'cli') {
    @ini_set('display_errors', '1');

// otherwise, always turn off, its a security thing
} else {
    @ini_set('display_errors', '0');
}

// Increase error reporting
error_reporting(E_ALL);
