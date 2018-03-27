<?php

//------------------------------
// Normalize env
//------------------------------

@setlocale(LC_CTYPE, 'C');
@date_default_timezone_set('UTC');
@ini_set('default_charset', 'UTF-8');
@ini_set('zlib.output_compression', '0');
@ini_set('xdebug.max_nesting_level', 1000000);
libxml_disable_entity_loader(true);

//------------------------------
// Paths
//------------------------------

require __DIR__.'/../../../app/run/lib/DpRun/DpEnv.php';

$config_reader = new \DpRun\ConfigReader([__DIR__.'/config']);
$config_reader->getConfig('all');
$DP_ENV = new \DpRun\DpEnv(__DIR__.'/../../../', [], $config_reader);

// needed for behat
$GLOBALS['DP_ENV'] = $DP_ENV;

define('DP_DIR', $DP_ENV->getDpRoot());
define('DP_APP_DIR', $DP_ENV->getAppDir());
define('DP_ACTIVE_BUILD', $DP_ENV->getAppName());
define('DP_ENV_ID', $DP_ENV->getEnvId());
define('DP_ROOT', $DP_ENV->getAppDir());
define('DP_WEB_ROOT', $DP_ENV->getAppWwwAssetDir());
define('DP_BUILD_NUM',  0);
define('DP_BUILD_TIME', 1323444089);

//------------------------------
// Erorr handling
//------------------------------

@ini_set('log_errors', true);
@ini_set('display_errors', '1');
error_reporting(E_ALL);

//------------------------------
// Boot libs
//------------------------------

define('DP_INTERFACE', 'test');

require DP_APP_DIR.'/sys/Boot/Boot.php';
\DpSys\Boot\Boot::runBootTasks($DP_ENV, [
    'Loader',
    'Lib',
    'PreparePaths',
]);
libxml_disable_entity_loader(false);

//------------------------------
// Set lic loader
//------------------------------

\DpSys\License::setLoaderFunction(function () {
    $CONFIG = require __DIR__.'/config/config.all.php';
    $code = $CONFIG['settings']['core.license'];

    return ['install_key' => '', 'license_code' => $code];
});
