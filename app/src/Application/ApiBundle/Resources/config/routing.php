<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('api_test', new Route(
	'/test',
	array('_controller' => 'ApiBundle:Test:test'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_test_post', new Route(
	'/test',
	array('_controller' => 'ApiBundle:Test:postTest'),
	array('_method' => 'POST'),
	array()
));

$collection->add('api_deskpro_time', new Route(
	'/deskpro/time',
	array('_controller' => 'ApiBundle:Deskpro:time'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_deskpro_setting', new Route(
	'/deskpro/setting/{setting_name}',
	array('_controller' => 'ApiBundle:Deskpro:setting'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_deskpro_setting_post', new Route(
	'/deskpro/setting/{setting_name}',
	array('_controller' => 'ApiBundle:Deskpro:postSetting'),
	array('_method' => 'POST'),
	array()
));

$collection->add('api_ping_object_updated', new Route(
	'/ping/object-updated/{resource_id}/{object_id}',
	array('_controller' => 'ApiBundle:ResourcePing:postObjectUpdated'),
	array('_method' => 'POST'),
	array()
));

$collection->add('api_ticketsearch_filters_getnames', new Route(
	'/ticket-search/get-filter-names',
	array('_controller' => 'ApiBundle:TicketSearch:getFilterNames'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_ticketsearch_filters_getcounts', new Route(
	'/ticket-search/get-filter-counts',
	array('_controller' => 'ApiBundle:TicketSearch:getFilterCounts'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_ticketsearch_filters_getresults', new Route(
	'/ticket-search/filters/{filter_id}/get-results',
	array('_controller' => 'ApiBundle:TicketSearch:getFilterResults'),
	array('_method' => 'GET', 'filter_id' => '\\d+'),
	array()
));


return $collection;
