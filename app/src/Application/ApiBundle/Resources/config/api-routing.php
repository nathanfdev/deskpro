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
# Tickets
################################################################################

$collection->add('api_tickets_new', new Route(
	'/tickets',
	array('_controller' => 'ApiBundle:Ticket:newTicket'),
	array('_method' => 'POST'),
	array()
));

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

$collection->add('api_tickets_ticket_messages', new Route(
	'/tickets/{ticket_id}/messages',
	array('_controller' => 'ApiBundle:Ticket:getTicketMessages'),
	array('_method' => 'GET', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_messages_post', new Route(
	'/tickets/{ticket_id}/messages',
	array('_controller' => 'ApiBundle:Ticket:replyTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_message', new Route(
	'/tickets/{ticket_id}/messages/{message_id}',
	array('_controller' => 'ApiBundle:Ticket:getTicketMessage'),
	array('_method' => 'GET', 'ticket_id' => '\\d+', 'message_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_undelete', new Route(
	'/tickets/{ticket_id}/undelete',
	array('_controller' => 'ApiBundle:Ticket:undeleteTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_claim', new Route(
	'/tickets/{ticket_id}/claim',
	array('_controller' => 'ApiBundle:Ticket:claimTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_merge', new Route(
	'/tickets/{ticket_id}/merge/{merge_ticket_id}',
	array('_controller' => 'ApiBundle:Ticket:mergeTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+', 'merge_ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_spam', new Route(
	'/tickets/{ticket_id}/spam',
	array('_controller' => 'ApiBundle:Ticket:spamTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_unspam', new Route(
	'/tickets/{ticket_id}/unspam',
	array('_controller' => 'ApiBundle:Ticket:unspamTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_lock', new Route(
	'/tickets/{ticket_id}/lock',
	array('_controller' => 'ApiBundle:Ticket:lockTicket'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_unlock', new Route(
	'/tickets/{ticket_id}/unlock',
	array('_controller' => 'ApiBundle:Ticket:unlockTicket'),
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

$collection->add('api_tickets_ticket_participant', new Route(
	'/tickets/{ticket_id}/participants/{person_id}',
	array('_controller' => 'ApiBundle:Ticket:getParticipant'),
	array('_method' => 'GET', 'ticket_id' => '\\d+', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_participant_delete', new Route(
	'/tickets/{ticket_id}/participants/{person_id}',
	array('_controller' => 'ApiBundle:Ticket:deleteParticipant'),
	array('_method' => 'DELETE', 'ticket_id' => '\\d+', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_labels', new Route(
	'/tickets/{ticket_id}/labels',
	array('_controller' => 'ApiBundle:Ticket:getLabels'),
	array('_method' => 'GET', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_labels_post', new Route(
	'/tickets/{ticket_id}/labels',
	array('_controller' => 'ApiBundle:Ticket:postLabels'),
	array('_method' => 'POST', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_label', new Route(
	'/tickets/{ticket_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Ticket:getLabel'),
	array('_method' => 'GET', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_ticket_label_delete', new Route(
	'/tickets/{ticket_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Ticket:deleteLabel'),
	array('_method' => 'DELETE', 'ticket_id' => '\\d+'),
	array()
));

$collection->add('api_tickets_fields', new Route(
	'/tickets/fields',
	array('_controller' => 'ApiBundle:Ticket:getFields'),
	array('_method' => 'GET'),
	array()
));

################################################################################
# Ticket filters
################################################################################

$collection->add('api_tickets', new Route(
	'/tickets',
	array('_controller' => 'ApiBundle:TicketSearch:search'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_tickets_filters', new Route(
	'/tickets/filters',
	array('_controller' => 'ApiBundle:TicketSearch:getFilters'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_tickets_filter', new Route(
	'/tickets/filters/{filter_id}',
	array('_controller' => 'ApiBundle:TicketSearch:getFilter'),
	array('_method' => 'GET', 'filter_id' => '\\d+'),
	array()
));

################################################################################
# People
################################################################################

$collection->add('api_people', new Route(
	'/people',
	array('_controller' => 'ApiBundle:Person:search'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_people_person', new Route(
	'/people/{person_id}',
	array('_controller' => 'ApiBundle:Person:getPerson'),
	array('_method' => 'GET', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_post', new Route(
	'/people/{person_id}',
	array('_controller' => 'ApiBundle:Person:postPerson'),
	array('_method' => 'POST', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_delete', new Route(
	'/people/{person_id}',
	array('_controller' => 'ApiBundle:Person:deletePerson'),
	array('_method' => 'DELETE', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_reset_password', new Route(
	'/people/{person_id}/reset-password',
	array('_controller' => 'ApiBundle:Person:resetPassword'),
	array('_method' => 'POST', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_notes', new Route(
	'/people/{person_id}/notes',
	array('_controller' => 'ApiBundle:Person:getPersonNotes'),
	array('_method' => 'GET', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_notes_post', new Route(
	'/people/{person_id}/notes',
	array('_controller' => 'ApiBundle:Person:postPersonNotes'),
	array('_method' => 'POST', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_notes_note', new Route(
	'/people/{person_id}/notes/{note_id}',
	array('_controller' => 'ApiBundle:Person:getPersonNote'),
	array('_method' => 'GET', 'person_id' => '\\d+', 'note_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_contact_details', new Route(
	'/people/{person_id}/contact-details',
	array('_controller' => 'ApiBundle:Person:getPersonContactDetails'),
	array('_method' => 'GET', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_contact_detail', new Route(
	'/people/{person_id}/contact-details/{contact_id}',
	array('_controller' => 'ApiBundle:Person:getPersonContactDetail'),
	array('_method' => 'GET', 'person_id' => '\\d+', 'contact_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_contact_detail_delete', new Route(
	'/people/{person_id}/contact-details/{contact_id}',
	array('_controller' => 'ApiBundle:Person:deletePersonContactDetail'),
	array('_method' => 'DELETE', 'person_id' => '\\d+', 'contact_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_groups', new Route(
	'/people/{person_id}/groups',
	array('_controller' => 'ApiBundle:Person:getPersonGroups'),
	array('_method' => 'GET', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_groups_post', new Route(
	'/people/{person_id}/groups',
	array('_controller' => 'ApiBundle:Person:postPersonGroups'),
	array('_method' => 'POST', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_group', new Route(
	'/people/{person_id}/groups/{usergroup_id}',
	array('_controller' => 'ApiBundle:Person:getPersonGroup'),
	array('_method' => 'GET', 'person_id' => '\\d+', 'usergroup_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_group_delete', new Route(
	'/people/{person_id}/groups/{usergroup_id}',
	array('_controller' => 'ApiBundle:Person:deletePersonGroup'),
	array('_method' => 'DELETE', 'person_id' => '\\d+', 'usergroup_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_labels', new Route(
	'/people/{person_id}/labels',
	array('_controller' => 'ApiBundle:Person:getPersonLabels'),
	array('_method' => 'GET', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_labels_post', new Route(
	'/people/{person_id}/labels',
	array('_controller' => 'ApiBundle:Person:postPersonLabels'),
	array('_method' => 'POST', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_label', new Route(
	'/people/{person_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Person:getPersonLabel'),
	array('_method' => 'GET', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_person_label_delete', new Route(
	'/people/{person_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Person:deletePersonLabel'),
	array('_method' => 'DELETE', 'person_id' => '\\d+'),
	array()
));

$collection->add('api_people_fields', new Route(
	'/people/fields',
	array('_controller' => 'ApiBundle:Person:getFields'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_people_groups', new Route(
	'/people/groups',
	array('_controller' => 'ApiBundle:Person:getGroups'),
	array('_method' => 'GET'),
	array()
));

################################################################################
# Organizations
################################################################################

$collection->add('api_organizations', new Route(
	'/organizations',
	array('_controller' => 'ApiBundle:Organization:search'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_organizations_organization', new Route(
	'/organizations/{organization_id}',
	array('_controller' => 'ApiBundle:Organization:getOrganization'),
	array('_method' => 'GET', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_post', new Route(
	'/organizations/{organization_id}',
	array('_controller' => 'ApiBundle:Organization:postOrganization'),
	array('_method' => 'POST', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_delete', new Route(
	'/organizations/{organization_id}',
	array('_controller' => 'ApiBundle:Organization:deleteOrganization'),
	array('_method' => 'DELETE', 'organization_id' => '\\d+'),
	array()
));

/*$collection->add('api_organizations_organization_notes', new Route(
	'/organizations/{organization_id}/notes',
	array('_controller' => 'ApiBundle:Organization:getOrganizationNotes'),
	array('_method' => 'GET', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_notes_post', new Route(
	'/organizations/{organization_id}/notes',
	array('_controller' => 'ApiBundle:Organization:postOrganizationNotes'),
	array('_method' => 'POST', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_notes_note', new Route(
	'/organizations/{organization_id}/notes/{note_id}',
	array('_controller' => 'ApiBundle:Organization:getOrganizationNote'),
	array('_method' => 'GET', 'organization_id' => '\\d+', 'note_id' => '\\d+'),
	array()
));*/

$collection->add('api_organizations_organization_contact_details', new Route(
	'/organizations/{organization_id}/contact-details',
	array('_controller' => 'ApiBundle:Organization:getOrganizationContactDetails'),
	array('_method' => 'GET', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_contact_detail', new Route(
	'/organizations/{organization_id}/contact-details/{contact_id}',
	array('_controller' => 'ApiBundle:Organization:getOrganizationContactDetail'),
	array('_method' => 'GET', 'organization_id' => '\\d+', 'contact_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_contact_detail_delete', new Route(
	'/organizations/{organization_id}/contact-details/{contact_id}',
	array('_controller' => 'ApiBundle:Organization:deleteOrganizationContactDetail'),
	array('_method' => 'DELETE', 'organization_id' => '\\d+', 'contact_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_groups', new Route(
	'/organizations/{organization_id}/groups',
	array('_controller' => 'ApiBundle:Organization:getOrganizationGroups'),
	array('_method' => 'GET', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_groups_post', new Route(
	'/organizations/{organization_id}/groups',
	array('_controller' => 'ApiBundle:Organization:postOrganizationGroups'),
	array('_method' => 'POST', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_group', new Route(
	'/organizations/{organization_id}/groups/{usergroup_id}',
	array('_controller' => 'ApiBundle:Organization:getOrganizationGroup'),
	array('_method' => 'GET', 'organization_id' => '\\d+', 'usergroup_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_group_delete', new Route(
	'/organizations/{organization_id}/groups/{usergroup_id}',
	array('_controller' => 'ApiBundle:Organization:deleteOrganizationGroup'),
	array('_method' => 'DELETE', 'organization_id' => '\\d+', 'usergroup_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_labels', new Route(
	'/organizations/{organization_id}/labels',
	array('_controller' => 'ApiBundle:Organization:getOrganizationLabels'),
	array('_method' => 'GET', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_labels_post', new Route(
	'/organizations/{organization_id}/labels',
	array('_controller' => 'ApiBundle:Organization:postOrganizationLabels'),
	array('_method' => 'POST', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_label', new Route(
	'/organizations/{organization_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Organization:getOrganizationLabel'),
	array('_method' => 'GET', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_organization_label_delete', new Route(
	'/organizations/{organization_id}/labels/{label}',
	array('_controller' => 'ApiBundle:Organization:deleteOrganizationLabel'),
	array('_method' => 'DELETE', 'organization_id' => '\\d+'),
	array()
));

$collection->add('api_organizations_fields', new Route(
	'/organizations/fields',
	array('_controller' => 'ApiBundle:Organization:getFields'),
	array('_method' => 'GET'),
	array()
));

$collection->add('api_organizations_groups', new Route(
	'/organizations/groups',
	array('_controller' => 'ApiBundle:Organization:getGroups'),
	array('_method' => 'GET'),
	array()
));

return $collection;
