<?php

ini_set('display_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

define('DP_DATABASE_HOST', '127.0.0.1');
define('DP_DATABASE_USER', 'deskpro');
define('DP_DATABASE_PASSWORD', 'deskpro');
define('DP_DATABASE_NAME', 'deskpro');
define('DP_TECHNICAL_EMAIL', 'chris.nadeau@deskpro.com');

//TODO get elastic tests working again
//$GLOBALS['DP_TEST_ELASTIC'] = true;

$DP_CONFIG = array('debug' => array(), 'cache' => array());
$DP_CONFIG['php_path'] = '';
$DP_CONFIG['mysqldump_path'] = '';
$DP_CONFIG['mysql_path'] = '';
$DP_CONFIG['dir_data'] = '';
$DP_CONFIG['trust_proxy_data'] = false;
$DP_CONFIG['disable_url_corrections'] = false;
$DP_CONFIG['debug']['enable_debug_trace'] = false;
$DP_CONFIG['debug']['enable_debug_trace_keep'] = false;

$DP_CONFIG['debug']['page_log'] = array(
	'enabled' => false,
	'slow_query_time' => false,
	'max_query_count' => false,
	'slow_db_time' => false,
	'slow_php_time' => false,
	'slow_page_time' => false,
);

$DP_CONFIG['debug']['enable_usersource_log'] = false;

$DP_CONFIG['debug']['mail'] = array();
$DP_CONFIG['debug']['mail']['enable_mail_log'] = true;
$DP_CONFIG['debug']['mail']['save_to_file'] = true;
$DP_CONFIG['debug']['mail']['disable_send'] = true;
$DP_CONFIG['debug']['mail']['force_to'] = '';

$DP_CONFIG['cache']['page_cache'] = array();
$DP_CONFIG['cache']['page_cache']['enable'] = false;
$DP_CONFIG['cache']['page_cache']['ttl'] = 900;
$DP_CONFIG['cache']['page_cache']['max_size'] = 10000000;
$DP_CONFIG['cache']['page_cache']['enable_hit_log'] = false;
$DP_CONFIG['cache']['page_cache']['hit_log_file'] = '';

$DP_CONFIG['debug']['dev']                     = true;
$DP_CONFIG['debug']['raw_assets']              = array('all');
$DP_CONFIG['debug']['no_report_errors']        = true;

$DP_CONFIG['rewrite_urls'] = true;

$DP_CONFIG['SETTINGS'] = array();
$DP_CONFIG['SETTINGS']['core.use_mail_queue']    = 'never';
$DP_CONFIG['SETTINGS']['core.show_share_widget'] = false;
$DP_CONFIG['SETTINGS']['core.use_gravatar']      = false;