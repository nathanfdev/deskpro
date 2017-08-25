<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('api_dpc_call_ping', [
    'path'       => '/dpc-call/ping',
    'controller' => 'CloudLegacyApiBundle:CloudCall:ping',
    'methods'    => ['GET'],
]);

$collection->create('api_dpc_call_resetpass', [
    'path'       => '/dpc-call/reset-password/{person_id}',
    'controller' => 'CloudLegacyApiBundle:CloudCall:resetPassword',
    'methods'    => ['GET', 'POST'],
]);

//#######################################################################################################################
// Agents
//#######################################################################################################################

$collection->rewriteController('LegacyApiBundle:Agents', 'CloudLegacyApiBundle:Agents');

//#######################################################################################################################
// License
//#######################################################################################################################

$collection->rewriteController('LegacyApiBundle:License', 'CloudLegacyApiBundle:License');
$collection->removeRoutes(
    'api_dp_license_save',
    'api_dp_keyfile',
    'api_dp_license_versioninfo',
    'api_dp_license_latestversion',
    'api_dp_license_news'
);

$collection->create('api_dpc_call_resetpass', [
    'path'       => '/dp_license/cloud/billing-login-token',
    'controller' => 'CloudLegacyApiBundle:License:getBillingLoginToken',
    'methods'    => ['GET'],
]);

//#######################################################################################################################
// Settings
//#######################################################################################################################

$collection->create('api_cloud_urlsettings', [
    'path'       => '/settings/cloud/url-settings',
    'controller' => 'CloudLegacyApiBundle:Settings:getUrlSettings',
    'methods'    => ['GET'],
]);

$collection->create('api_cloud_urlsettings_save', [
    'path'       => '/settings/cloud/url-settings',
    'controller' => 'CloudLegacyApiBundle:Settings:saveUrlSettings',
    'methods'    => ['POST'],
]);

$collection->rewriteController('LegacyApiBundle:Settings', 'CloudLegacyApiBundle:Settings');
$collection->removeRoutes(
    'api_all_settings_raw',
    'api_all_settings_raw_save'
);

//#######################################################################################################################
// Email Accounts
//#######################################################################################################################

$collection->rewriteController('LegacyApiBundle:EmailAccounts', 'CloudLegacyApiBundle:EmailAccounts');

//#######################################################################################################################
// Server
//#######################################################################################################################

$collection->removeRoutes(
    'api_server_settings',
    'api_server_settings_save',
    'api_server_reqs',
    'api_server_php_info',
    'api_server_mysql_info',
    'api_server_mysql_info_schemadiff',
    'api_server_mysql_status',
    'api_server_mysql_sort_order',
    'api_server_mysql_sort_order_save',
    'api_server_mysql_sort_order_status',
    'api_server_cron_status',
    'api_server_error_status',
    'api_server_delete_error_log',
    'api_server_apc_status',
    'api_server_autoupdate_begin',
    'api_server_autoupdate_abort',
    'api_server_autoupdate_status',
    'api_server_error_logs',
    'api_server_error_logs_get',
    'api_server_error_logs_delete',
    'api_server_cron',
    'api_server_cron_logs',
    'api_server_cron_logs_delete',
    'api_server_file_uploads',
    'api_server_test_file_uploads',
    'api_server_switch_file_uploads_storage',
    'api_server_switch_file_uploads_storage_status',
    'api_server_file_check',
    'api_server_file_check_get',
    'api_server_report_file_get',
    'api_server_report_file_check_save'
);

return $collection;
