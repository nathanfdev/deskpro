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

// This is a very low-level check to make sure at least the fundamentals
// like namespaces will work (which even our requirements checker needs).
if (version_compare(phpversion(), '5.5.0', '<')) {
    echo "You are using a very old version of PHP that is incompatible with this software.\n\n";
    echo "Please refer to the server requirements here: https://www.deskpro.com/requirements.\n";
    echo "(ERR_CODE:MPHPVFT)";
    exit(1);
}
if (version_compare(phpversion(), '7.2.0alpha', '>')) {
    echo "You are using PHP 7.2.x that is incompatible with this software for now.\n\n";
    echo "Please refer to the server requirements here: https://www.deskpro.com/requirements.\n";
    echo "(ERR_CODE:MPHPVFT)";
    exit(1);
}

#------------------------------
# Normalize env
#------------------------------

@setlocale(LC_CTYPE, 'C');
@date_default_timezone_set('UTC');
@ini_set('default_charset', 'UTF-8');
@ini_set('zlib.output_compression', '0');
@ini_set('xdebug.max_nesting_level', 1000000);
libxml_disable_entity_loader(true);

// always show errors before boot because we need to make sure silly
// things like typo in a config file is highly visible
// (it's fine-tuned below to only show on cli or pre-install)
@ini_set('display_errors', '1');

define('DP_START_TIME', microtime(true));

#------------------------------
# Paths
#------------------------------

require __DIR__ . '/lib/DpRun/DpEnv.php';

$config = [];

if (defined('DP_USE_BUILD_NAME')) {
    $config['env'] = ['use_build_name' => DP_USE_BUILD_NAME];
}

if (php_sapi_name() === 'cli') {
    if (empty($config['env'])) {
        $config['env'] = [];
    }

    if (in_array('--no-debug', $_SERVER['argv'])) {
        $config['env']['debug_mode'] = false;
        $config['env']['no_debug']   = true;
    }

    $m = null;
    if (preg_match('/\-\-env=?\s*("|\')?(?P<env>[a-zA-Z0-9]+)/', implode(' ', $_SERVER['argv']), $m)) {
        $config['env']['environment'] = $m['env'];
    }
}

if (isset($_SERVER['DESKPRO_USE_ROOT_DIR'])) {
    $dp_root = $_SERVER['DESKPRO_USE_ROOT_DIR'];
} else if (defined('DESKPRO_USE_ROOT_DIR')) {
    $dp_root = DESKPRO_USE_ROOT_DIR;
} else {
    $dp_root = __DIR__.'/../../';
}

if (isset($_SERVER['DESKPRO_USE_CONFIG_DIR'])) {
    $config['use_config_dir'] = $_SERVER['DESKPRO_USE_CONFIG_DIR'];
} else if (defined('DESKPRO_USE_CONFIG_DIR')) {
    $config['use_config_dir'] = DESKPRO_USE_CONFIG_DIR;
}

$DP_ENV = new \DpRun\DpEnv($dp_root, $config ?: null);

/**
 * The root path to DeskPRO.
 */
define('DP_DIR', $DP_ENV->getDpRoot());

/**
 * The path to the currently active build.
 */
define('DP_APP_DIR', $DP_ENV->getAppDir());

/**
 * The name of the currently active build.
 */
define('DP_ACTIVE_BUILD', $DP_ENV->getAppName());

/**
 * The name of the currently active env (prod, dev, test)
 */
define('DP_ENV_ID', $DP_ENV->getEnvId());

#------------------------------
# Legacy path defs
#------------------------------

/**
 * This is the path to the currently active build.
 * Use DP_APP_DIR instead.
 *
 * @deprecated
 */
define('DP_ROOT', $DP_ENV->getAppDir());
define('PCLZIP_TEMPORARY_DIR', $DP_ENV->getUserTmpDir().DIRECTORY_SEPARATOR);

/**
 * This is the path to the currently active build within the www dir.
 * This should NOT be necessary.
 *
 * @deprecated
 */
define('DP_WEB_ROOT', $DP_ENV->getAppWwwAssetDir());

$buildInfo = null;
if (file_exists($DP_ENV->getAppDir() . '/sys/config/build-info.php')) {
    $buildInfo = require($DP_ENV->getAppDir() . '/sys/config/build-info.php');
}

