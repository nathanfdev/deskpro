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
# License
########################################################################################################################

$collection->create('api_dp_license', array(
	'path'        => '/dp_license',
	'controller'  => 'ApiBundle:License:getLicense',
	'methods'     => array('GET'),
));

$collection->create('api_dp_license_save', array(
	'path'        => '/dp_license',
	'controller'  => 'ApiBundle:License:setLicense',
	'methods'     => array('POST'),
));

$collection->create('api_dp_keyfile', array(
	'path'         => '/dp_license/keyfile.{_format}',
	'controller'   => 'ApiBundle:License:downloadKeyfile',
	'methods'      => array('GET'),
	'requirements' => array('_format' => 'txt|json'),
));

$collection->create('api_dp_license_supportrequest', array(
	'path'         => '/dp_license/support-request',
	'controller'   => 'ApiBundle:License:sendSupportRequest',
	'methods'      => array('POST'),
));

$collection->create('api_dp_license_versioninfo', array(
	'path'         => '/dp_license/version-info',
	'controller'   => 'ApiBundle:License:getVersionInfo',
	'methods'      => array('GET'),
));

$collection->create('api_dp_license_latestversion', array(
	'path'         => '/dp_license/latest-version-info',
	'controller'   => 'ApiBundle:License:getLatestVersion',
	'methods'      => array('GET'),
));

$collection->create('api_dp_license_news', array(
	'path'         => '/dp_license/news',
	'controller'   => 'ApiBundle:License:getNews',
	'methods'      => array('GET'),
));

return $collection;