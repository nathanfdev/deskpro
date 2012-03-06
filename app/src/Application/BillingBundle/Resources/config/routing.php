<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('billing', new Route(
	'/',
	array('_controller' => 'BillingBundle:Main:index'),
	array(),
	array()
));

return $collection;