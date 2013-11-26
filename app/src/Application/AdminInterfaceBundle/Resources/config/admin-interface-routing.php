<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;
use Application\DeskPRO\Routing\Route;

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

########################################################################################################################
# Portal
########################################################################################################################

$collection->create('admin_portal', array(
	'path'        => '/portal',
	'controller'  => 'AdminInterfaceBundle:Portal:index',
));

$collection->create('admin_portal_updateblockorder', array(
	'path'        => '/portal/update-block-orders.json',
	'controller'  => 'AdminInterfaceBundle:Portal:updateBlockOrders',
));

$collection->create('admin_portal_blocktoggle', array(
	'path'        => '/portal/blocks/{pid}/toggle.json',
	'controller'  => 'AdminInterfaceBundle:Portal:blockToggle',
));

$collection->create('admin_portal_custom_block_delete', array(
	'path'        => '/portal/blocks/{pid}/delete-template-block.json',
	'controller'  => 'AdminInterfaceBundle:Portal:deleteTemplateBlock',
));

$collection->create('admin_portal_custom_sideblock_simple_get', array(
	'path'        => '/portal/sideblock-simple/{pid}.json',
	'controller'  => 'AdminInterfaceBundle:Portal:getCustomBlockSimple',
));

$collection->create('admin_portal_custom_sideblock_simple_save', array(
	'path'        => '/portal/sideblock-simple/{pid}/save.json',
	'controller'  => 'AdminInterfaceBundle:Portal:saveCustomBlockSimple',
	'defaults'    => array('pid' => '0'),
	'methods'     => array('POST'),
));

$collection->create('admin_portal_custom_sideblock_simple_delete', array(
	'path'        => '/portal/sideblock-simple/{pid}/delete.json',
	'controller'  => 'AdminInterfaceBundle:Portal:deleteCustomBlockSimple',
	'methods'     => array('POST'),
));

$collection->create('admin_portal_toggle', array(
	'path'        => '/portal/toggle-portal',
	'controller'  => 'AdminInterfaceBundle:Portal:togglePortal',
));

$collection->create('admin_portal_get_editor', array(
	'path'        => '/portal/get-editor/{type}',
	'controller'  => 'AdminInterfaceBundle:Portal:getEditor',
));

$collection->create('admin_portal_save_editor', array(
	'path'        => '/portal/save-editor/{type}',
	'controller'  => 'AdminInterfaceBundle:Portal:saveEditor',
	'methods'     => array('POST'),
));

$collection->create('admin_portal_twitter_oauth', array(
	'path'        => '/portal/twitter-oauth',
	'controller'  => 'AdminInterfaceBundle:Portal:twitterOauth',
));

return $collection;
