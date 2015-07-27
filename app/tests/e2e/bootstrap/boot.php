<?php
define('DP_ROOT', realpath(__DIR__ . '/../../../'));
define('DP_WEB_ROOT', __DIR__ . '/../../../../');
define('DP_CONFIG_FILE', __DIR__ . '/../../config.test.php');
define('DP_TESTS_RUNNING', true);
define('DP_INTERFACE', 'user');
define('DP_TESTS_START_TIME', time());

require_once __DIR__ . '/../../../sys/preboot.php';
require_once __DIR__ . '/../../../sys/autoload.php';
require_once __DIR__ . '/../../../sys/system.php';

set_time_limit(0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');



if ($exist = getenv('BEHAT_PARAMS')) {
    $exist = @json_decode($exist, true) ?: array();
} else {
    $exist = array();
}

if (!isset($exist['extensions']['Behat\MinkExtension']['base_url'])) {
    if (!defined('DP_E2E_BASE_URL')) {
        echo "config.test.php must defined DP_E2E_BASE_URL or your env must define BEHAT_PARAMS.";
        exit(1);
    }

    if (!isset($exist['extensions'])) $exist['extensions'] = array();
    if (!isset($exist['extensions']['Behat\MinkExtension'])) $exist['extensions']['Behat\MinkExtension'] = array();
    $exist['extensions']['Behat\MinkExtension']['base_url'] = DP_E2E_BASE_URL;

    $exist = json_encode($exist);
    putenv("BEHAT_PARAMS=".$exist);
}

\Orb\Util\Strings::setPhpUtf8Dir(DP_ROOT . '/vendor-src/php-utf8/');