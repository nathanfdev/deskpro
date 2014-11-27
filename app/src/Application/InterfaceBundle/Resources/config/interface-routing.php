<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('iface', array(
    'path'        => '/',
    'controller'  => 'InterfaceBundle:Interface:interface',
));

$collection->create('iface_load_views', array(
    'path'        => '/load-views',
    'controller'  => 'InterfaceBundle:Interface:loadViews',
));

return $collection;