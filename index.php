<?php

/**
 * You may wish to turn the display of PHP errors off.
 * You should monitor your PHP error log (location defined in php.ini) if you do.
 */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

/**
 * The path to the 'deskpro_backend' directory.
 * If you move the deskpro_backend directory, you must update this path.
 */
define('DP_ROOT', dirname(__FILE__) . '/app');

/**
 * The path to the config.php file.
 * You may wish to move the config file outisde of the webroot.
 * If you move the config.php file, you must update this path.
 */
define('DP_CONFIG_FILE', dirname(__FILE__) . '/config.php');

/**
 * You should not change anything below this line.
 */
define('DP_WEB_ROOT', dirname(__FILE__));
require DP_ROOT . '/sys/preboot.php';

if (!defined('DP_BOOT_MODE')) define('DP_BOOT_MODE', 'web');
switch (DP_BOOT_MODE) {
	case 'cron':        require DP_ROOT.'/sys/boot_cron.php';    break;
	case 'cli':         require DP_ROOT.'/sys/boot_cli.php';     break;
	case 'web':         require DP_ROOT.'/sys/boot_web.php';     break;
	case 'serve_file':  require DP_ROOT.'/sys/serve_file.php';   break;
	case 'upgrade':     require DP_ROOT.'/sys/boot_upgrade.php'; break;
}