$readSysFile = function($name, $default = 0) use ($DP_ENV, $buildInfo) {
    if ($buildInfo && array_key_exists($name, $buildInfo)) {
        return $buildInfo[$name];
    }
    $f = $DP_ENV->getAppDir() . '/sys/config/' . $name . '.txt';
    if (file_exists($f)) {
        return trim(file_get_contents($f));
    }
    return $default;
};

/**
 * The current build number: 15839
 * @deprecated Use AppEnv::getBuildId
 */
define('DP_BUILD_NUM',  $readSysFile('build-num', 0));

/**
 * The current build time stamp
 * @deprecated Use AppEnv::getBuildTime
 */
define('DP_BUILD_TIME', $readSysFile('build-time', 1323444089 /* magic value, used when someone has not built yet */));

#------------------------------
# Erorr handling
#------------------------------

@ini_set('log_errors', true);

define('DP_REAL_ERROR_LOG', @ini_get('error_log'));

// Attempt to set a log file if its not set
if (!@ini_get('error_log')) {
    @ini_set('error_log', $DP_ENV->getUserLogsDir().'/server-php.log');
}

// If DeskPRO is not installed yet, always enable display_errors
// so problems during an install process are not missed
if (!defined('DPC_IS_CLOUD') && !$DP_ENV->getConfig('database.host')) {
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

#------------------------------
# Memory Limits
#------------------------------

$parse_bytes = function($val) {
    $val = trim($val);
    $num = (int) $val;
    switch(strtolower($val[strlen($val)-1])) {
        case 'g': $num *= 1024;
        case 'm': $num *= 1024;
        case 'k': $num *= 1024;
    }
    return $num;
};

/**
 * The size memory_limit in bytes that is set in config.php
 */
define('DP_REAL_MEMSIZE', $parse_bytes(@ini_get('memory_limit') ?: -1));

if (DP_REAL_MEMSIZE && DP_REAL_MEMSIZE != '-1' && DP_REAL_MEMSIZE < 134217728/* 128 MB */) {
    // attempt to raise to at least 128 MB
    @ini_set('memory_limit', 134217728);
}

if (!defined('DP_MAX_MEMSIZE')) {
    /**
     * The max size DeskPRO should ever attempt to set itself.
     * This is used in email processing where the size is raised temporarily.
     */
    define('DP_MAX_MEMSIZE', max(512 * 1024 * 1024, DP_REAL_MEMSIZE));
}

/**
 * The memory size in bytes. This is the same as `ini_get('memory_limit')`,
 * except it's always in bytes (or -1).
 */
define('DP_USE_MEMSIZE', $parse_bytes(@ini_get('memory_limit') ?: -1));

#------------------------------
# Time limit
#------------------------------

define('DP_REAL_MAX_EXEC_TIME', @ini_get('max_execution_time') ?: 0);

// Disable time limit on cli
if (php_sapi_name() === 'cli') {
    @set_time_limit(0);

// Set time limit to 40s unless there's a config saying not to
// 40s should be enough for any normal script to complete
// (The time limit is raised during cron run for things like email processing)
} else if (!$DP_ENV->getConfig('settings.no_set_time_limit')) {
    @set_time_limit(40);
}

#------------------------------
# Compat
#------------------------------

require __DIR__.'/lib/compat/load_compat.php';

#------------------------------
# Custom init part
#------------------------------

if ($init_scripts = $DP_ENV->getConfig('env.init_scripts')) {
    if (is_array($init_scripts)) {
        array_map(function ($f) {
            $f = str_replace(
                [ 'DP_DIR', 'DP_APP_DIR', 'DP_ACTIVE_BUILD', 'DP_ENV_ID' ],
                [ DP_DIR, DP_APP_DIR, DP_ACTIVE_BUILD, DP_ENV_ID ],
                $f
            );
            if (is_file($f)) {
                global $DP_ENV;
                require($f);
            }
        }, $init_scripts);
    }
}

if ($DP_ENV->getConfig('env.init_fn')) {
    call_user_func($DP_ENV->getConfig('env.init_fn'), $DP_ENV);
}
