<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('jira_token', array(
	'path'        => '/request_token',
	'controller'  => 'DeskPRO:JIRA:token',
));

$collection->create('jira_test', array(
	'path'        => '/call',
	'controller'  => 'DeskPRO:JIRA:test',
));

return $collection;