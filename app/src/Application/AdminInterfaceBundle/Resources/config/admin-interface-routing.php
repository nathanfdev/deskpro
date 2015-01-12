<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('admin', array(
	'path'        => '/',
	'controller'  => 'AdminInterfaceBundle:Index:interface',
));

$collection->create('admin_tpl_loadmulti', array(
	'path'        => '/load-view/multi',
	'controller'  => 'AdminInterfaceBundle:Interface:multiLoadView',
));

$collection->create('admin_tpl_load', array(
	'path'          => '/load-view/{view_name}',
	'controller'    => 'AdminInterfaceBundle:Interface:loadView',
	'requirements'  => array('view_name' => '.+'),
));

$collection->create('admin_lang_load', array(
	'path'        => '/load-lang.{_format}',
	'controller'  => 'AdminInterfaceBundle:Interface:loadLang',
));

$collection->create('admin_apps_download_package', array(
	'path'        => '/apps/download-package/{name}',
	'controller'  => 'AdminInterfaceBundle:Apps:downloadPackage',
	'methods'     => array('GET'),
));

########################################################################################################################
# Start
########################################################################################################################

$collection->create('admin_start_index', array(
	'path'        => '/start',
	'controller'  => 'AdminInterfaceBundle:Start:index',
));

########################################################################################################################
# Upgrade
########################################################################################################################

$collection->create('admin_upgrade_index', array(
	'path'        => '/upgrade',
	'controller'  => 'AdminInterfaceBundle:Upgrade:index',
));

########################################################################################################################
# JIRA
########################################################################################################################

$collection->create('jira_token', array(
	'path'        => '/jira/request_token',
	'controller'  => 'AdminInterfaceBundle:Jira:token',
));

return $collection;
