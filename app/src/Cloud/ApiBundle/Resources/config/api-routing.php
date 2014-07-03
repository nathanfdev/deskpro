<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('api_dpc_call_ping', array(
	'path'        => '/dpc-call/ping',
	'controller'  => 'CloudApiBundle:CloudCall:ping',
	'methods'     => array('GET'),
));

$collection->create('api_dpc_call_resetpass', array(
	'path'        => '/dpc-call/reset-password/{person_id}',
	'controller'  => 'CloudApiBundle:CloudCall:resetPassword',
	'methods'     => array('GET', 'POST'),
));

########################################################################################################################
# Agents
########################################################################################################################

$collection->rewriteController('ApiBundle:Agents', 'CloudApiBundle:Agents');

########################################################################################################################
# License
########################################################################################################################

$collection->rewriteController('ApiBundle:License', 'CloudApiBundle:License');
$collection->removeRoutes(
	'api_dp_license_save',
	'api_dp_keyfile',
	'api_dp_license_versioninfo',
	'api_dp_license_latestversion',
	'api_dp_license_news'
);

$collection->create('api_dpc_call_resetpass', array(
	'path'        => '/dp_license/cloud/billing-login-token',
	'controller'  => 'CloudApiBundle:License:getBillingLoginToken',
	'methods'     => array('GET'),
));

########################################################################################################################
# Settings
########################################################################################################################

$collection->create('api_cloud_urlsettings', array(
	'path'       => '/settings/cloud/url-settings',
	'controller' => 'CloudApiBundle:Settings:getUrlSettings',
	'methods'    => array('GET')
));

$collection->create('api_cloud_urlsettings_save', array(
	'path'       => '/settings/cloud/url-settings',
	'controller' => 'CloudApiBundle:Settings:saveUrlSettings',
	'methods'    => array('POST')
));

$collection->rewriteController('ApiBundle:Settings', 'CloudApiBundle:Settings');
$collection->removeRoutes(
	'api_all_settings_raw',
	'api_all_settings_raw_save'
);

########################################################################################################################
# Server
########################################################################################################################

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