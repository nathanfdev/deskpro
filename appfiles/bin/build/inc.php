<?php
if (!defined('DP_CONFIG_FILE')) {
	define('DP_CONFIG_FILE', realpath(DP_ROOT . '/../config.php'));
	require DP_CONFIG_FILE;
}

require DP_ROOT . '/sys/bootstrap-dev.php';
