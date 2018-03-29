<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('email_callback', [
    'path'         => '/callback{trailingSlash}',
    'controller'   => 'EmailBundle:Callback:handle',
    'defaults'     => ['trailingSlash' => '/'],
    'requirements' => ['trailingSlash' => '[/]{0,1}'],
    'methods'      => ['POST'],
]);

return $collection;
