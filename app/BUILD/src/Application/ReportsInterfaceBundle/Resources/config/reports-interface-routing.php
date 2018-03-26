<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('reports', [
    'path'       => '/',
    'controller' => 'ReportsInterfaceBundle:Index:interface',
]);

$collection->create('reports_tpl_loadmulti', [
    'path'       => '/load-view/multi',
    'controller' => 'ReportsInterfaceBundle:Interface:multiLoadView',
]);

$collection->create('reports_tpl_load', [
    'path'         => '/load-view/{view_name}',
    'controller'   => 'ReportsInterfaceBundle:Interface:loadView',
    'requirements' => ['view_name' => '.+'],
]);

$collection->create('reports_lang_load', [
    'path'       => '/load-lang.{_format}',
    'controller' => 'ReportsInterfaceBundle:Interface:loadLang',
]);

return $collection;
