<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;
use Application\DeskPRO\Routing\Route;

$collection = new RouteCollection();

$collection->create('billing', array(
	'path'        => '/',
	'controller'  => 'BillingBundle:Main:index',
));

$collection->create('billing_login', array(
	'path'        => '/login',
	'controller'  => 'BillingBundle:Login:index',
));

$collection->create('billing_logout', array(
	'path'        => '/logout/{auth}',
	'controller'  => 'BillingBundle:Login:logout',
));

$collection->create('billing_login_authenticate_local', array(
	'path'        => '/login/authenticate-password',
	'controller'  => 'BillingBundle:Login:authenticateLocal',
	'defaults'    => array('usersource_id' => 0),
));

$collection->create('billing_login_ma_login', array(
	'path'        => '/login/verity-ma-login/{license_id}/{code}',
	'controller'  => 'BillingBundle:Login:verifyMaLoginRequest',
));

$collection->create('billing_license_reqdemo', array(
	'path'        => '/license/generate-demo',
	'controller'  => 'BillingBundle:License:requestDemo',
));

$collection->create('billing_license_input_save', array(
	'path'        => '/license/input/save',
	'controller'  => 'BillingBundle:License:saveNewLicense',
));

$collection->create('billing_license_keyfile', array(
	'path'        => '/license/download/deskpro-license-sign.key',
	'controller'  => 'BillingBundle:License:keyFile',
));

return $collection;
