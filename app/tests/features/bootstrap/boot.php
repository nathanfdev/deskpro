<?php
define('DP_ROOT', __DIR__.'/../../..');
define('DP_WEB_ROOT', __DIR__.'/../../../..');
define('DP_CONFIG_FILE', __DIR__.'/../../config.test.php');
define('DP_TESTS_RUNNING', true);
define('DP_INTERFACE', 'user');
define('DP_TESTS_START_TIME', time());

require_once __DIR__.'/../../../sys/preboot.php';
require_once __DIR__.'/../../../sys/autoload.php';

ini_set('max_execution_time', 0);