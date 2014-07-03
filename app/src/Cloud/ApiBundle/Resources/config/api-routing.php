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

$collection->rewriteController('ApiBundle:License', 'CloudApiBundle:License');
$collection->nullRoute(
	'api_dp_license',
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

return $collection;