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

require DP_ROOT.'/sys/boot_web.php';
