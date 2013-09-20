<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('adm', new Route(
	'/',
	array('_controller' => 'AdminInterfaceBundle:Index:interface'),
	array()
));

$collection->add('adm_tpl_loadmulti', new Route(
	'/load-view/multi',
	array('_controller' => 'AdminInterfaceBundle:Interface:multiLoadView'),
	array()
));

$collection->add('adm_tpl_load', new Route(
	'/load-view/{view_name}',
	array('_controller' => 'AdminInterfaceBundle:Interface:loadView'),
	array('view_name' => '.+')
));

$collection->add('adm_lang_load', new Route(
	'/load-lang.{_format}',
	array('_controller' => 'AdminInterfaceBundle:Interface:loadLang'),
	array()
));

return $collection;
