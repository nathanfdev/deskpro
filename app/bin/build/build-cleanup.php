#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT . '/config.php');

require_once DP_ROOT . '/sys/load_config.php';

// Remove log stuff
$rm_paths = array(
	dp_get_cache_dir().'/dev',
	dp_get_cache_dir().'/prod/classes.map'
);

foreach ($rm_paths as $p) {
	system('rm -rf ' . $p);
}
