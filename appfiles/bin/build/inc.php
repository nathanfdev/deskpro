<?php
require DP_ROOT . '/sys/bootstrap-dev.php';

#------------------------------
# Load main config now
# Similar to KernelBooter
#------------------------------

if (!defined('DP_CONFIG_FILE')) {
	define('DP_CONFIG_FILE', realpath(DP_ROOT . '/../config.php'));
}

global $DP_CONFIG;
require DP_CONFIG_FILE;

if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
	$DP_CONFIG = array();
}

if (!isset($DP_CONFIG['db'])) $DP_CONFIG['db'] = array();
if (!isset($DP_CONFIG['db']['host']))      $DP_CONFIG['db']['host']      = DP_DATABASE_HOST;
if (!isset($DP_CONFIG['db']['user']))      $DP_CONFIG['db']['user']      = DP_DATABASE_USER;
if (!isset($DP_CONFIG['db']['password']))  $DP_CONFIG['db']['password']  = DP_DATABASE_PASSWORD;
if (!isset($DP_CONFIG['db']['dbname']))    $DP_CONFIG['db']['dbname']    = DP_DATABASE_NAME;
