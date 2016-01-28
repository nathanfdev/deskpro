<?php

# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
# PLEASE NOTE:
#
# This is a test configuration file that is used
# in our automated test suite. If you are not a
# developer, you might be looking for "config.php"
# in the project root directory. This config file
# has no effect on the DeskPRO web application.
# ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

#
# Test Config
#
# The tests need this config file to run. This file
# is identical to the "normal" config.php.
#
# To run the tests, you need to do the following:
#
# 1. rename this file to "config.test.php"
# 2. ensure the database config below are correct
# 3. ensure the DP_E2E_BASE_URL is correct (used in e2e tests)
# 3. Remember that the test db will be deleted and
#    reconstructed on every test run. Make sure it
#    is NOT set to your dev database.
#

define('DP_DATABASE_HOST', 'localhost');
define('DP_DATABASE_USER', 'root');
define('DP_DATABASE_PASSWORD', 'deskpro');
define('DP_DATABASE_NAME', 'deskpro_test');
define('DP_TECHNICAL_EMAIL', 'chris.nadeau@deskpro.com');

define('DP_E2E_BASE_URL', 'http://dev.dp.devput.com:8080/');

######################################################
######################################################
######################################################
######################################################
####             DESKPRO DEV OPTIONS              ####
######################################################
######################################################
######################################################
######################################################

$DP_CONFIG = array('debug' => array(), 'cache' => array());

$DP_CONFIG['debug']['dev'] = true;
$DP_CONFIG['debug']['raw_assets'] = array('all');
$DP_CONFIG['debug']['no_report_errors'] = true;
$DP_CONFIG['cache']['page_cache']['enable'] = false;
$DP_CONFIG['debug']['mail']['enable_mail_log'] = true;
$DP_CONFIG['debug']['mail']['disable_send'] = false;

$DP_CONFIG['SETTINGS'] = array();
$DP_CONFIG['SETTINGS']['core.use_mail_queue'] = 'never';
$DP_CONFIG['SETTINGS']['core.show_share_widget'] = false;
$DP_CONFIG['SETTINGS']['core.use_gravatar'] = false;
$DP_CONFIG['SETTINGS']['core.default_timezone'] = 'EST';

// cache settings
$DP_CONFIG['SETTINGS']['portal.http_cache_etags'] = false;
$DP_CONFIG['SETTINGS']['portal.http_cache_last_modified'] = false;
$DP_CONFIG['SETTINGS']['portal.smaxage_guest_page'] = 0;
$DP_CONFIG['SETTINGS']['portal.smaxage_guest_tag'] = 0;
$DP_CONFIG['SETTINGS']['portal.smaxage_user_page'] = 0;
$DP_CONFIG['SETTINGS']['portal.smaxage_user_tag'] = 0;

// anti-abuse settings
$DP_CONFIG['SETTINGS']['user.login_rate_limit.enabled'] = true;
$DP_CONFIG['SETTINGS']['user.login_rate_limit.attempts'] = 4; // set low for testing
$DP_CONFIG['SETTINGS']['user.login_rate_limit.attempts_time'] = 900;
$DP_CONFIG['SETTINGS']['user.login_rate_limit.lock_time'] = 900;
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.enabled'] = true;
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.attempts'] = 4; // set low for testing
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.attempts_time'] = 900;
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.lock_time'] = 900;

$DP_CONFIG['enable_termengine_log'] = true;

$DP_CONFIG['dir_data'] = '';
$DP_CONFIG['php_path'] = '';
$DP_CONFIG['mysqldump_path'] = '';
$DP_CONFIG['mysql_path'] = '';
