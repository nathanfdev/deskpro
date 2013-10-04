<?php

ini_set('display_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

define('DP_DATABASE_HOST', 'localhost');
define('DP_DATABASE_USER', 'deskpro');
define('DP_DATABASE_PASSWORD', 'deskpro');
define('DP_DATABASE_NAME', 'deskpro');
define('DP_TECHNICAL_EMAIL', 'chris.nadeau@deskpro.com');

$DP_CONFIG['rewrite_urls'] = true;
$DP_CONFIG['debug'] = array();
$DP_CONFIG['debug']['dev']                     = true;
$DP_CONFIG['debug']['raw_assets']              = array('all');
$DP_CONFIG['debug']['no_report_errors']        = true;

$DP_CONFIG['debug']['mail'] = array();
$DP_CONFIG['debug']['mail']['enable_mail_log'] = true;
$DP_CONFIG['debug']['mail']['save_to_file']    = true;
$DP_CONFIG['debug']['mail']['disable_send']    = true;
$DP_CONFIG['debug']['mail']['force_to'] = '';

$DP_CONFIG['SETTINGS'] = array();
$DP_CONFIG['SETTINGS']['core.use_mail_queue']    = 'never';
$DP_CONFIG['SETTINGS']['core.show_share_widget'] = false;
$DP_CONFIG['SETTINGS']['core.use_gravatar']      = false;