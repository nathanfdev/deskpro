<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

ini_set('display_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

define('DP_DATABASE_HOST', '127.0.0.1');
define('DP_DATABASE_USER', 'deskpro');
define('DP_DATABASE_PASSWORD', 'deskpro');
define('DP_DATABASE_NAME', getenv('DATABASE_PREFIX'));
define('DP_TECHNICAL_EMAIL', 'chris.nadeau@deskpro.com');

//TODO get elastic tests working again
//$GLOBALS['DP_TEST_ELASTIC'] = true;

$DP_CONFIG                                     = array('debug' => array(), 'cache' => array());
$DP_CONFIG['php_path']                         = '';
$DP_CONFIG['mysqldump_path']                   = '';
$DP_CONFIG['mysql_path']                       = '';
$DP_CONFIG['dir_data']                         = '';
$DP_CONFIG['trust_proxy_data']                 = false;
$DP_CONFIG['disable_url_corrections']          = false;
$DP_CONFIG['debug']['enable_debug_trace']      = false;
$DP_CONFIG['debug']['enable_debug_trace_keep'] = false;

$DP_CONFIG['debug']['page_log'] = array(
    'enabled'         => false,
    'slow_query_time' => false,
    'max_query_count' => false,
    'slow_db_time'    => false,
    'slow_php_time'   => false,
    'slow_page_time'  => false,
);

$DP_CONFIG['debug']['enable_usersource_log'] = false;

$DP_CONFIG['debug']['mail']                    = array();
$DP_CONFIG['debug']['mail']['enable_mail_log'] = true;
$DP_CONFIG['debug']['mail']['save_to_file']    = true;
$DP_CONFIG['debug']['mail']['disable_send']    = true;
$DP_CONFIG['debug']['mail']['force_to']        = '';

$DP_CONFIG['debug']['dev']              = true;
$DP_CONFIG['debug']['raw_assets']       = array('all');
$DP_CONFIG['debug']['no_report_errors'] = true;

$DP_CONFIG['SETTINGS']                           = array();
$DP_CONFIG['SETTINGS']['core.use_mail_queue']    = 'never';
$DP_CONFIG['SETTINGS']['core.show_share_widget'] = false;
$DP_CONFIG['SETTINGS']['core.use_gravatar']      = false;

// cache settings
$DP_CONFIG['SETTINGS']['portal.http_cache_etags']         = false;
$DP_CONFIG['SETTINGS']['portal.http_cache_last_modified'] = false;
$DP_CONFIG['SETTINGS']['portal.smaxage_guest_page']       = 0;
$DP_CONFIG['SETTINGS']['portal.smaxage_guest_tag']        = 0;
$DP_CONFIG['SETTINGS']['portal.smaxage_user_page']        = 0;
$DP_CONFIG['SETTINGS']['portal.smaxage_user_tag']         = 0;

// anti-abuse settings
$DP_CONFIG['SETTINGS']['user.login_rate_limit.enabled']        = true;
$DP_CONFIG['SETTINGS']['user.login_rate_limit.attempts']       = 4; // set low for testing
$DP_CONFIG['SETTINGS']['user.login_rate_limit.attempts_time']  = 900;
$DP_CONFIG['SETTINGS']['user.login_rate_limit.lock_time']      = 900;
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.enabled']       = true;
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.attempts']      = 4; // set low for testing
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.attempts_time'] = 900;
$DP_CONFIG['SETTINGS']['agent.login_rate_limit.lock_time']     = 900;
