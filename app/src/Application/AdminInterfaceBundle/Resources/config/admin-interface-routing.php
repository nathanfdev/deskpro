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
# Portal
########################################################################################################################

$collection->create('admin_portaleditor_updateblockorder', array(
	'path'        => '/portal-editor/update-block-orders.json',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:updateBlockOrders',
));

$collection->create('admin_portaleditor_blocktoggle', array(
	'path'        => '/portal-editor/blocks/{pid}/toggle.json',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:blockToggle',
));

$collection->create('admin_portaleditor_custom_block_delete', array(
	'path'        => '/portal-editor/blocks/{pid}/delete-template-block.json',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:deleteTemplateBlock',
));

$collection->create('admin_portaleditor_custom_sideblock_save', array(
	'path'        => '/portal-editor/sideblock-simple/{name}',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:saveCustomBlock',
	'methods'     => array('POST'),
));

$collection->create('admin_portaleditor_custom_sideblock_simple_get', array(
	'path'        => '/portal-editor/sideblock-simple/{pid}.json',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:getCustomBlockSimple',
));

$collection->create('admin_portaleditor_custom_sideblock_simple_save', array(
	'path'        => '/portal-editor/sideblock-simple/{pid}/save.json',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:saveCustomBlockSimple',
	'defaults'    => array('pid' => '0'),
	'methods'     => array('POST'),
));

$collection->create('admin_portaleditor_custom_sideblock_simple_delete', array(
	'path'        => '/portal-editor/sideblock-simple/{pid}/delete.json',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:deleteCustomBlockSimple',
	'methods'     => array('POST'),
));

$collection->create('admin_portaleditor_toggle', array(
	'path'        => '/portal-editor/toggle-portal',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:togglePortal',
));

$collection->create('admin_portaleditor_get_editor', array(
	'path'        => '/portal-editor/get-editor/{type}',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:getEditor',
));

$collection->create('admin_portaleditor_save_editor', array(
	'path'        => '/portal-editor/save-editor/{type}',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:saveEditor',
	'methods'     => array('POST'),
));

$collection->create('admin_portaleditor_twitter_oauth', array(
	'path'        => '/portal-editor/twitter-oauth',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:twitterOauth',
));

$collection->create('admin_portaleditor_accept_upload', array(
	'path'        => '/portal-editor/accept-upload',
	'controller'  => 'AdminInterfaceBundle:PortalEditor:acceptTempUpload',
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

return $collection;
