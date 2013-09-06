<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('adm', new Route(
	'/',
	array('_controller' => 'AdminInterfaceBundle:Index:interface'),
	array(),
	array()
));

$collection->add('adm_tpl_home', new Route(
	'/load-view/{view_name}',
	array('_controller' => 'AdminInterfaceBundle:Interface:loadView'),
	array('view_name' => '.+'),
	array()
));

return $collection;
