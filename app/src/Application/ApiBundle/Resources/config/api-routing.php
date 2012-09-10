<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

################################################################################
# Misc
################################################################################

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

################################################################################
# Ticket search
################################################################################

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

################################################################################
# Tickets
################################################################################

$collection->add('api_tickets_ticket', new Route(
	'/tickets/{ticket_id}',
	array('_controller' => 'ApiBundle:Ticket:getTicket'),
	array('_method' => 'GET', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_post', new Route(
	'/tickets/{ticket_id}',
	array('_controller' => 'ApiBundle:Ticket:postTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_delete', new Route(
	'/tickets/{ticket_id}',
	array('_controller' => 'ApiBundle:Ticket:deleteTicket'),
	array('_method' => 'DELETE', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_spam', new Route(
	'/tickets/{ticket_id}/reply',
	array('_controller' => 'ApiBundle:Ticket:replyTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_spam', new Route(
	'/tickets/{ticket_id}/spam',
	array('_controller' => 'ApiBundle:Ticket:spamTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_lock', new Route(
	'/tickets/{ticket_id}/lock',
	array('_controller' => 'ApiBundle:Ticket:postLockTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_unlock', new Route(
	'/tickets/{ticket_id}/unlock',
	array('_controller' => 'ApiBundle:Ticket:postUnlockTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_participants', new Route(
	'/tickets/{ticket_id}/participants',
	array('_controller' => 'ApiBundle:Ticket:getParticipants'),
	array('_method' => 'GET', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_participants_post', new Route(
	'/tickets/{ticket_id}/participants',
	array('_controller' => 'ApiBundle:Ticket:postParticipants'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_participant_delete', new Route(
	'/tickets/{ticket_id}/participants/{person_id}',
	array('_controller' => 'ApiBundle:Ticket:deleteParticipant'),
	array('_method' => 'DELETE', 'ticket_id' => '\\d+', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_labels_post', new Route(
	'/tickets/{ticket_id}/labels',
	array('_controller' => 'ApiBundle:Ticket:postLabels'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_label_delete', new Route(
	'/tickets/{ticket_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Ticket:deleteLabel'),
	array('_method' => 'DELETE', 'ticket_id' => '\\d+'),
	array()
));


return $collection;
