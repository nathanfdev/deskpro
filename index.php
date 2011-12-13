<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

/**
 * The path to the 'appfiles' directory.
 * If you move that directory, you must update this path.
 */
define('DP_ROOT', __DIR__ . '/appfiles');

/**
 * The path to the config.php file.
 * If you want to that file, you must update this path.
 */
define('DP_CONFIG_FILE', __DIR__ . '/config.php');

if (!defined('DP_BOOT_MODE')) define('DP_BOOT_MODE', 'web');

switch (DP_BOOT_MODE) {
	case 'cron': require DP_ROOT.'/sys/boot_cron.php'; break;
	case 'cli':  require DP_ROOT.'/sys/boot_cli.php'; break;
	case 'web':  require DP_ROOT.'/sys/boot_web.php'; break;
}
