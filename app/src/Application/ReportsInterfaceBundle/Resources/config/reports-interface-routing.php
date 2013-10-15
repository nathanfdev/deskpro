<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('reports', new Route(
	'/',
	array('_controller' => 'ReportsInterfaceBundle:Index:interface'),
	array()
));

$collection->add('reports_tpl_loadmulti', new Route(
	'/load-view/multi',
	array('_controller' => 'ReportsInterfaceBundle:Interface:multiLoadView'),
	array()
));

$collection->add('reports_tpl_load', new Route(
	'/load-view/{view_name}',
	array('_controller' => 'ReportsInterfaceBundle:Interface:loadView'),
	array('view_name' => '.+')
));

$collection->add('reports_lang_load', new Route(
	'/load-lang.{_format}',
	array('_controller' => 'ReportsInterfaceBundle:Interface:loadLang'),
	array()
));

return $collection;
