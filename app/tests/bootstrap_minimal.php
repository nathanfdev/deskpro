<?php
if (!defined('DP_ROOT')) {
    define('DP_ROOT', dirname(__FILE__) . '/../../app');
}
if (!defined('DP_WEB_ROOT')) {
    define('DP_WEB_ROOT', DP_ROOT . '/..');
}
define('DP_CONFIG_FILE', __DIR__ . '/config.test.php');
define('DP_TESTS_RUNNING', true);
define('DP_INTERFACE', 'user');
define('DP_TESTS_START_TIME', time());

require_once DP_ROOT . '/sys/preboot.php';
require_once DP_ROOT . '/sys/autoload.php';

set_time_limit(0);
ini_set('max_execution_time', 0);

\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT . '/vendor-src/php-utf8/');