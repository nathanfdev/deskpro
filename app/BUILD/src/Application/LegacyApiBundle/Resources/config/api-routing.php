<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('api', array(
    'path'       => '/',
    'controller' => 'LegacyApiBundle:Docs:about',
    'methods'    => array('GET'),
));

$collection->create('api_docs_home', array(
    'path'       => '/api.html',
    'controller' => 'LegacyApiBundle:Docs:api',
    'methods'    => array('GET'),
));

$collection->create('api_getagentsforkey', array(
    'path'       => '/get-agents-for-key.json',
    'controller' => 'LegacyApiBundle:Docs:getAgentsForKey',
    'methods'    => array('GET'),
));

$collection->create('api_discover', array(
    'path'       => '/discover',
    'controller' => 'LegacyApiBundle:Test:discover',
    'methods'    => array('GET'),
));

$collection->create('api_test', array(
    'path'       => '/test',
    'controller' => 'LegacyApiBundle:Test:test',
    'methods'    => array('GET'),
));

$collection->create('api_test_post', array(
    'path'       => '/test',
    'controller' => 'LegacyApiBundle:Test:postTest',
    'methods'    => array('POST'),
));

$collection->create('api_deskpro_time', array(
    'path'       => '/deskpro/time',
    'controller' => 'LegacyApiBundle:Deskpro:time',
    'methods'    => array('GET'),
));

$collection->create('api_deskpro_info', array(
    'path'       => '/deskpro/info',
    'controller' => 'LegacyApiBundle:Misc:helpdeskInfo',
    'methods'    => array('GET'),
));

$collection->create('api_deskpro_dpspecial', array(
    'path'       => '/deskpro/dp_special/{action}',
    'controller' => 'LegacyApiBundle:Misc:dpSpecial',
    'methods'    => array('GET', 'POST'),
));

$collection->create('api_me_lastlogin', array(
    'path'       => '/me/last-login',
    'controller' => 'LegacyApiBundle:Misc:getLastLogin',
    'methods'    => array('GET'),
));

$collection->create('api_token_exchange', array(
    'path'       => '/token-exchange',
    'controller' => 'LegacyApiBundle:Misc:tokenExchange',
    'methods'    => array('POST'),
));

$collection->create('api_token_renew', array(
    'path'       => '/renew-token',
    'controller' => 'LegacyApiBundle:Misc:renewToken',
    'methods'    => array('POST'),
));

$collection->create('api_profile_inhelpstate', array(
    'path'       => '/profile/inhelp/{id}/{state}',
    'controller' => 'LegacyApiBundle:Profile:saveInhelpState',
    'methods'    => array('POST'),
));

$collection->create('api_docs', array(
    'path'       => '/docs',
    'controller' => 'LegacyApiBundle:Docs:list',
    'methods'    => array('GET'),
));

$collection->create('api_docs_get', array(
    'path'       => '/docs/{id}',
    'controller' => 'LegacyApiBundle:Docs:get',
    'methods'    => array('GET'),
));

########################################################################################################################
# General
########################################################################################################################

$collection->create('api_labels_definitions', array(
    'path'       => '/labels/definitions/{type}',
    'controller' => 'LegacyApiBundle:Labels:listDefinitions',
    'methods'    => array('GET'),
    'defaults'   => array('type' => null),
));

$collection->create('api_labels_definitions_create', array(
    'path'       => '/labels/definitions',
    'controller' => 'LegacyApiBundle:Labels:createDefinition',
    'methods'    => array('POST'),
));

$collection->create('api_labels_definitions_update', array(
    'path'       => '/labels/definitions',
    'controller' => 'LegacyApiBundle:Labels:updateDefinition',
    'methods'    => array('PUT'),
));

$collection->create('api_labels_definitions_delete', array(
    'path'       => '/labels/definitions',
    'controller' => 'LegacyApiBundle:Labels:deleteDefinition',
    'methods'    => array('DELETE'),
));

$collection->create('api_misc_upload', array(
    'path'       => '/misc/upload',
    'controller' => 'LegacyApiBundle:Misc:upload',
    'methods'    => array('POST'),
));

$collection->create('api_misc_session_person', array(
    'path'       => '/misc/session-person/{session_code}',
    'controller' => 'LegacyApiBundle:Misc:getSessionPerson',
    'methods'    => array('GET'),
));

$collection->create('api_misc_rate_limit', array(
    'path'       => '/misc/rate-limit',
    'controller' => 'LegacyApiBundle:Misc:getRateLimit',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_new', array(
    'path'       => '/tickets',
    'controller' => 'LegacyApiBundle:Ticket:newTicket',
    'methods'    => array('POST'),
));

$collection->create('api_tickets_ticket', array(
    'path'         => '/tickets/{ticket_id}',
    'controller'   => 'LegacyApiBundle:Ticket:getTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_post', array(
    'path'         => '/tickets/{ticket_id}',
    'controller'   => 'LegacyApiBundle:Ticket:postTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_delete', array(
    'path'         => '/tickets/{ticket_id}',
    'controller'   => 'LegacyApiBundle:Ticket:deleteTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tickets_ticket_logs', array(
    'path'         => '/tickets/{ticket_id}/logs',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketLogs',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_messages', array(
    'path'         => '/tickets/{ticket_id}/messages',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketMessages',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_messages_post', array(
    'path'         => '/tickets/{ticket_id}/messages',
    'controller'   => 'LegacyApiBundle:Ticket:replyTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_message', array(
    'path'         => '/tickets/{ticket_id}/messages/{message_id}',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketMessage',
    'requirements' => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_message_details', array(
    'path'         => '/tickets/{ticket_id}/messages/{message_id}/details',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketMessageDetails',
    'requirements' => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_undelete', array(
    'path'         => '/tickets/{ticket_id}/undelete',
    'controller'   => 'LegacyApiBundle:Ticket:undeleteTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_split', array(
    'path'         => '/tickets/{ticket_id}/split',
    'controller'   => 'LegacyApiBundle:Ticket:splitTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_claim', array(
    'path'         => '/tickets/{ticket_id}/claim',
    'controller'   => 'LegacyApiBundle:Ticket:claimTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_merge', array(
    'path'         => '/tickets/{ticket_id}/merge/{merge_ticket_id}',
    'controller'   => 'LegacyApiBundle:Ticket:mergeTicket',
    'requirements' => array('ticket_id' => '\\d+', 'merge_ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_link', array(
    'path'         => '/tickets/{ticket_id}/link/{link_ticket_id}',
    'controller'   => 'LegacyApiBundle:Ticket:linkTicket',
    'requirements' => array('ticket_id' => '\\d+', 'link_ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_spam', array(
    'path'         => '/tickets/{ticket_id}/spam',
    'controller'   => 'LegacyApiBundle:Ticket:spamTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_unspam', array(
    'path'         => '/tickets/{ticket_id}/unspam',
    'controller'   => 'LegacyApiBundle:Ticket:unspamTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_lock', array(
    'path'         => '/tickets/{ticket_id}/lock',
    'controller'   => 'LegacyApiBundle:Ticket:lockTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_unlock', array(
    'path'         => '/tickets/{ticket_id}/unlock',
    'controller'   => 'LegacyApiBundle:Ticket:unlockTicket',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_tasks', array(
    'path'         => '/tickets/{ticket_id}/tasks',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketTasks',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_tasks_post', array(
    'path'         => '/tickets/{ticket_id}/tasks',
    'controller'   => 'LegacyApiBundle:Ticket:postTicketTasks',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_billing_charges', array(
    'path'         => '/tickets/{ticket_id}/billing-charges',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketBillingCharges',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_billing_charges_post', array(
    'path'         => '/tickets/{ticket_id}/billing-charges',
    'controller'   => 'LegacyApiBundle:Ticket:postTicketBillingCharges',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_billing_charge', array(
    'path'         => '/tickets/{ticket_id}/billing-charges/{charge_id}',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketBillingCharge',
    'requirements' => array('ticket_id' => '\\d+', 'charge_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_billing_charge_delete', array(
    'path'         => '/tickets/{ticket_id}/billing-charges/{charge_id}',
    'controller'   => 'LegacyApiBundle:Ticket:deleteTicketBillingCharge',
    'requirements' => array('ticket_id' => '\\d+', 'charge_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tickets_ticket_slas', array(
    'path'         => '/tickets/{ticket_id}/slas',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketSlas',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_slas_post', array(
    'path'         => '/tickets/{ticket_id}/slas',
    'controller'   => 'LegacyApiBundle:Ticket:postTicketSlas',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_sla', array(
    'path'         => '/tickets/{ticket_id}/slas/{ticket_sla_id}',
    'controller'   => 'LegacyApiBundle:Ticket:getTicketSla',
    'requirements' => array('ticket_id' => '\\d+', 'ticket_sla_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_sla_delete', array(
    'path'         => '/tickets/{ticket_id}/slas/{ticket_sla_id}',
    'controller'   => 'LegacyApiBundle:Ticket:deleteTicketSla',
    'requirements' => array('ticket_id' => '\\d+', 'ticket_sla_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tickets_ticket_participants', array(
    'path'         => '/tickets/{ticket_id}/participants',
    'controller'   => 'LegacyApiBundle:Ticket:getParticipants',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_participants_post', array(
    'path'         => '/tickets/{ticket_id}/participants',
    'controller'   => 'LegacyApiBundle:Ticket:postParticipants',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_participant', array(
    'path'         => '/tickets/{ticket_id}/participants/{person_id}',
    'controller'   => 'LegacyApiBundle:Ticket:getParticipant',
    'requirements' => array('ticket_id' => '\\d+', 'person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_participant_delete', array(
    'path'         => '/tickets/{ticket_id}/participants/{person_id}',
    'controller'   => 'LegacyApiBundle:Ticket:deleteParticipant',
    'requirements' => array('ticket_id' => '\\d+', 'person_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tickets_ticket_labels', array(
    'path'         => '/tickets/{ticket_id}/labels',
    'controller'   => 'LegacyApiBundle:Ticket:getLabels',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_labels_post', array(
    'path'         => '/tickets/{ticket_id}/labels',
    'controller'   => 'LegacyApiBundle:Ticket:postLabels',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets_ticket_label', array(
    'path'         => '/tickets/{ticket_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Ticket:getLabel',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tickets_ticket_label_delete', array(
    'path'         => '/tickets/{ticket_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Ticket:deleteLabel',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tickets_update_dates', array(
    'path'         => '/tickets/{ticket_id}/update_dates',
    'controller'   => 'LegacyApiBundle:Ticket:updateTicketDates',
    'requirements' => array('ticket_id' => '\\d+'),
    'methods'      => array('PUT'),
));

$collection->create('api_tickets_fields', array(
    'path'       => '/tickets/fields',
    'controller' => 'LegacyApiBundle:Ticket:getFields',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_departments', array(
    'path'       => '/tickets/departments',
    'controller' => 'LegacyApiBundle:Ticket:getDepartments',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_products', array(
    'path'       => '/tickets/products',
    'controller' => 'LegacyApiBundle:Ticket:getProducts',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_categories', array(
    'path'       => '/tickets/categories',
    'controller' => 'LegacyApiBundle:Ticket:getCategories',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_priorities', array(
    'path'       => '/tickets/priorities',
    'controller' => 'LegacyApiBundle:Ticket:getPriorities',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_workflows', array(
    'path'       => '/tickets/workflows',
    'controller' => 'LegacyApiBundle:Ticket:getWorkflows',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_slas', array(
    'path'       => '/tickets/slas',
    'controller' => 'LegacyApiBundle:Ticket:getSlas',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_sla', array(
    'path'         => '/tickets/slas/{sla_id}',
    'controller'   => 'LegacyApiBundle:Ticket:getSla',
    'requirements' => array('sla_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_textsnippets_list', array(
    'path'       => '/text-snippets/{typename}',
    'controller' => 'LegacyApiBundle:TextSnippets:filterSnippets',
    'methods'    => array('GET'),
));

$collection->create('api_textsnippets_new', array(
    'path'       => '/text-snippets/{typename}',
    'controller' => 'LegacyApiBundle:TextSnippets:saveSnippet',
    'defaults'   => array('id' => '0'),
    'methods'    => array('POST'),
));

$collection->create('api_textsnippets_edit', array(
    'path'         => '/text-snippets/{typename}/{id}',
    'controller'   => 'LegacyApiBundle:TextSnippets:saveSnippet',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_textsnippets_del', array(
    'path'         => '/text-snippets/{typename}/{id}',
    'controller'   => 'LegacyApiBundle:TextSnippets:deleteSnippet',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_textsnippets_get', array(
    'path'         => '/text-snippets/{typename}/{id}',
    'controller'   => 'LegacyApiBundle:TextSnippets:getSnippet',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_textsnippets_cats_list', array(
    'path'       => '/text-snippets/{typename}/categories',
    'controller' => 'LegacyApiBundle:TextSnippets:listCategories',
    'methods'    => array('GET'),
));

$collection->create('api_textsnippets_cats_new', array(
    'path'       => '/text-snippets/{typename}/categories',
    'controller' => 'LegacyApiBundle:TextSnippets:saveCategory',
    'defaults'   => array('id' => '0'),
    'methods'    => array('POST'),
));

$collection->create('api_textsnippets_cats_edit', array(
    'path'         => '/text-snippets/{typename}/categories/{id}',
    'controller'   => 'LegacyApiBundle:TextSnippets:saveCategory',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_textsnippets_cats_get', array(
    'path'         => '/text-snippets/{typename}/categories/{id}',
    'controller'   => 'LegacyApiBundle:TextSnippets:getCategory',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_textsnippets_cats_del', array(
    'path'         => '/text-snippets/{typename}/categories/{id}',
    'controller'   => 'LegacyApiBundle:TextSnippets:deleteCategory',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_open_tickets_newticketmessage', array(
    'path'         => '/open/tickets/new-ticket-message',
    'controller'   => 'LegacyApiBundle:OpenTicket:newTicketMessage',
    'requirements' => array('sla_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tickets', array(
    'path'       => '/tickets',
    'controller' => 'LegacyApiBundle:TicketSearch:search',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_quickstats', array(
    'path'       => '/tickets/quick-stats',
    'controller' => 'LegacyApiBundle:TicketSearch:getQuickStats',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_filters', array(
    'path'       => '/tickets/filters',
    'controller' => 'LegacyApiBundle:TicketSearch:getFilters',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_filter_counts', array(
    'path'       => '/tickets/filters/counts',
    'controller' => 'LegacyApiBundle:TicketSearch:getFilterCounts',
    'methods'    => array('GET'),
));

$collection->create('api_tickets_filter', array(
    'path'         => '/tickets/filters/{filter_id}',
    'controller'   => 'LegacyApiBundle:TicketSearch:getFilter',
    'requirements' => array('filter_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people', array(
    'path'       => '/people',
    'controller' => 'LegacyApiBundle:Person:search',
    'methods'    => array('GET'),
));

$collection->create('api_people_quick_search', array(
    'path'       => '/people/quick_search',
    'controller' => 'LegacyApiBundle:Person:quickSearch',
    'methods'    => array('GET'),
));

$collection->create('api_people_quick_search_email', array(
    'path'       => '/people/quick_search_email',
    'controller' => 'LegacyApiBundle:Person:quickSearchEmail',
    'methods'    => array('GET'),
));

$collection->create('api_people_post', array(
    'path'       => '/people',
    'controller' => 'LegacyApiBundle:Person:newPerson',
    'methods'    => array('POST'),
));

$collection->create('api_people_person', array(
    'path'         => '/people/{person_id}',
    'controller'   => 'LegacyApiBundle:Person:getPerson',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_post', array(
    'path'         => '/people/{person_id}',
    'controller'   => 'LegacyApiBundle:Person:postPerson',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_delete', array(
    'path'         => '/people/{person_id}',
    'controller'   => 'LegacyApiBundle:Person:deletePerson',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_person_merge', array(
    'path'         => '/people/{person_id}/merge/{other_person_id}',
    'controller'   => 'LegacyApiBundle:Person:mergePerson',
    'requirements' => array('person_id' => '\\d+', 'other_person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_logintoken', array(
    'path'         => '/people/{person_id}/login-token',
    'controller'   => 'LegacyApiBundle:Person:getLoginToken',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_picture', array(
    'path'         => '/people/{person_id}/picture',
    'controller'   => 'LegacyApiBundle:Person:getPersonPicture',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_picture_post', array(
    'path'         => '/people/{person_id}/picture',
    'controller'   => 'LegacyApiBundle:Person:postPersonPicture',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_picture_delete', array(
    'path'         => '/people/{person_id}/picture',
    'controller'   => 'LegacyApiBundle:Person:deletePersonPicture',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_person_emails', array(
    'path'         => '/people/{person_id}/emails',
    'controller'   => 'LegacyApiBundle:Person:getPersonEmails',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_emails_post', array(
    'path'         => '/people/{person_id}/emails',
    'controller'   => 'LegacyApiBundle:Person:postPersonEmails',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_email', array(
    'path'         => '/people/{person_id}/emails/{email_id}',
    'controller'   => 'LegacyApiBundle:Person:getPersonEmail',
    'requirements' => array('person_id' => '\\d+', 'email_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_email_post', array(
    'path'         => '/people/{person_id}/emails/{email_id}',
    'controller'   => 'LegacyApiBundle:Person:postPersonEmail',
    'requirements' => array('person_id' => '\\d+', 'email_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_email_delete', array(
    'path'         => '/people/{person_id}/emails/{email_id}',
    'controller'   => 'LegacyApiBundle:Person:deletePersonEmail',
    'requirements' => array('person_id' => '\\d+', 'email_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_person_phone_numbers', array(
    'path'         => '/people/{person_id}/phone_numbers',
    'controller'   => 'LegacyApiBundle:Person:getPersonPhoneNumbers',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_phone_numbers_post', array(
    'path'         => '/people/{person_id}/phone_numbers',
    'controller'   => 'LegacyApiBundle:Person:postPersonPhoneNumbers',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_phone_numbers_get', array(
    'path'         => '/people/{person_id}/phone_numbers/{number_id}',
    'controller'   => 'LegacyApiBundle:Person:getPersonPhoneNumber',
    'requirements' => array('person_id' => '\\d+', 'number_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_phone_numbers_update', array(
    'path'         => '/people/{person_id}/phone_numbers/{number_id}',
    'controller'   => 'LegacyApiBundle:Person:postPersonPhoneNumber',
    'requirements' => array('person_id' => '\\d+', 'number_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_phone_numbers_delete', array(
    'path'         => '/people/{person_id}/phone_numbers/{number_id}',
    'controller'   => 'LegacyApiBundle:Person:deletePersonPhoneNumber',
    'requirements' => array('person_id' => '\\d+', 'number_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_person_vcard', array(
    'path'         => '/people/{person_id}/vcard',
    'controller'   => 'LegacyApiBundle:Person:getPersonVcard',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_tickets', array(
    'path'         => '/people/{person_id}/tickets',
    'controller'   => 'LegacyApiBundle:Person:getPersonTickets',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_chats', array(
    'path'         => '/people/{person_id}/chats',
    'controller'   => 'LegacyApiBundle:Person:getPersonChats',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_activity_stream', array(
    'path'         => '/people/{person_id}/activity-stream',
    'controller'   => 'LegacyApiBundle:Person:getPersonActivityStream',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_reset_password', array(
    'path'         => '/people/{person_id}/reset-password',
    'controller'   => 'LegacyApiBundle:Person:resetPassword',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_clear_session', array(
    'path'         => '/people/{person_id}/clear-session',
    'controller'   => 'LegacyApiBundle:Person:clearSession',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_notes', array(
    'path'         => '/people/{person_id}/notes',
    'controller'   => 'LegacyApiBundle:Person:getPersonNotes',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_notes_post', array(
    'path'         => '/people/{person_id}/notes',
    'controller'   => 'LegacyApiBundle:Person:postPersonNotes',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_billing_charges', array(
    'path'         => '/people/{person_id}/billing-charges',
    'controller'   => 'LegacyApiBundle:Person:getPersonBillingCharges',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_contact_details', array(
    'path'         => '/people/{person_id}/contact-details',
    'controller'   => 'LegacyApiBundle:Person:getPersonContactDetails',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_contact_details_post', array(
    'path'         => '/people/{person_id}/contact-details',
    'controller'   => 'LegacyApiBundle:Person:postPersonContactDetails',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_contact_detail', array(
    'path'         => '/people/{person_id}/contact-details/{contact_id}',
    'controller'   => 'LegacyApiBundle:Person:getPersonContactDetail',
    'requirements' => array('person_id' => '\\d+', 'contact_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_contact_detail_delete', array(
    'path'         => '/people/{person_id}/contact-details/{contact_id}',
    'controller'   => 'LegacyApiBundle:Person:deletePersonContactDetail',
    'requirements' => array('person_id' => '\\d+', 'contact_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_person_groups', array(
    'path'         => '/people/{person_id}/groups',
    'controller'   => 'LegacyApiBundle:Person:getPersonGroups',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_groups_post', array(
    'path'         => '/people/{person_id}/groups',
    'controller'   => 'LegacyApiBundle:Person:postPersonGroups',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_group', array(
    'path'         => '/people/{person_id}/groups/{usergroup_id}',
    'controller'   => 'LegacyApiBundle:Person:getPersonGroup',
    'requirements' => array('person_id' => '\\d+', 'usergroup_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_group_delete', array(
    'path'         => '/people/{person_id}/groups/{usergroup_id}',
    'controller'   => 'LegacyApiBundle:Person:deletePersonGroup',
    'requirements' => array('person_id' => '\\d+', 'usergroup_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_person_labels', array(
    'path'         => '/people/{person_id}/labels',
    'controller'   => 'LegacyApiBundle:Person:getPersonLabels',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_labels_post', array(
    'path'         => '/people/{person_id}/labels',
    'controller'   => 'LegacyApiBundle:Person:postPersonLabels',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_people_person_label', array(
    'path'         => '/people/{person_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Person:getPersonLabel',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_people_person_label_delete', array(
    'path'         => '/people/{person_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Person:deletePersonLabel',
    'requirements' => array('person_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_people_fields', array(
    'path'       => '/people/fields',
    'controller' => 'LegacyApiBundle:Person:getFields',
    'methods'    => array('GET'),
));

$collection->create('api_people_groups', array(
    'path'       => '/people/groups',
    'controller' => 'LegacyApiBundle:Person:getGroups',
    'methods'    => array('GET'),
));

$collection->create('api_people_authlogin', array(
    'path'       => '/people/auth-login',
    'controller' => 'LegacyApiBundle:Person:authLogin',
    'methods'    => array('POST'),
));

$collection->create('api_agents_list', array(
    'path'       => '/agents',
    'controller' => 'LegacyApiBundle:Agents:listAgents',
    'methods'    => array('GET'),
));

$collection->create('api_agents_list_deleted', array(
    'path'       => '/agents/deleted',
    'controller' => 'LegacyApiBundle:Agents:listDeletedAgents',
    'methods'    => array('GET'),
));

$collection->create('api_agents_get_deleted', array(
    'path'       => '/agents/deleted/{id}',
    'controller' => 'LegacyApiBundle:Agents:getDeletedAgent',
    'methods'    => array('GET'),
));

$collection->create('api_agents_undelete', array(
    'path'       => '/agents/deleted/{id}/undelete',
    'controller' => 'LegacyApiBundle:Agents:undeleteAgent',
    'methods'    => array('POST'),
));

$collection->create('api_agents_get', array(
    'path'       => '/agents/{id}',
    'controller' => 'LegacyApiBundle:Agents:getAgent',
    'methods'    => array('GET'),
));

$collection->create('api_agents_delete', array(
    'path'       => '/agents/{id}/delete',
    'controller' => 'LegacyApiBundle:Agents:deleteAgent',
    'defaults'   => array('mode' => 'delete'),
    'methods'    => array('DELETE'),
));

$collection->create('api_agents_deletetouse', array(
    'path'       => '/agents/{id}/delete/to-user',
    'controller' => 'LegacyApiBundle:Agents:deleteAgent',
    'defaults'   => array('mode' => 'user'),
    'methods'    => array('DELETE'),
));

$collection->create('api_agents_save', array(
    'path'       => '/agents/{id}',
    'controller' => 'LegacyApiBundle:Agents:saveAgent',
    'methods'    => array('POST'),
));

$collection->create('api_agents_save_profile', array(
    'path'       => '/agents/{id}/profile',
    'controller' => 'LegacyApiBundle:Agents:saveAgentProfile',
    'methods'    => array('POST'),
));

$collection->create('api_agents_resetpassword', array(
    'path'       => '/agents/{id}/reset-password',
    'controller' => 'LegacyApiBundle:Agents:resetPassword',
    'methods'    => array('POST'),
));

$collection->create('api_agents_getlogintoken', array(
    'path'       => '/agents/{id}/login-token',
    'controller' => 'LegacyApiBundle:Agents:generateLoginToken',
    'methods'    => array('GET'),
));

$collection->create('api_agents_create', array(
    'path'       => '/agents',
    'controller' => 'LegacyApiBundle:Agents:saveAgent',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_agents_create_bulk', array(
    'path'       => '/agents_bulk',
    'controller' => 'LegacyApiBundle:Agents:bulkCreateAgents',
    'methods'    => array('POST'),
));

$collection->create('api_agents_create_bulk_check', array(
    'path'       => '/agents_bulk/check',
    'controller' => 'LegacyApiBundle:Agents:bulkLicenseCheck',
    'methods'    => array('POST'),
));

$collection->create('api_agents_notifyprefs_gettables', array(
    'path'       => '/agents/{id}/notify-prefs/get-tables',
    'controller' => 'LegacyApiBundle:Agents:getNotifyPrefs',
    'methods'    => array('GET'),
));

$collection->create('api_agent_teams_list', array(
    'path'       => '/agent_teams',
    'controller' => 'LegacyApiBundle:AgentTeams:listTeams',
    'methods'    => array('GET'),
));

$collection->create('api_agent_teams_get', array(
    'path'       => '/agent_teams/{id}',
    'controller' => 'LegacyApiBundle:AgentTeams:getTeam',
    'methods'    => array('GET'),
));

$collection->create('api_agent_teams_update', array(
    'path'       => '/agent_teams/{id}',
    'controller' => 'LegacyApiBundle:AgentTeams:saveTeam',
    'methods'    => array('POST'),
));

$collection->create('api_agent_teams_create', array(
    'path'       => '/agent_teams',
    'controller' => 'LegacyApiBundle:AgentTeams:saveTeam',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_agent_teams_delete', array(
    'path'       => '/agent_teams/{id}',
    'controller' => 'LegacyApiBundle:AgentTeams:deleteTeam',
    'methods'    => array('DELETE'),
));

$collection->create('api_agentgroups_list', array(
    'path'       => '/agent_groups',
    'controller' => 'LegacyApiBundle:AgentGroups:list',
    'methods'    => array('GET'),
));

$collection->create('api_agentgroups_get', array(
    'path'       => '/agent_groups/{id}',
    'controller' => 'LegacyApiBundle:AgentGroups:getGroup',
    'methods'    => array('GET'),
));

$collection->create('api_agentgroups_save', array(
    'path'       => '/agent_groups/{id}',
    'controller' => 'LegacyApiBundle:AgentGroups:saveGroup',
    'methods'    => array('POST'),
));

$collection->create('api_agentgroups_create', array(
    'path'       => '/agent_groups',
    'controller' => 'LegacyApiBundle:AgentGroups:saveGroup',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_agentgroups_del', array(
    'path'       => '/agent_groups/{id}',
    'controller' => 'LegacyApiBundle:AgentGroups:deleteGroup',
    'methods'    => array('DELETE'),
));

$collection->create('api_agentgroups_enable', array(
    'path'       => '/agent_groups/{id}/enable',
    'controller' => 'LegacyApiBundle:AgentGroups:toggleGroup',
    'defaults'   => array('is_enabled' => true),
    'methods'    => array('POST'),
));

$collection->create('api_agentgroups_disable', array(
    'path'       => '/agent_groups/{id}/disable',
    'controller' => 'LegacyApiBundle:AgentGroups:toggleGroup',
    'defaults'   => array('is_enabled' => false),
    'methods'    => array('POST'),
));

$collection->create('api_agentgroups_getperms', array(
    'path'       => '/agent_groups/all/permissions',
    'controller' => 'LegacyApiBundle:AgentGroups:getAllPerms',
    'defaults'   => array(),
    'methods'    => array('GET'),
));

$collection->create('api_combiner', array(
    'path'       => '/api_caller',
    'controller' => 'LegacyApiBundle:ApiCombiner:get',
    'methods'    => array('GET'),
));

$collection->create('api_organizations', array(
    'path'       => '/organizations',
    'controller' => 'LegacyApiBundle:Organization:search',
    'methods'    => array('GET'),
));

$collection->create('api_organizations_quick_search', array(
    'path'       => '/organizations/quick_search',
    'controller' => 'LegacyApiBundle:Organization:quickSearch',
    'methods'    => array('GET'),
));

$collection->create('api_organizations_post', array(
    'path'       => '/organizations',
    'controller' => 'LegacyApiBundle:Organization:newOrganization',
    'methods'    => array('POST'),
));

$collection->create('api_organizations_organization', array(
    'path'         => '/organizations/{organization_id}',
    'controller'   => 'LegacyApiBundle:Organization:getOrganization',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_post', array(
    'path'         => '/organizations/{organization_id}',
    'controller'   => 'LegacyApiBundle:Organization:postOrganization',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_delete', array(
    'path'         => '/organizations/{organization_id}',
    'controller'   => 'LegacyApiBundle:Organization:deleteOrganization',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_organizations_organization_picture', array(
    'path'         => '/organizations/{organization_id}/picture',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationPicture',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_picture_post', array(
    'path'         => '/organizations/{organization_id}/picture',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationPicture',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_picture_delete', array(
    'path'         => '/organizations/{organization_id}/picture',
    'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationPicture',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_organizations_organization_activity_stream', array(
    'path'         => '/organizations/{organization_id}/activity-stream',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationActivityStream',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_members', array(
    'path'         => '/organizations/{organization_id}/members',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationMembers',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_tickets', array(
    'path'         => '/organizations/{organization_id}/tickets',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationTickets',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_chats', array(
    'path'         => '/organizations/{organization_id}/chats',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationChats',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_billing_charges', array(
    'path'         => '/organizations/{organization_id}/billing-charges',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationBillingCharges',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_email_domains', array(
    'path'         => '/organizations/{organization_id}/email-domains',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationEmailDomains',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_email_domains_post', array(
    'path'         => '/organizations/{organization_id}/email-domains',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationEmailDomains',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_email_domain', array(
    'path'         => '/organizations/{organization_id}/email-domains/{domain}',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationEmailDomain',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_email_domain_move_users', array(
    'path'         => '/organizations/{organization_id}/email-domains/{domain}/move-users',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationEmailDomainMoveUsers',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_email_domain_move_taken_users', array(
    'path'         => '/organizations/{organization_id}/email-domains/{domain}/move-taken-users',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationEmailDomainMoveTakenUsers',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_email_domain_delete', array(
    'path'         => '/organizations/{organization_id}/email-domains/{domain}',
    'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationEmailDomain',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_organizations_organization_contact_details', array(
    'path'         => '/organizations/{organization_id}/contact-details',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationContactDetails',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_contact_details_post', array(
    'path'         => '/organizations/{organization_id}/contact-details',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationContactDetails',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_contact_detail', array(
    'path'         => '/organizations/{organization_id}/contact-details/{contact_id}',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationContactDetail',
    'requirements' => array('organization_id' => '\\d+', 'contact_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_contact_detail_delete', array(
    'path'         => '/organizations/{organization_id}/contact-details/{contact_id}',
    'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationContactDetail',
    'requirements' => array('organization_id' => '\\d+', 'contact_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_organizations_organization_groups', array(
    'path'         => '/organizations/{organization_id}/groups',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationGroups',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_groups_post', array(
    'path'         => '/organizations/{organization_id}/groups',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationGroups',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_group', array(
    'path'         => '/organizations/{organization_id}/groups/{usergroup_id}',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationGroup',
    'requirements' => array('organization_id' => '\\d+', 'usergroup_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_group_delete', array(
    'path'         => '/organizations/{organization_id}/groups/{usergroup_id}',
    'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationGroup',
    'requirements' => array('organization_id' => '\\d+', 'usergroup_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_organizations_organization_labels', array(
    'path'         => '/organizations/{organization_id}/labels',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationLabels',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_labels_post', array(
    'path'         => '/organizations/{organization_id}/labels',
    'controller'   => 'LegacyApiBundle:Organization:postOrganizationLabels',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_organizations_organization_label', array(
    'path'         => '/organizations/{organization_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Organization:getOrganizationLabel',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_organizations_organization_label_delete', array(
    'path'         => '/organizations/{organization_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationLabel',
    'requirements' => array('organization_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_organizations_fields', array(
    'path'       => '/organizations/fields',
    'controller' => 'LegacyApiBundle:Organization:getFields',
    'methods'    => array('GET'),
));

$collection->create('api_organizations_groups', array(
    'path'       => '/organizations/groups',
    'controller' => 'LegacyApiBundle:Organization:getGroups',
    'methods'    => array('GET'),
));

$collection->create('api_chats', array(
    'path'       => '/chats',
    'controller' => 'LegacyApiBundle:Chat:search',
    'methods'    => array('GET'),
));

$collection->create('api_chats_chat', array(
    'path'         => '/chats/{chat_id}',
    'controller'   => 'LegacyApiBundle:Chat:getChat',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chats_chat_post', array(
    'path'         => '/chats/{chat_id}',
    'controller'   => 'LegacyApiBundle:Chat:postChat',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chats_chat_leave', array(
    'path'         => '/chats/{chat_id}/leave',
    'controller'   => 'LegacyApiBundle:Chat:leaveChat',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chats_chat_end', array(
    'path'         => '/chats/{chat_id}/end',
    'controller'   => 'LegacyApiBundle:Chat:endChat',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chats_chat_messages', array(
    'path'         => '/chats/{chat_id}/messages',
    'controller'   => 'LegacyApiBundle:Chat:getMessages',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chats_chat_messages_post', array(
    'path'         => '/chats/{chat_id}/messages',
    'controller'   => 'LegacyApiBundle:Chat:newMessage',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chats_chat_participants', array(
    'path'         => '/chats/{chat_id}/participants',
    'controller'   => 'LegacyApiBundle:Chat:getParticipants',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chats_chat_participants_post', array(
    'path'         => '/chats/{chat_id}/participants',
    'controller'   => 'LegacyApiBundle:Chat:postParticipants',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chats_chat_participant', array(
    'path'         => '/chats/{chat_id}/participants/{person_id}',
    'controller'   => 'LegacyApiBundle:Chat:getParticipant',
    'requirements' => array('chat_id' => '\\d+', 'person_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chats_chat_participant_delete', array(
    'path'         => '/chats/{chat_id}/participants/{person_id}',
    'controller'   => 'LegacyApiBundle:Chat:deleteParticipant',
    'requirements' => array('chat_id' => '\\d+', 'person_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_chats_chat_labels', array(
    'path'         => '/chats/{chat_id}/labels',
    'controller'   => 'LegacyApiBundle:Chat:getChatLabels',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chats_chat_labels_post', array(
    'path'         => '/chats/{chat_id}/labels',
    'controller'   => 'LegacyApiBundle:Chat:postChatLabels',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chats_chat_label', array(
    'path'         => '/chats/{chat_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Chat:getChatLabel',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chats_chat_label_delete', array(
    'path'         => '/chats/{chat_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Chat:deleteChatLabel',
    'requirements' => array('chat_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_downloads', array(
    'path'       => '/downloads',
    'controller' => 'LegacyApiBundle:Download:search',
    'methods'    => array('GET'),
));

$collection->create('api_downloads_post', array(
    'path'       => '/downloads',
    'controller' => 'LegacyApiBundle:Download:newDownload',
    'methods'    => array('POST'),
));

$collection->create('api_downloads_download', array(
    'path'         => '/downloads/{download_id}',
    'controller'   => 'LegacyApiBundle:Download:getDownload',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_download_post', array(
    'path'         => '/downloads/{download_id}',
    'controller'   => 'LegacyApiBundle:Download:postDownload',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_downloads_download_delete', array(
    'path'         => '/downloads/{download_id}',
    'controller'   => 'LegacyApiBundle:Download:deleteDownload',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_downloads_download_comments', array(
    'path'         => '/downloads/{download_id}/comments',
    'controller'   => 'LegacyApiBundle:Download:getDownloadComments',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_download_comments_new', array(
    'path'         => '/downloads/{download_id}/comments',
    'controller'   => 'LegacyApiBundle:Download:newDownloadComment',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_downloads_download_comments_comment', array(
    'path'         => '/downloads/{download_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Download:getDownloadComment',
    'requirements' => array('download_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_download_comments_comment_post', array(
    'path'         => '/downloads/{download_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Download:postDownloadComment',
    'requirements' => array('download_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_downloads_download_comments_comment_delete', array(
    'path'         => '/downloads/{download_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Download:deleteDownloadComment',
    'requirements' => array('download_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_downloads_download_labels', array(
    'path'         => '/downloads/{download_id}/labels',
    'controller'   => 'LegacyApiBundle:Download:getDownloadLabels',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_download_labels_post', array(
    'path'         => '/downloads/{download_id}/labels',
    'controller'   => 'LegacyApiBundle:Download:postDownloadLabels',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_downloads_download_label', array(
    'path'         => '/downloads/{download_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Download:getDownloadLabel',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_download_label_delete', array(
    'path'         => '/downloads/{download_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Download:deleteDownloadLabel',
    'requirements' => array('download_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_downloads_validating_comments', array(
    'path'       => '/downloads/validating-comments',
    'controller' => 'LegacyApiBundle:Download:getValidatingComments',
    'methods'    => array('GET'),
));

$collection->create('api_downloads_categories', array(
    'path'       => '/downloads/categories',
    'controller' => 'LegacyApiBundle:Download:getCategories',
    'methods'    => array('GET'),
));

$collection->create('api_downloads_categories_post', array(
    'path'       => '/downloads/categories',
    'controller' => 'LegacyApiBundle:Download:postCategories',
    'methods'    => array('POST'),
));

$collection->create('api_downloads_category', array(
    'path'         => '/downloads/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:Download:getCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_category_post', array(
    'path'         => '/downloads/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:Download:postCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_downloads_category_delete', array(
    'path'         => '/downloads/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:Download:deleteCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_downloads_category_downloads', array(
    'path'         => '/downloads/categories/{category_id}/downloads',
    'controller'   => 'LegacyApiBundle:Download:getCategoryDownloads',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_category_groups', array(
    'path'         => '/downloads/categories/{category_id}/groups',
    'controller'   => 'LegacyApiBundle:Download:getCategoryGroups',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_category_groups_post', array(
    'path'         => '/downloads/categories/{category_id}/groups',
    'controller'   => 'LegacyApiBundle:Download:postCategoryGroups',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_downloads_category_group', array(
    'path'         => '/downloads/categories/{category_id}/groups/{group_id}',
    'controller'   => 'LegacyApiBundle:Download:getCategoryGroup',
    'requirements' => array('category_id' => '\\d+', 'group_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_downloads_category_group_delete', array(
    'path'         => '/downloads/categories/{category_id}/groups/{group_id}',
    'controller'   => 'LegacyApiBundle:Download:deleteCategoryGroup',
    'requirements' => array('category_id' => '\\d+', 'group_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_news', array(
    'path'       => '/news',
    'controller' => 'LegacyApiBundle:News:search',
    'methods'    => array('GET'),
));

$collection->create('api_news_post', array(
    'path'       => '/news',
    'controller' => 'LegacyApiBundle:News:newNews',
    'methods'    => array('POST'),
));

$collection->create('api_news_news', array(
    'path'         => '/news/{news_id}',
    'controller'   => 'LegacyApiBundle:News:getNews',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_news_post', array(
    'path'         => '/news/{news_id}',
    'controller'   => 'LegacyApiBundle:News:postNews',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_news_news_delete', array(
    'path'         => '/news/{news_id}',
    'controller'   => 'LegacyApiBundle:News:deleteNews',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_news_news_comments', array(
    'path'         => '/news/{news_id}/comments',
    'controller'   => 'LegacyApiBundle:News:getNewsComments',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_news_comments_new', array(
    'path'         => '/news/{news_id}/comments',
    'controller'   => 'LegacyApiBundle:News:newNewsComment',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_news_news_comments_comment', array(
    'path'         => '/news/{news_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:News:getNewsComment',
    'requirements' => array('news_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_news_comments_comment_post', array(
    'path'         => '/news/{news_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:News:postNewsComment',
    'requirements' => array('news_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_news_news_comments_comment_delete', array(
    'path'         => '/news/{news_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:News:deleteNewsComment',
    'requirements' => array('news_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_news_news_labels', array(
    'path'         => '/news/{news_id}/labels',
    'controller'   => 'LegacyApiBundle:News:getNewsLabels',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_news_labels_post', array(
    'path'         => '/news/{news_id}/labels',
    'controller'   => 'LegacyApiBundle:News:postNewsLabels',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_news_news_label', array(
    'path'         => '/news/{news_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:News:getNewsLabel',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_news_label_delete', array(
    'path'         => '/news/{news_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:News:deleteNewsLabel',
    'requirements' => array('news_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_news_validating_comments', array(
    'path'       => '/news/validating-comments',
    'controller' => 'LegacyApiBundle:News:getValidatingComments',
    'methods'    => array('GET'),
));

$collection->create('api_news_categories', array(
    'path'       => '/news/categories',
    'controller' => 'LegacyApiBundle:News:getCategories',
    'methods'    => array('GET'),
));

$collection->create('api_news_categories_post', array(
    'path'       => '/news/categories',
    'controller' => 'LegacyApiBundle:News:postCategories',
    'methods'    => array('POST'),
));

$collection->create('api_news_category', array(
    'path'         => '/news/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:News:getCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_category_post', array(
    'path'         => '/news/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:News:postCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_news_category_delete', array(
    'path'         => '/news/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:News:deleteCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_news_category_news', array(
    'path'         => '/news/categories/{category_id}/news',
    'controller'   => 'LegacyApiBundle:News:getCategoryNews',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_category_groups', array(
    'path'         => '/news/categories/{category_id}/groups',
    'controller'   => 'LegacyApiBundle:News:getCategoryGroups',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_category_groups_post', array(
    'path'         => '/news/categories/{category_id}/groups',
    'controller'   => 'LegacyApiBundle:News:postCategoryGroups',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_news_category_group', array(
    'path'         => '/news/categories/{category_id}/groups/{group_id}',
    'controller'   => 'LegacyApiBundle:News:getCategoryGroup',
    'requirements' => array('category_id' => '\\d+', 'group_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_news_category_group_delete', array(
    'path'         => '/news/categories/{category_id}/groups/{group_id}',
    'controller'   => 'LegacyApiBundle:News:deleteCategoryGroup',
    'requirements' => array('category_id' => '\\d+', 'group_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb', array(
    'path'       => '/kb',
    'controller' => 'LegacyApiBundle:Kb:search',
    'methods'    => array('GET'),
));

$collection->create('api_kb_post', array(
    'path'       => '/kb',
    'controller' => 'LegacyApiBundle:Kb:newArticle',
    'methods'    => array('POST'),
));

$collection->create('api_kb_article', array(
    'path'         => '/kb/{article_id}',
    'controller'   => 'LegacyApiBundle:Kb:getArticle',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_post', array(
    'path'         => '/kb/{article_id}',
    'controller'   => 'LegacyApiBundle:Kb:postArticle',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_article_delete', array(
    'path'         => '/kb/{article_id}',
    'controller'   => 'LegacyApiBundle:Kb:deleteArticle',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb_article_votes', array(
    'path'         => '/kb/{article_id}/votes',
    'controller'   => 'LegacyApiBundle:Kb:getArticleVotes',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_comments', array(
    'path'         => '/kb/{article_id}/comments',
    'controller'   => 'LegacyApiBundle:Kb:getArticleComments',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_comments_new', array(
    'path'         => '/kb/{article_id}/comments',
    'controller'   => 'LegacyApiBundle:Kb:newArticleComment',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_article_comments_comment', array(
    'path'         => '/kb/{article_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Kb:getArticleComment',
    'requirements' => array('article_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_comments_comment_post', array(
    'path'         => '/kb/{article_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Kb:postArticleComment',
    'requirements' => array('article_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_article_comments_comment_delete', array(
    'path'         => '/kb/{article_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Kb:deleteArticleComment',
    'requirements' => array('article_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb_article_attachments', array(
    'path'         => '/kb/{article_id}/attachments',
    'controller'   => 'LegacyApiBundle:Kb:getArticleAttachments',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_attachments_post', array(
    'path'         => '/kb/{article_id}/attachments',
    'controller'   => 'LegacyApiBundle:Kb:newArticleAttachment',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_article_attachment', array(
    'path'         => '/kb/{article_id}/attachments/{attachment_id}',
    'controller'   => 'LegacyApiBundle:Kb:getArticleAttachment',
    'requirements' => array('article_id' => '\\d+', 'attachment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_attachment_delete', array(
    'path'         => '/kb/{article_id}/attachments/{attachment_id}',
    'controller'   => 'LegacyApiBundle:Kb:deleteArticleAttachment',
    'requirements' => array('article_id' => '\\d+', 'attachment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb_article_labels', array(
    'path'         => '/kb/{article_id}/labels',
    'controller'   => 'LegacyApiBundle:Kb:getArticleLabels',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_labels_post', array(
    'path'         => '/kb/{article_id}/labels',
    'controller'   => 'LegacyApiBundle:Kb:postArticleLabels',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_article_label', array(
    'path'         => '/kb/{article_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Kb:getArticleLabel',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_article_label_delete', array(
    'path'         => '/kb/{article_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Kb:deleteArticleLabel',
    'requirements' => array('article_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb_validating_comments', array(
    'path'       => '/kb/validating-comments',
    'controller' => 'LegacyApiBundle:Kb:getValidatingComments',
    'methods'    => array('GET'),
));

$collection->create('api_kb_categories', array(
    'path'       => '/kb/categories',
    'controller' => 'LegacyApiBundle:Kb:getCategories',
    'methods'    => array('GET'),
));

$collection->create('api_kb_categories_post', array(
    'path'       => '/kb/categories',
    'controller' => 'LegacyApiBundle:Kb:postCategories',
    'methods'    => array('POST'),
));

$collection->create('api_kb_category', array(
    'path'         => '/kb/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:Kb:getCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_category_post', array(
    'path'         => '/kb/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:Kb:postCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_category_delete', array(
    'path'         => '/kb/categories/{category_id}',
    'controller'   => 'LegacyApiBundle:Kb:deleteCategory',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb_category_articles', array(
    'path'         => '/kb/categories/{category_id}/articles',
    'controller'   => 'LegacyApiBundle:Kb:getCategoryArticles',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_category_groups', array(
    'path'         => '/kb/categories/{category_id}/groups',
    'controller'   => 'LegacyApiBundle:Kb:getCategoryGroups',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_category_groups_post', array(
    'path'         => '/kb/categories/{category_id}/groups',
    'controller'   => 'LegacyApiBundle:Kb:postCategoryGroups',
    'requirements' => array('category_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_kb_category_group', array(
    'path'         => '/kb/categories/{category_id}/groups/{group_id}',
    'controller'   => 'LegacyApiBundle:Kb:getCategoryGroup',
    'requirements' => array('category_id' => '\\d+', 'group_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_kb_category_group_delete', array(
    'path'         => '/kb/categories/{category_id}/groups/{group_id}',
    'controller'   => 'LegacyApiBundle:Kb:deleteCategoryGroup',
    'requirements' => array('category_id' => '\\d+', 'group_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_kb_fields', array(
    'path'       => '/kb/fields',
    'controller' => 'LegacyApiBundle:Kb:getFields',
    'methods'    => array('GET'),
));

$collection->create('api_kb_products', array(
    'path'       => '/kb/products',
    'controller' => 'LegacyApiBundle:Kb:getProducts',
    'methods'    => array('GET'),
));

$collection->create('api_feedback', array(
    'path'       => '/feedback',
    'controller' => 'LegacyApiBundle:Feedback:search',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_post', array(
    'path'       => '/feedback',
    'controller' => 'LegacyApiBundle:Feedback:newFeedback',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_feedback', array(
    'path'         => '/feedback/{feedback_id}',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedback',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_post', array(
    'path'         => '/feedback/{feedback_id}',
    'controller'   => 'LegacyApiBundle:Feedback:postFeedback',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_feedback_feedback_delete', array(
    'path'         => '/feedback/{feedback_id}',
    'controller'   => 'LegacyApiBundle:Feedback:deleteFeedback',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_feedback_feedback_votes', array(
    'path'         => '/feedback/{feedback_id}/votes',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackVotes',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_comments', array(
    'path'         => '/feedback/{feedback_id}/comments',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackComments',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_comments_new', array(
    'path'         => '/feedback/{feedback_id}/comments',
    'controller'   => 'LegacyApiBundle:Feedback:newFeedbackComment',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_feedback_feedback_comments_comment', array(
    'path'         => '/feedback/{feedback_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackComment',
    'requirements' => array('feedback_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_comments_comment_post', array(
    'path'         => '/feedback/{feedback_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Feedback:postFeedbackComment',
    'requirements' => array('feedback_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_feedback_feedback_comments_comment_delete', array(
    'path'         => '/feedback/{feedback_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Feedback:deleteFeedbackComment',
    'requirements' => array('feedback_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_feedback_feedback_merge', array(
    'path'         => '/feedback/{feedback_id}/merge/{other_feedback_id}',
    'controller'   => 'LegacyApiBundle:Feedback:mergeFeedback',
    'requirements' => array('feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_feedback_feedback_attachments', array(
    'path'         => '/feedback/{feedback_id}/attachments',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackAttachments',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_attachments_post', array(
    'path'         => '/feedback/{feedback_id}/attachments',
    'controller'   => 'LegacyApiBundle:Feedback:newFeedbackAttachment',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_feedback_feedback_attachment', array(
    'path'         => '/feedback/{feedback_id}/attachments/{attachment_id}',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackAttachment',
    'requirements' => array('feedback_id' => '\\d+', 'attachment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_attachment_delete', array(
    'path'         => '/feedback/{feedback_id}/attachments/{attachment_id}',
    'controller'   => 'LegacyApiBundle:Feedback:deleteFeedbackAttachment',
    'requirements' => array('feedback_id' => '\\d+', 'attachment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_feedback_feedback_labels', array(
    'path'         => '/feedback/{feedback_id}/labels',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackLabels',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_labels_post', array(
    'path'         => '/feedback/{feedback_id}/labels',
    'controller'   => 'LegacyApiBundle:Feedback:postFeedbackLabels',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_feedback_feedback_label', array(
    'path'         => '/feedback/{feedback_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Feedback:getFeedbackLabel',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_feedback_feedback_label_delete', array(
    'path'         => '/feedback/{feedback_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Feedback:deleteFeedbackLabel',
    'requirements' => array('feedback_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_feedback_validating_comments', array(
    'path'       => '/feedback/validating-comments',
    'controller' => 'LegacyApiBundle:Feedback:getValidatingComments',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_categories', array(
    'path'       => '/feedback/categories',
    'controller' => 'LegacyApiBundle:Feedback:getCategories',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_status_categories', array(
    'path'       => '/feedback/status-categories',
    'controller' => 'LegacyApiBundle:Feedback:getStatusCategories',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_user_categories', array(
    'path'       => '/feedback/user-categories',
    'controller' => 'LegacyApiBundle:Feedback:getUserCategories',
    'methods'    => array('GET'),
));

$collection->create('api_tasks', array(
    'path'       => '/tasks',
    'controller' => 'LegacyApiBundle:Task:search',
    'methods'    => array('GET'),
));

$collection->create('api_tasks_post', array(
    'path'       => '/tasks',
    'controller' => 'LegacyApiBundle:Task:newTask',
    'methods'    => array('POST'),
));

$collection->create('api_tasks_task', array(
    'path'         => '/tasks/{task_id}',
    'controller'   => 'LegacyApiBundle:Task:getTask',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_post', array(
    'path'         => '/tasks/{task_id}',
    'controller'   => 'LegacyApiBundle:Task:postTask',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tasks_task_delete', array(
    'path'         => '/tasks/{task_id}',
    'controller'   => 'LegacyApiBundle:Task:deleteTask',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tasks_task_associations', array(
    'path'         => '/tasks/{task_id}/associations',
    'controller'   => 'LegacyApiBundle:Task:getTaskAssociations',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_associations_post', array(
    'path'         => '/tasks/{task_id}/associations',
    'controller'   => 'LegacyApiBundle:Task:postTaskAssociations',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tasks_task_associated_item', array(
    'path'         => '/tasks/{task_id}/associations/{assoc_id}',
    'controller'   => 'LegacyApiBundle:Task:getTaskAssociation',
    'requirements' => array('task_id' => '\\d+', 'assoc_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_comments', array(
    'path'         => '/tasks/{task_id}/comments',
    'controller'   => 'LegacyApiBundle:Task:getTaskComments',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_comments_post', array(
    'path'         => '/tasks/{task_id}/comments',
    'controller'   => 'LegacyApiBundle:Task:postTaskComments',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tasks_task_comment', array(
    'path'         => '/tasks/{task_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Task:getTaskComment',
    'requirements' => array('task_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_associated_item_delete', array(
    'path'         => '/tasks/{task_id}/comments/{comment_id}',
    'controller'   => 'LegacyApiBundle:Task:deleteTaskComment',
    'requirements' => array('task_id' => '\\d+', 'comment_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_tasks_task_labels', array(
    'path'         => '/tasks/{task_id}/labels',
    'controller'   => 'LegacyApiBundle:Task:getTaskLabels',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_labels_post', array(
    'path'         => '/tasks/{task_id}/labels',
    'controller'   => 'LegacyApiBundle:Task:postTaskLabels',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_tasks_task_label', array(
    'path'         => '/tasks/{task_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Task:getTaskLabel',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_tasks_task_label_delete', array(
    'path'         => '/tasks/{task_id}/labels/{label}',
    'controller'   => 'LegacyApiBundle:Task:deleteTaskLabel',
    'requirements' => array('task_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_glossary', array(
    'path'       => '/glossary',
    'controller' => 'LegacyApiBundle:Glossary:list',
    'methods'    => array('GET'),
));

$collection->create('api_glossary_lookup', array(
    'path'       => '/glossary/lookup',
    'controller' => 'LegacyApiBundle:Glossary:lookup',
    'methods'    => array('GET'),
));

$collection->create('api_glossary_post', array(
    'path'       => '/glossary',
    'controller' => 'LegacyApiBundle:Glossary:newWord',
    'methods'    => array('POST'),
));

$collection->create('api_glossary_word', array(
    'path'         => '/glossary/{word_id}',
    'controller'   => 'LegacyApiBundle:Glossary:getWord',
    'requirements' => array('word_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_glossary_word_delete', array(
    'path'         => '/glossary/{word_id}',
    'controller'   => 'LegacyApiBundle:Glossary:deleteWord',
    'requirements' => array('word_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_glossary_definition', array(
    'path'         => '/glossary/definitions/{definition_id}',
    'controller'   => 'LegacyApiBundle:Glossary:getDefinition',
    'requirements' => array('definition_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_glossary_definition_post', array(
    'path'         => '/glossary/definitions/{definition_id}',
    'controller'   => 'LegacyApiBundle:Glossary:postDefinition',
    'requirements' => array('definition_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_glossary_definition_delete', array(
    'path'         => '/glossary/definitions/{definition_id}',
    'controller'   => 'LegacyApiBundle:Glossary:deleteDefinition',
    'requirements' => array('definition_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_dismiss_activity', array(
    'path'       => '/activity/dismiss',
    'controller' => 'LegacyApiBundle:Activity:dismiss',
    'methods'    => array('POST'),
));

$collection->create('api_get_activity', array(
    'path'         => '/activity/{since}',
    'controller'   => 'LegacyApiBundle:Activity:getActivity',
    'requirements' => array('since' => '\\d+'),
    'methods'      => array('GET'),
));

########################################################################################################################
# Label Management
########################################################################################################################

$label_types = array(
    array('route' => 'ticket',    'route_url' => 'ticket',    'controller' => 'TicketLabels'),
    array('route' => 'person',    'route_url' => 'person',    'controller' => 'PersonLabels'),
    array('route' => 'org',       'route_url' => 'org',       'controller' => 'OrgLabels'),
    array('route' => 'feedback',  'route_url' => 'feedback',  'controller' => 'FeedbackLabels'),
    array('route' => 'chat',      'route_url' => 'chat',      'controller' => 'ChatLabels'),
    array('route' => 'kb',        'route_url' => 'kb',        'controller' => 'KbLabels'),
    array('route' => 'news',      'route_url' => 'news',      'controller' => 'NewsLabels'),
    array('route' => 'downloads', 'route_url' => 'downloads', 'controller' => 'DownloadsLabels'),
);

foreach ($label_types as $info) {
    $collection->create("api_{$info['route']}_labels", array(
        'path'       => "/{$info['route_url']}_labels",
        'controller' => "LegacyApiBundle:{$info['controller']}:list",
        'methods'    => array('GET'),
    ));

    $collection->create("api_{$info['route']}_labels_get", array(
        'path'       => "/{$info['route_url']}_labels/get",
        'controller' => "LegacyApiBundle:{$info['controller']}:get",
        'methods'    => array('GET'),
    ));

    $collection->create("api_{$info['route']}_labels_save", array(
        'path'       => "/{$info['route_url']}_labels/save",
        'controller' => "LegacyApiBundle:{$info['controller']}:save",
        'methods'    => array('POST'),
    ));

    $collection->create("api_{$info['route']}_labels_add", array(
        'path'       => "/{$info['route_url']}_labels",
        'controller' => "LegacyApiBundle:{$info['controller']}:add",
        'methods'    => array('POST'),
    ));

    $collection->create("api_{$info['route']}_labels_remove", array(
        'path'       => "/{$info['route_url']}_labels",
        'controller' => "LegacyApiBundle:{$info['controller']}:remove",
        'methods'    => array('DELETE'),
    ));
}

########################################################################################################################
# Round Robin
########################################################################################################################

$collection->create('api_roundrobins_list', array(
    'path'       => '/round_robin',
    'controller' => 'LegacyApiBundle:RoundRobin:list',
    'methods'    => array('GET'),
));

$collection->create('api_roundrobins_settings', array(
    'path'       => '/round_robin/settings',
    'controller' => 'LegacyApiBundle:RoundRobin:settings',
    'methods'    => array('GET', 'PUT'),
));

$collection->create('api_roundrobins_triggers', array(
    'path'       => '/round_robin/triggers/{id}',
    'controller' => 'LegacyApiBundle:RoundRobin:checkTriggers',
    'defaults'   => array('id' => null),
    'methods'    => array('GET'),
));

$collection->create('api_roundrobins_get', array(
    'path'       => '/round_robin/{id}',
    'controller' => 'LegacyApiBundle:RoundRobin:get',
    'methods'    => array('GET'),
));

$collection->create('api_roundrobins_set', array(
    'path'       => '/round_robin/{id}',
    'controller' => 'LegacyApiBundle:RoundRobin:set',
    'methods'    => array('POST', 'PUT'),
    'defaults'   => array('id' => 0),
));

$collection->create('api_roundrobins_delete', array(
    'path'       => '/round_robin/{id}',
    'controller' => 'LegacyApiBundle:RoundRobin:delete',
    'methods'    => array('DELETE'),
));

$collection->create('api_roundrobins_logs', array(
    'path'       => '/round_robin/{id}/logs',
    'controller' => 'LegacyApiBundle:RoundRobin:logs',
    'methods'    => array('GET'),
));

########################################################################################################################
# Start Settings
########################################################################################################################

$collection->create('api_startsettings_set', array(
    'path'       => '/start-settings',
    'controller' => 'LegacyApiBundle:Settings:setStartSettings',
    'methods'    => array('POST'),
));

$collection->create('api_startsettings_setinitial', array(
    'path'       => '/start-settings/set-initial',
    'controller' => 'LegacyApiBundle:Settings:setDoneInitial',
    'methods'    => array('POST'),
));

########################################################################################################################
# Settings
########################################################################################################################

$collection->create('api_settings_values_get', array(
    'path'       => '/settings/values/{name}',
    'controller' => 'LegacyApiBundle:Settings:getValue',
    'methods'    => array('GET'),
));

$collection->create('api_settings_values_set', array(
    'path'       => '/settings/values/{name}',
    'controller' => 'LegacyApiBundle:Settings:setValue',
    'methods'    => array('POST'),
));

########################################################################################################################
# Portal App Settings
########################################################################################################################

$collection->create('api_settings_portalapps', array(
    'path'       => '/settings/portal/{app}',
    'controller' => 'LegacyApiBundle:Settings:portalAppSettings',
    'methods'    => array('GET'),
));

$collection->create('api_settings_portalapps_save', array(
    'path'       => '/settings/portal/{app}',
    'controller' => 'LegacyApiBundle:Settings:savePortalAppSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# Server Settings
########################################################################################################################

$collection->create('api_server_settings', array(
    'path'       => '/server_settings',
    'controller' => 'LegacyApiBundle:Settings:serverSettings',
    'methods'    => array('GET'),
));

$collection->create('api_server_settings_save', array(
    'path'       => '/server_settings',
    'controller' => 'LegacyApiBundle:Settings:saveServerSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# General Settings
########################################################################################################################

$collection->create('api_general_settings_get_logo_blob', array(
    'path'       => '/general_settings/blob',
    'controller' => 'LegacyApiBundle:Settings:getLogoBlob',
    'methods'    => array('GET'),
));

$collection->create('api_general_settings_set_logo_blob', array(
    'path'       => '/general_settings/blob',
    'controller' => 'LegacyApiBundle:Settings:setLogoBlob',
    'methods'    => array('POST'),
));

$collection->create('api_general_settings', array(
    'path'       => '/general_settings',
    'controller' => 'LegacyApiBundle:Settings:generalSettings',
    'methods'    => array('GET'),
));

$collection->create('api_general_settings_save', array(
    'path'       => '/general_settings',
    'controller' => 'LegacyApiBundle:Settings:saveGeneralSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# Usersources
########################################################################################################################

$collection->create('api_usersources_start_sync', array(
    'path'       => '/usersources/start-sync',
    'controller' => 'LegacyApiBundle:Usersources:startUsersourceSync',
    'methods'    => array('POST'),
));

$collection->create('api_usersources_list', array(
    'path'       => '/usersources/{type}',
    'controller' => 'LegacyApiBundle:Usersources:listByType',
    'methods'    => array('GET'),
));

$collection->create('api_usersources_iframe', array(
    'path'       => '/usersources/iframe/code/{interface}/{app_id}',
    'controller' => 'LegacyApiBundle:Usersources:getIframe',
    'methods'    => array('GET'),
));

$collection->create('api_usersources_display_order', array(
    'path'       => '/usersources/display-order',
    'controller' => 'LegacyApiBundle:Usersources:updateDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_usersources_sync_status', array(
    'path'       => '/usersources/sync/status',
    'controller' => 'LegacyApiBundle:Usersources:syncStatus',
    'methods'    => array('GET'),
));

$collection->create('api_usersources_sync_start', array(
    'path'       => '/usersources/sync/start',
    'controller' => 'LegacyApiBundle:Usersources:syncStart',
    'methods'    => array('POST'),
));

$collection->create('api_usersources_sync_stop', array(
    'path'       => '/usersources/sync/stop',
    'controller' => 'LegacyApiBundle:Usersources:syncStop',
    'methods'    => array('POST'),
));

$collection->create('api_usersources_sync_info', array(
    'path'       => '/usersources/sync/info/{app_id}',
    'controller' => 'LegacyApiBundle:Usersources:getSyncInformation',
    'methods'    => array('GET'),
));

$collection->create('api_usersources_available_apps', array(
    'path'       => '/usersources/available/app-packages/{interface}',
    'controller' => 'LegacyApiBundle:Usersources:availableAppPackages',
    'methods'    => array('GET'),
));

$collection->create('api_usersources_get', array(
    'path'       => '/usersources/{type}/{id}',
    'controller' => 'LegacyApiBundle:Usersources:getUsersource',
    'methods'    => array('GET'),
));

$collection->create('api_usersources_post', array(
    'path'       => '/usersources/{type}/{id}',
    'controller' => 'LegacyApiBundle:Usersources:postUsersource',
    'methods'    => array('POST'),
));

$collection->create('api_usersources_extra_details', array(
    'path'       => '/usersources/{type}/app-{app_id}/extra-details',
    'controller' => 'LegacyApiBundle:Usersources:getUsersourceExtra',
    'methods'    => array('GET'),
));

$collection->create('api_usersource_refresh_person', array(
    'path'       => '/usersources/{usersource_id}/person-refresh/{identity_or_email}',
    'controller' => 'LegacyApiBundle:Usersources:personRefresh',
    'methods'    => array('GET'),
));

########################################################################################################################
# Registration Settings

$collection->create('api_reg_settings', array(
    'path'       => '/registration_settings',
    'controller' => 'LegacyApiBundle:Settings:registrationSettings',
    'methods'    => array('GET'),
));

$collection->create('api_reg_settings_save', array(
    'path'       => '/registration_settings',
    'controller' => 'LegacyApiBundle:Settings:saveRegistrationSettings',
    'methods'    => array('POST'),
));

$collection->create('api_pass_settings', array(
    'path'       => '/password_settings',
    'controller' => 'LegacyApiBundle:Settings:passwordSettings',
    'methods'    => array('GET'),
));

$collection->create('api_pass_settings_save', array(
    'path'       => '/password_settings',
    'controller' => 'LegacyApiBundle:Settings:savePasswordSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# Portal Settings
########################################################################################################################

$collection->create('api_portal_settings', array(
    'path'       => '/portal_settings',
    'controller' => 'LegacyApiBundle:Settings:portalSettings',
    'methods'    => array('GET'),
));

$collection->create('api_portal_settings_save', array(
    'path'       => '/portal_settings',
    'controller' => 'LegacyApiBundle:Settings:savePortalSettings',
    'methods'    => array('POST'),
));

$collection->create('api_portal_settings_setfavicon', array(
    'path'         => '/portal_settings/favicon/{blob_id}/{blob_auth}',
    'controller'   => 'LegacyApiBundle:Settings:saveCustomFavicon',
    'methods'      => array('POST'),
    'requirements' => array('blob_id' => '\\d+', 'blob_auth' => '[a-zA-Z0-9]+'),
));

########################################################################################################################
# Advanced Settings
########################################################################################################################

$collection->create('api_all_settings_raw', array(
    'path'       => '/all_settings_raw',
    'controller' => 'LegacyApiBundle:Settings:allSettingsRaw',
    'methods'    => array('GET'),
));

$collection->create('api_all_settings_raw_save', array(
    'path'       => '/all_settings_raw',
    'controller' => 'LegacyApiBundle:Settings:saveAllSettingsRaw',
    'methods'    => array('POST'),
));

########################################################################################################################
# Elastic Search
########################################################################################################################

$collection->create('api_elastic_settings', array(
    'path'       => '/elastic-search/settings',
    'controller' => 'LegacyApiBundle:ElasticSearch:getSettings',
    'methods'    => array('GET'),
));

$collection->create('api_elastic_index_status', array(
        'path'       => '/elastic-search/index-status',
        'controller' => 'LegacyApiBundle:ElasticSearch:indexStatus',
        'methods'    => array('GET'),
    ));

$collection->create('api_elastic_settings_save', array(
    'path'       => '/elastic-search/settings',
    'controller' => 'LegacyApiBundle:ElasticSearch:saveSettings',
    'methods'    => array('POST'),
));

$collection->create('api_elastic_settings_test', array(
    'path'       => '/elastic-search/settings/test',
    'controller' => 'LegacyApiBundle:ElasticSearch:testSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# License
########################################################################################################################

$collection->create('api_dp_license', array(
    'path'       => '/dp_license',
    'controller' => 'LegacyApiBundle:License:getLicense',
    'methods'    => array('GET'),
));

$collection->create('api_dp_license_save', array(
    'path'       => '/dp_license',
    'controller' => 'LegacyApiBundle:License:setLicense',
    'methods'    => array('POST'),
));

$collection->create('api_dp_keyfile', array(
    'path'         => '/dp_license/keyfile.{_format}',
    'controller'   => 'LegacyApiBundle:License:downloadKeyfile',
    'methods'      => array('GET'),
    'requirements' => array('_format' => 'txt|json'),
));

$collection->create('api_dp_license_supportrequest', array(
    'path'       => '/dp_license/support-request',
    'controller' => 'LegacyApiBundle:License:sendSupportRequest',
    'methods'    => array('POST'),
));

$collection->create('api_dp_license_versioninfo', array(
    'path'       => '/dp_license/version-info',
    'controller' => 'LegacyApiBundle:License:getVersionInfo',
    'methods'    => array('GET'),
));

$collection->create('api_dp_license_latestversion', array(
    'path'       => '/dp_license/latest-version-info',
    'controller' => 'LegacyApiBundle:License:getLatestVersion',
    'methods'    => array('GET'),
));

$collection->create('api_dp_license_news', array(
    'path'       => '/dp_license/news',
    'controller' => 'LegacyApiBundle:License:getNews',
    'methods'    => array('GET'),
));

########################################################################################################################
# Ticket Settings
########################################################################################################################

$collection->create('api_ticket_settings', array(
    'path'       => '/ticket_settings',
    'controller' => 'LegacyApiBundle:Settings:ticketSettings',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_settings_save', array(
    'path'       => '/ticket_settings',
    'controller' => 'LegacyApiBundle:Settings:saveTicketSettings',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_fwd_settings', array(
    'path'       => '/ticket_settings/fwd',
    'controller' => 'LegacyApiBundle:Settings:ticketFwdSettings',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_fwd_settings_save', array(
    'path'       => '/ticket_settings/fwd',
    'controller' => 'LegacyApiBundle:Settings:saveTicketFwdSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# Reg Settings
########################################################################################################################

$collection->create('api_registration_settings', array(
    'path'       => '/registraton_settings',
    'controller' => 'LegacyApiBundle:Settings:registrationSettings',
    'methods'    => array('GET'),
));

$collection->create('api_registration_settings_save', array(
    'path'       => '/registraton_settings',
    'controller' => 'LegacyApiBundle:Settings:saveRegistrationSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# Products
########################################################################################################################

$collection->create('api_products', array(
    'path'       => '/products',
    'controller' => 'LegacyApiBundle:TicketFields:listPriorities',
    'methods'    => array('GET'),
));

########################################################################################################################
# Ticket Departments
########################################################################################################################

$collection->create('api_ticket_deps', array(
    'path'       => '/ticket_deps',
    'controller' => 'LegacyApiBundle:TicketDeps:list',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_deps_create', array(
    'path'       => '/ticket_deps',
    'controller' => 'LegacyApiBundle:TicketDeps:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_deps_order', array(
    'path'       => '/ticket_deps/display_order',
    'controller' => 'LegacyApiBundle:TicketDeps:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_deps_settings', array(
    'path'       => '/ticket_deps/settings',
    'controller' => 'LegacyApiBundle:TicketDeps:getSettings',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_deps_settingssave', array(
    'path'       => '/ticket_deps/settings',
    'controller' => 'LegacyApiBundle:TicketDeps:saveSettings',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_deps_get', array(
    'path'       => '/ticket_deps/{id}',
    'controller' => 'LegacyApiBundle:TicketDeps:get',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_deps_save', array(
    'path'       => '/ticket_deps/{id}',
    'controller' => 'LegacyApiBundle:TicketDeps:save',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_deps_remove', array(
    'path'       => '/ticket_deps/{id}',
    'controller' => 'LegacyApiBundle:TicketDeps:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Ticket Layouts
########################################################################################################################

$collection->create('api_ticket_layout_get', array(
    'path'         => '/ticket_layouts/{dep_id}',
    'controller'   => 'LegacyApiBundle:TicketLayouts:get',
    'requirements' => array('dep_id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_ticket_layout_getdefault', array(
    'path'       => '/ticket_layouts/default',
    'controller' => 'LegacyApiBundle:TicketLayouts:get',
    'defaults'   => array('dep_id' => '0'),
    'methods'    => array('GET'),
));

$collection->create('api_ticket_layout_save', array(
    'path'         => '/ticket_layouts/{dep_id}',
    'controller'   => 'LegacyApiBundle:TicketLayouts:save',
    'requirements' => array('dep_id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_layout_delete', array(
    'path'         => '/ticket_layouts/{dep_id}',
    'controller'   => 'LegacyApiBundle:TicketLayouts:delete',
    'requirements' => array('dep_id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_ticket_layout_savedefault', array(
    'path'       => '/ticket_layouts/default',
    'controller' => 'LegacyApiBundle:TicketLayouts:save',
    'defaults'   => array('dep_id' => '0'),
    'methods'    => array('POST'),
));

$collection->create('api_ticket_layout_stats', array(
    'path'       => '/ticket_layouts/stats',
    'controller' => 'LegacyApiBundle:TicketLayouts:getLayoutStats',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_layout_field_status', array(
    'path'       => '/ticket_layouts/fields/{field_id}',
    'controller' => 'LegacyApiBundle:TicketLayouts:getFieldStatus',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_layout_field_status_save', array(
    'path'       => '/ticket_layouts/fields/{field_id}',
    'controller' => 'LegacyApiBundle:TicketLayouts:saveFieldStatus',
    'methods'    => array('POST'),
));

########################################################################################################################
# Products
########################################################################################################################

$collection->create('api_products', array(
    'path'       => '/ticket_prods',
    'controller' => 'LegacyApiBundle:TicketFields:listProducts',
    'methods'    => array('GET'),
));

$collection->create('api_products_save', array(
    'path'       => '/ticket_prods',
    'controller' => 'LegacyApiBundle:TicketFields:saveProducts',
    'methods'    => array('POST'),
));

########################################################################################################################
# Ticket Categoriesw
########################################################################################################################

$collection->create('api_ticket_cats', array(
    'path'       => '/ticket_cats',
    'controller' => 'LegacyApiBundle:TicketFields:listCategories',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_cats_save', array(
    'path'       => '/ticket_cats',
    'controller' => 'LegacyApiBundle:TicketFields:saveCategories',
    'methods'    => array('POST'),
));

########################################################################################################################
# Ticket Workflows
########################################################################################################################

$collection->create('api_ticket_works', array(
    'path'       => '/ticket_works',
    'controller' => 'LegacyApiBundle:TicketFields:listWorkflows',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_works_save', array(
    'path'       => '/ticket_works',
    'controller' => 'LegacyApiBundle:TicketFields:saveWorkflows',
    'methods'    => array('POST'),
));

########################################################################################################################
# Ticket Priorities
########################################################################################################################

$collection->create('api_ticket_pris', array(
    'path'       => '/ticket_pris',
    'controller' => 'LegacyApiBundle:TicketFields:listPriorities',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_pris_save', array(
    'path'       => '/ticket_pris',
    'controller' => 'LegacyApiBundle:TicketFields:savePriorities',
    'methods'    => array('POST'),
));

########################################################################################################################
# Ticket Statuses
########################################################################################################################

$collection->create('api_ticket_statuses_stats', array(
    'path'       => '/ticket_statuses/stats',
    'controller' => 'LegacyApiBundle:TicketStatuses:getStats',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_statuses_archived', array(
    'path'       => '/ticket_statuses/archived',
    'controller' => 'LegacyApiBundle:TicketStatuses:getArchivedInfo',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_statuses_archived_savesettings', array(
    'path'       => '/ticket_statuses/archived/settings',
    'controller' => 'LegacyApiBundle:TicketStatuses:saveArchivedSettings',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_statuses_archived_resetsearch', array(
    'path'       => '/ticket_statuses/archived/reset-search-tables',
    'controller' => 'LegacyApiBundle:TicketStatuses:resetSearchTables',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_statuses_deleted', array(
    'path'       => '/ticket_statuses/deleted',
    'controller' => 'LegacyApiBundle:TicketStatuses:getDeletedInfo',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_statuses_deleted_purge', array(
    'path'       => '/ticket_statuses/deleted/purge',
    'controller' => 'LegacyApiBundle:TicketStatuses:purgeDeleted',
    'methods'    => array('DELETE'),
));

$collection->create('api_ticket_statuses_deleted_savesettings', array(
    'path'       => '/ticket_statuses/deleted/settings',
    'controller' => 'LegacyApiBundle:TicketStatuses:saveDeletedSettings',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_statuses_spam', array(
    'path'       => '/ticket_statuses/spam',
    'controller' => 'LegacyApiBundle:TicketStatuses:getSpamInfo',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_statuses_spam_purge', array(
    'path'       => '/ticket_statuses/spam/purge',
    'controller' => 'LegacyApiBundle:TicketStatuses:purgeSpam',
    'methods'    => array('DELETE'),
));

$collection->create('api_ticket_statuses_spam_savesettings', array(
    'path'       => '/ticket_statuses/spam/settings',
    'controller' => 'LegacyApiBundle:TicketStatuses:saveSpamSettings',
    'methods'    => array('POST'),
));

########################################################################################################################
# Ticket SLAs
########################################################################################################################

$collection->create('api_ticket_slas', array(
    'path'       => '/ticket_slas',
    'controller' => 'LegacyApiBundle:TicketSlas:list',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_slascreate', array(
    'path'       => '/ticket_slas',
    'controller' => 'LegacyApiBundle:TicketSlas:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_slasget', array(
    'path'       => '/ticket_slas/{id}',
    'controller' => 'LegacyApiBundle:TicketSlas:get',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_slasupdate', array(
    'path'       => '/ticket_slas/{id}',
    'controller' => 'LegacyApiBundle:TicketSlas:save',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_slasdelete', array(
    'path'       => '/ticket_slas/{id}',
    'controller' => 'LegacyApiBundle:TicketSlas:delete',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Ticket Urgencies
########################################################################################################################

$collection->create('api_ticket_urgencies', array(
    'path'       => '/ticket_urgencies',
    'controller' => 'LegacyApiBundle:TicketUrgencies:list',
    'methods'    => array('GET'),
));

########################################################################################################################
# Ticket Fields
########################################################################################################################

$collection->create('api_ticket_fields_get', array(
    'path'         => '/ticket_fields/{id}',
    'controller'   => 'LegacyApiBundle:TicketFields:getCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_ticket_fields_create', array(
    'path'       => '/ticket_fields',
    'controller' => 'LegacyApiBundle:TicketFields:saveCustomField',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_fields_save', array(
    'path'         => '/ticket_fields/{id}',
    'controller'   => 'LegacyApiBundle:TicketFields:saveCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_fields_delete', array(
    'path'         => '/ticket_fields/{id}',
    'controller'   => 'LegacyApiBundle:TicketFields:deleteCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_ticket_fields', array(
    'path'       => '/ticket_fields',
    'controller' => 'LegacyApiBundle:TicketFields:list',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_fields_setenabled', array(
    'path'       => '/ticket_fields/set-enabled/{field_id}/{is_enabled}',
    'controller' => 'LegacyApiBundle:TicketFields:toggleField',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_fields_convert', array(
    'path'         => '/ticket_fields/convert/{type}',
    'controller'   => 'LegacyApiBundle:TicketFields:convert',
    'methods'      => array('POST'),
    'requirements' => array('type' => 'categories|workflows|priorities|products'),
));

########################################################################################################################
# SMS Channel
########################################################################################################################

$collection->create(
    'api_channel_sms_accounts', array(
        'path'       => '/channel/sms/accounts',
        'controller' => 'LegacyApiBundle:ChannelSms:list',
        'methods'    => array('GET'),
    )
);

$collection->create(
    'api_channel_sms_account_get', array(
        'path'         => '/channel/sms/account/{id}',
        'requirements' => array('id' => '\d+'),
        'controller'   => 'LegacyApiBundle:ChannelSms:get',
        'methods'      => array('GET'),
    )
);

$collection->create(
    'api_channel_sms_account_delete', array(
        'path'         => '/channel/sms/account/{id}',
        'requirements' => array('id' => '\d+'),
        'controller'   => 'LegacyApiBundle:ChannelSms:delete',
        'methods'      => array('DELETE'),
    )
);

$collection->create(
    'api_channel_sms_account_save', array(
        'path'       => '/channel/sms/account/{id}',
        'controller' => 'LegacyApiBundle:ChannelSms:save',
        'methods'    => array('POST'),
    )
);

$collection->create(
    'api_channel_sms_account_create', array(
        'path'       => '/channel/sms/account',
        'controller' => 'LegacyApiBundle:ChannelSms:save',
        'methods'    => array('PUT'),
    )
);

$collection->create(
    'api_channel_sms_connect_provider', array(
        'path'       => '/channel/sms/connect_provider',
        'controller' => 'LegacyApiBundle:ChannelSms:connectProvider',
        'methods'    => array('POST'),
    )
);

$collection->create(
    'api_channel_sms_setup_and_test_twilio', array(
        'path'       => '/channel/sms/setup-and-test/twilio',
        'controller' => 'LegacyApiBundle:ChannelSms:setupAndTestTwilio',
        'methods'    => array('POST'),
    )
);

########################################################################################################################
# Facebook Channel
########################################################################################################################

$collection->create(
    'api_channel_facebook_pages', array(
        'path'       => '/channel/facebook/pages',
        'controller' => 'LegacyApiBundle:ChannelFacebook:list',
        'methods'    => array('GET'),
    )
);

$collection->create(
    'api_channel_facebook_pages_post', array(
        'path'       => '/channel/facebook/pages',
        'controller' => 'LegacyApiBundle:ChannelFacebook:create',
        'methods'    => array('POST'),
    )
);

$collection->create(
    'api_channel_facebook_page_get', array(
        'path'         => '/channel/facebook/page/{id}',
        'requirements' => array('id' => '\d+'),
        'controller'   => 'LegacyApiBundle:ChannelFacebook:get',
        'methods'      => array('GET'),
    )
);

$collection->create(
    'api_channel_facebook_page_delete', array(
        'path'         => '/channel/facebook/page/{id}',
        'requirements' => array('id' => '\d+'),
        'controller'   => 'LegacyApiBundle:ChannelFacebook:delete',
        'methods'      => array('DELETE'),
    )
);

$collection->create(
    'api_channel_facebook_page_save', array(
        'path'       => '/channel/facebook/page/{id}',
        'controller' => 'LegacyApiBundle:ChannelFacebook:save',
        'methods'    => array('POST'),
    )
);

########################################################################################################################
# Email Accounts
########################################################################################################################

$collection->create('api_emailaccounts', array(
    'path'       => '/email_accounts',
    'controller' => 'LegacyApiBundle:EmailAccounts:list',
    'methods'    => array('GET'),
));

$collection->create('api_emailaccounts_settings_get', array(
    'path'       => '/email_accounts/settings',
    'controller' => 'LegacyApiBundle:EmailAccounts:getSettings',
    'methods'    => array('GET'),
));

$collection->create('api_emailaccounts_settings_set', array(
    'path'       => '/email_accounts/settings',
    'controller' => 'LegacyApiBundle:EmailAccounts:setSettings',
    'methods'    => array('PUT'),
));

$collection->create('api_emailaccounts_create', array(
    'path'       => '/email_accounts',
    'controller' => 'LegacyApiBundle:EmailAccounts:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_emailaccounts_test', array(
    'path'       => '/email_accounts/test-account',
    'controller' => 'LegacyApiBundle:EmailAccounts:testAccount',
    'methods'    => array('POST'),
));

$collection->create('api_emailaccounts_testoutgoing', array(
    'path'       => '/email_accounts/test-outgoing-account',
    'controller' => 'LegacyApiBundle:EmailAccounts:testOutgoingAccount',
    'methods'    => array('POST'),
));

$collection->create('api_emailaccounts_get', array(
    'path'       => '/email_accounts/{id}',
    'controller' => 'LegacyApiBundle:EmailAccounts:get',
    'methods'    => array('GET'),
));

$collection->create('api_emailaccounts_remove', array(
    'path'       => '/email_accounts/{id}',
    'controller' => 'LegacyApiBundle:EmailAccounts:remove',
    'methods'    => array('DELETE'),
));

$collection->create('api_emailaccounts_save', array(
    'path'       => '/email_accounts/{id}',
    'controller' => 'LegacyApiBundle:EmailAccounts:save',
    'methods'    => array('POST'),
));

########################################################################################################################
# Email Status
########################################################################################################################

$collection->create('api_emailstatus_sourcelist', array(
    'path'       => '/email_status/sources',
    'controller' => 'LegacyApiBundle:EmailStatus:listSources',
    'methods'    => array('GET'),
));

$collection->create('api_emailstatus_sourcestats', array(
    'path'       => '/email_status/stats',
    'controller' => 'LegacyApiBundle:EmailStatus:sourcesStats',
    'methods'    => array('GET'),
));

$collection->create('api_emailstatus_source_get', array(
    'path'         => '/email_status/sources/{id}',
    'controller'   => 'LegacyApiBundle:EmailStatus:getSourceInfo',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_emailstatus_source_get_summary', array(
    'path'         => '/email_status/sources/{id}/summary',
    'controller'   => 'LegacyApiBundle:EmailStatus:getSourceSummary',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_emailstatus_source_get_rendered', array(
    'path'         => '/email_status/sources/{id}/rendered',
    'controller'   => 'LegacyApiBundle:EmailStatus:getSourceRendered',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_emailstatus_source_reprocess', array(
    'path'         => '/email_status/sources/{id}/reprocess',
    'controller'   => 'LegacyApiBundle:EmailStatus:reprocessEmailSource',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_emailstatus_source_massactions', array(
    'path'         => '/email_status/sources/mass-actions/{action}',
    'controller'   => 'LegacyApiBundle:EmailStatus:emailSourceMassActions',
    'requirements' => array('action' => '[a-z]+'),
    'methods'      => array('POST'),
));

$collection->create('api_emailstatus_source_delete', array(
    'path'         => '/email_status/sources/{id}',
    'controller'   => 'LegacyApiBundle:EmailStatus:deleteEmailSource',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_emailstatus_sendmaillist', array(
    'path'       => '/email_status/sendmail',
    'controller' => 'LegacyApiBundle:EmailStatus:listSendmail',
    'methods'    => array('GET'),
));

$collection->create('api_emailstatus_sendmail_massactions', array(
    'path'         => '/email_status/sendmail/mass-actions/{action}',
    'controller'   => 'LegacyApiBundle:EmailStatus:sendmailMassActions',
    'requirements' => array('action' => '[a-z]+'),
    'methods'      => array('POST'),
));

$collection->create('api_emailstatus_sendmail_delete', array(
    'path'         => '/email_status/sendmail/{id}',
    'controller'   => 'LegacyApiBundle:EmailStatus:deleteSendmail',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_emailstatus_sendmail_get_summary', array(
    'path'         => '/email_status/sendmail/{id}/summary',
    'controller'   => 'LegacyApiBundle:EmailStatus:getSendmailSummary',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_emailstatus_sendmail_get_rendered', array(
    'path'         => '/email_status/sendmail/{id}/rendered',
    'controller'   => 'LegacyApiBundle:EmailStatus:getSendmailRendered',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_emailstatus_sendmail_resend', array(
    'path'         => '/email_status/sendmail/{id}/resend',
    'controller'   => 'LegacyApiBundle:EmailStatus:resendSendmail',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_emailstatus_sendmail_get', array(
    'path'         => '/email_status/sendmail/{id}',
    'controller'   => 'LegacyApiBundle:EmailStatus:getSendmailInfo',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

########################################################################################################################
# Audit Log
########################################################################################################################

$collection->create('api_auditlog_list', array(
    'path'       => '/audit_log',
    'controller' => 'LegacyApiBundle:AuditLog:list',
    'methods'    => array('GET'),
));

$collection->create('api_auditlog_detail', array(
    'path'         => '/audit_log/{id}',
    'controller'   => 'LegacyApiBundle:AuditLog:getDetail',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

########################################################################################################################
# Ticket Triggers
########################################################################################################################

$collection->create('api_ticket_triggers_getappevents', array(
    'path'       => '/ticket_triggers/app-events/{type}',
    'controller' => 'LegacyApiBundle:TicketTriggers:getAppEvents',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_triggers_getcustomactions', array(
    'path'       => '/ticket_triggers/get-custom-actions',
    'controller' => 'LegacyApiBundle:TicketTriggers:getCustomActions',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_triggers_getspecial', array(
    'path'         => '/ticket_triggers/{special_type}/{id}',
    'controller'   => 'LegacyApiBundle:TicketTriggers:get',
    'requirements' => array('special_type' => '(departments|departments_changed|email_accounts|satisfaction)', 'id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_ticket_triggers_updatespecial', array(
    'path'         => '/ticket_triggers/{special_type}/{id}',
    'controller'   => 'LegacyApiBundle:TicketTriggers:save',
    'requirements' => array('special_type' => '(departments|departments_changed|email_accounts|satisfaction)', 'id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers', array(
    'path'         => '/ticket_triggers/{type}',
    'controller'   => 'LegacyApiBundle:TicketTriggers:list',
    'requirements' => array('type' => '(all|newticket|newreply|update)'),
    'methods'      => array('GET'),
));

$collection->create('api_ticket_triggers_updateorder', array(
    'path'       => '/ticket_triggers/run_order',
    'controller' => 'LegacyApiBundle:TicketTriggers:saveRunOrder',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_triggers_create', array(
    'path'       => '/ticket_triggers',
    'controller' => 'LegacyApiBundle:TicketTriggers:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_triggers_get', array(
    'path'         => '/ticket_triggers/{id}',
    'controller'   => 'LegacyApiBundle:TicketTriggers:get',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_ticket_triggers_update', array(
    'path'         => '/ticket_triggers/{id}',
    'controller'   => 'LegacyApiBundle:TicketTriggers:save',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_delete', array(
    'path'         => '/ticket_triggers/{id}',
    'controller'   => 'LegacyApiBundle:TicketTriggers:delete',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_ticket_triggers_enabletriggergroup', array(
    'path'         => '/ticket_triggers/{special_type}/enable',
    'defaults'     => array('is_enabled' => true),
    'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTriggerGroup',
    'requirements' => array('special_type' => '(departments|departments_changed|email_accounts|satisfaction)', 'id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_disabletriggergroup', array(
    'path'         => '/ticket_triggers/{special_type}/disable',
    'defaults'     => array('is_enabled' => false),
    'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTriggerGroup',
    'requirements' => array('special_type' => '(departments|departments_changed|email_accounts|satisfaction)', 'id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_enabletrigger', array(
    'path'         => '/ticket_triggers/{id}/enable',
    'defaults'     => array('is_enabled' => true),
    'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTrigger',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_disabletrigger', array(
    'path'         => '/ticket_triggers/{id}/disable',
    'defaults'     => array('is_enabled' => false),
    'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTrigger',
    'requirements' => array('id' => '\d+'),
    'methods'      => array('POST'),
));

########################################################################################################################
# Ticket Escalations
########################################################################################################################

$collection->create('api_ticket_escalations', array(
    'path'       => '/ticket_escalations',
    'controller' => 'LegacyApiBundle:TicketEscalations:list',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_escalations_updateorder', array(
    'path'       => '/ticket_escalations/run_order',
    'controller' => 'LegacyApiBundle:TicketEscalations:saveRunOrder',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_escalations_create', array(
    'path'       => '/ticket_escalations',
    'controller' => 'LegacyApiBundle:TicketEscalations:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_escalations_get', array(
    'path'       => '/ticket_escalations/{id}',
    'controller' => 'LegacyApiBundle:TicketEscalations:get',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_escalations_update', array(
    'path'       => '/ticket_escalations/{id}',
    'controller' => 'LegacyApiBundle:TicketEscalations:save',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_escalations_getspecial', array(
    'path'         => '/ticket_escalations/{special_type}/{id}',
    'controller'   => 'LegacyApiBundle:TicketEscalations:get',
    'requirements' => array('special_type' => '(satisfaction|statuses)', 'id' => '\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_ticket_escalations_updatespecial', array(
    'path'         => '/ticket_escalations/{special_type}/{id}',
    'controller'   => 'LegacyApiBundle:TicketEscalations:save',
    'requirements' => array('special_type' => '(satisfaction|statuses)', 'id' => '\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_ticket_escalations_delete', array(
    'path'       => '/ticket_escalations/{id}',
    'controller' => 'LegacyApiBundle:TicketEscalations:delete',
    'methods'    => array('DELETE'),
));

$collection->create('api_ticket_escalations_enable', array(
    'path'       => '/ticket_escalations/{id}/enable',
    'defaults'   => array('is_enabled' => true),
    'controller' => 'LegacyApiBundle:TicketEscalations:toggleEscalation',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_escalations_disable', array(
    'path'       => '/ticket_escalations/{id}/disable',
    'defaults'   => array('is_enabled' => false),
    'controller' => 'LegacyApiBundle:TicketEscalations:toggleEscalation',
    'methods'    => array('POST'),
));

########################################################################################################################
# Ticket Filters
########################################################################################################################

$collection->create('api_ticket_filters', array(
    'path'       => '/ticket_filters',
    'controller' => 'LegacyApiBundle:TicketFilters:list',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_filters_create', array(
    'path'       => '/ticket_filters',
    'defaults'   => array('id' => '0'),
    'controller' => 'LegacyApiBundle:TicketFilters:save',
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_filters_savedisplayorder', array(
    'path'       => '/ticket_filters/display_order',
    'controller' => 'LegacyApiBundle:TicketFilters:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_filters_get', array(
    'path'         => '/ticket_filters/{id}',
    'requirements' => array('id' => '\\d+'),
    'controller'   => 'LegacyApiBundle:TicketFilters:get',
    'methods'      => array('GET'),
));

$collection->create('api_ticket_filters_save', array(
    'path'         => '/ticket_filters/{id}',
    'requirements' => array('id' => '\\d+'),
    'controller'   => 'LegacyApiBundle:TicketFilters:save',
    'methods'      => array('POST'),
));

$collection->create('api_ticket_filters_delete', array(
    'path'         => '/ticket_filters/{id}',
    'requirements' => array('id' => '\\d+'),
    'controller'   => 'LegacyApiBundle:TicketFilters:remove',
    'methods'      => array('DELETE'),
));

########################################################################################################################
# Ticket Macros
########################################################################################################################

$collection->create('api_ticket_macros', array(
    'path'       => '/ticket_macros',
    'controller' => 'LegacyApiBundle:TicketMacros:list',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_macros_create', array(
    'path'       => '/ticket_macros',
    'defaults'   => array('id' => '0'),
    'controller' => 'LegacyApiBundle:TicketMacros:save',
    'methods'    => array('PUT'),
));

$collection->create('api_ticket_macros_get', array(
    'path'       => '/ticket_macros/{id}',
    'controller' => 'LegacyApiBundle:TicketMacros:get',
    'methods'    => array('GET'),
));

$collection->create('api_ticket_macros_save', array(
    'path'       => '/ticket_macros/{id}',
    'controller' => 'LegacyApiBundle:TicketMacros:save',
    'methods'    => array('POST'),
));

$collection->create('api_ticket_macros_delete', array(
    'path'       => '/ticket_macros/{id}',
    'controller' => 'LegacyApiBundle:TicketMacros:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Feedback Statuses
########################################################################################################################

$collection->create('api_feedback_statuses', array(
    'path'       => '/feedback_statuses',
    'controller' => 'LegacyApiBundle:FeedbackStatuses:list',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_statuses_order', array(
    'path'       => '/feedback_statuses/display_order',
    'controller' => 'LegacyApiBundle:FeedbackStatuses:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_statuses_get', array(
    'path'       => '/feedback_statuses/{id}',
    'controller' => 'LegacyApiBundle:FeedbackStatuses:get',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_statuses_create', array(
    'path'       => '/feedback_statuses',
    'controller' => 'LegacyApiBundle:FeedbackStatuses:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_feedback_statuses_save', array(
    'path'       => '/feedback_statuses/{id}',
    'controller' => 'LegacyApiBundle:FeedbackStatuses:save',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_statuses_delete', array(
    'path'       => '/feedback_statuses/{id}',
    'controller' => 'LegacyApiBundle:FeedbackStatuses:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Feedback Types
########################################################################################################################

$collection->create('api_feedback_types', array(
    'path'       => '/feedback_types',
    'controller' => 'LegacyApiBundle:FeedbackTypes:list',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_types_order', array(
    'path'       => '/feedback_types/display_order',
    'controller' => 'LegacyApiBundle:FeedbackTypes:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_types_get', array(
    'path'       => '/feedback_types/{id}',
    'controller' => 'LegacyApiBundle:FeedbackTypes:get',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_types_create', array(
    'path'       => '/feedback_types',
    'controller' => 'LegacyApiBundle:FeedbackTypes:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_feedback_types_save', array(
    'path'       => '/feedback_types/{id}',
    'controller' => 'LegacyApiBundle:FeedbackTypes:save',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_types_delete', array(
    'path'       => '/feedback_types/{id}',
    'controller' => 'LegacyApiBundle:FeedbackTypes:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Feedback Categories
########################################################################################################################

$collection->create('api_feedback_categories', array(
    'path'       => '/feedback_categories',
    'controller' => 'LegacyApiBundle:FeedbackCategories:list',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_categories_order', array(
    'path'       => '/feedback_categories/display_order',
    'controller' => 'LegacyApiBundle:FeedbackCategories:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_categories_get', array(
    'path'       => '/feedback_categories/{id}',
    'controller' => 'LegacyApiBundle:FeedbackCategories:get',
    'methods'    => array('GET'),
));

$collection->create('api_feedback_categories_create', array(
    'path'       => '/feedback_categories',
    'controller' => 'LegacyApiBundle:FeedbackCategories:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_feedback_categories_save', array(
    'path'       => '/feedback_categories/{id}',
    'controller' => 'LegacyApiBundle:FeedbackCategories:save',
    'methods'    => array('POST'),
));

$collection->create('api_feedback_categories_delete', array(
    'path'       => '/feedback_categories/{id}',
    'controller' => 'LegacyApiBundle:FeedbackCategories:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Twitter Setup
########################################################################################################################

$collection->create('api_twitter_setup', array(
    'path'       => '/twitter_setup',
    'controller' => 'LegacyApiBundle:TwitterSetup:twitterSetup',
    'methods'    => array('GET'),
));

$collection->create('api_twitter_setup_save', array(
    'path'       => '/twitter_setup',
    'controller' => 'LegacyApiBundle:TwitterSetup:save',
    'methods'    => array('POST'),
));

########################################################################################################################
# Twitter Accounts
########################################################################################################################

$collection->create('api_twitter_accounts', array(
    'path'       => '/twitter_accounts',
    'controller' => 'LegacyApiBundle:TwitterAccounts:list',
    'methods'    => array('GET'),
));

$collection->create('api_twitter_accounts_get', array(
    'path'       => '/twitter_accounts/{id}',
    'controller' => 'LegacyApiBundle:TwitterAccounts:get',
    'methods'    => array('GET'),
));

$collection->create('api_twitter_accounts_create', array(
    'path'       => '/twitter_accounts',
    'controller' => 'LegacyApiBundle:TwitterAccounts:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_twitter_accounts_save', array(
    'path'       => '/twitter_accounts/{id}',
    'controller' => 'LegacyApiBundle:TwitterAccounts:save',
    'methods'    => array('POST'),
));

$collection->create('api_twitter_accounts_delete', array(
    'path'       => '/twitter_accounts/{id}',
    'controller' => 'LegacyApiBundle:TwitterAccounts:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Server Requirements
########################################################################################################################

$collection->create('api_server_reqs', array(
    'path'       => '/server_reqs',
    'controller' => 'LegacyApiBundle:Server:getServerReqs',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server PHP Info
########################################################################################################################

$collection->create('api_server_php_info', array(
    'path'       => '/server_php_info',
    'controller' => 'LegacyApiBundle:Server:getPhpInfo',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server Mysql Info
########################################################################################################################

$collection->create('api_server_mysql_info', array(
    'path'       => '/server_mysql_info',
    'controller' => 'LegacyApiBundle:Server:getMysqlInfo',
    'methods'    => array('GET'),
));

$collection->create('api_server_mysql_info_schemadiff', array(
    'path'       => '/server_mysql_info/schema-diff',
    'controller' => 'LegacyApiBundle:Server:getMysqlSchemaDiff',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server Mysql Status
########################################################################################################################

$collection->create('api_server_mysql_status', array(
    'path'       => '/server_mysql_status',
    'controller' => 'LegacyApiBundle:Server:getMysqlStatus',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server Mysql Sort Order
########################################################################################################################

$collection->create('api_server_mysql_sort_order', array(
    'path'       => '/server_mysql_sort_order',
    'controller' => 'LegacyApiBundle:Server:getMysqlSortOrder',
    'methods'    => array('GET'),
));

$collection->create('api_server_mysql_sort_order_save', array(
    'path'       => '/server_mysql_sort_order',
    'controller' => 'LegacyApiBundle:Server:saveMysqlSortOrder',
    'methods'    => array('POST'),
));

$collection->create('api_server_mysql_sort_order_status', array(
    'path'       => '/server_mysql_sort_order_status',
    'controller' => 'LegacyApiBundle:Server:getMysqlSortOrderStatus',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server
########################################################################################################################

$collection->create('api_server_cron_status', array(
    'path'       => '/server/cron-status',
    'controller' => 'LegacyApiBundle:Server:cronStatus',
    'methods'    => array('GET'),
));

$collection->create('api_server_error_status', array(
    'path'       => '/server/error-status',
    'controller' => 'LegacyApiBundle:Server:errorStatus',
    'methods'    => array('GET'),
));

$collection->create('api_server_apc_status', array(
    'path'       => '/server/apc-status',
    'controller' => 'LegacyApiBundle:Server:apcStatus',
    'methods'    => array('GET'),
));

$collection->create('api_server_autoupdate_begin', array(
    'path'       => '/server/updates/auto',
    'controller' => 'LegacyApiBundle:Server:beginAutomaticUpdate',
    'methods'    => array('PUT'),
));

$collection->create('api_server_autoupdate_abort', array(
    'path'       => '/server/updates/auto',
    'controller' => 'LegacyApiBundle:Server:abortAutomaticUpdate',
    'methods'    => array('DELETE'),
));

$collection->create('api_server_autoupdate_status', array(
    'path'       => '/server/updates/auto',
    'controller' => 'LegacyApiBundle:Server:getAutomaticUpdateStatus',
    'methods'    => array('GET'),
));

$collection->create('api_server_enc_status', array(
    'path'       => '/server/encryption/status',
    'controller' => 'LegacyApiBundle:Server:encryptionStatus',
    'methods'    => array('GET'),
));

$collection->create('api_server_enc_enable', array(
    'path'       => '/server/encryption/enable',
    'controller' => 'LegacyApiBundle:Server:enableEncryption',
    'defaults'   => array(),
    'methods'    => array('POST'),
));

$collection->create('api_server_enc_disable', array(
    'path'       => '/server/encryption/disable',
    'controller' => 'LegacyApiBundle:Server:disableEncryption',
    'defaults'   => array(),
    'methods'    => array('POST'),
));

########################################################################################################################
# Server Error Logs
########################################################################################################################

$collection->create('api_server_error_logs', array(
    'path'       => '/server_error_logs',
    'controller' => 'LegacyApiBundle:Server:listErrorLogs',
    'methods'    => array('GET'),
));

$collection->create('api_server_error_logs_get', array(
    'path'       => '/server_error_logs/{id}',
    'controller' => 'LegacyApiBundle:Server:getErrorLogs',
    'methods'    => array('GET'),
));

$collection->create('api_server_error_logs_delete', array(
    'path'       => '/server_error_logs',
    'controller' => 'LegacyApiBundle:Server:removeErrorLogs',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Server Task Queue
########################################################################################################################

$collection->create('api_server_task_queue', array(
    'path'       => '/server_task_queue',
    'controller' => 'LegacyApiBundle:Server:getTaskQueue',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server Cron
########################################################################################################################

$collection->create('api_server_cron', array(
    'path'       => '/server_cron',
    'controller' => 'LegacyApiBundle:Server:listCron',
    'methods'    => array('GET'),
));

$collection->create('api_server_cron_logs', array(
    'path'       => '/server_cron/logs',
    'controller' => 'LegacyApiBundle:Server:logsCron',
    'methods'    => array('GET'),
));

$collection->create('api_server_cron_logs_delete', array(
    'path'       => '/server_cron/logs',
    'controller' => 'LegacyApiBundle:Server:removeCron',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Server File Uploads
########################################################################################################################

$collection->create('api_server_file_uploads', array(
    'path'       => '/server_file_uploads',
    'controller' => 'LegacyApiBundle:Server:getFileUploads',
    'methods'    => array('GET'),
));

$collection->create('api_server_test_file_uploads', array(
    'path'       => '/server_file_uploads',
    'controller' => 'LegacyApiBundle:Server:testFileUpload',
    'methods'    => array('POST'),
));

$collection->create('api_server_switch_file_uploads_storage', array(
    'path'       => '/server_file_uploads/switch',
    'controller' => 'LegacyApiBundle:Server:switchFileStorage',
    'methods'    => array('POST'),
));

$collection->create('api_server_switch_file_uploads_storage_status', array(
    'path'       => '/server_file_uploads/switch_status',
    'controller' => 'LegacyApiBundle:Server:switchFileStorageStatus',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server File Integrity
########################################################################################################################

$collection->create('api_server_file_check', array(
    'path'       => '/server_file_check',
    'controller' => 'LegacyApiBundle:Server:listFileCheck',
    'methods'    => array('GET'),
));

$collection->create('api_server_file_check_get', array(
    'path'       => '/server_file_check/{id}',
    'controller' => 'LegacyApiBundle:Server:getFileCheck',
    'methods'    => array('GET'),
));

########################################################################################################################
# Server Report File
########################################################################################################################

$collection->create('api_server_report_file_get', array(
    'path'       => '/server_report_file',
    'controller' => 'LegacyApiBundle:Server:getReportFile',
    'methods'    => array('GET'),
));

$collection->create('api_server_report_file_check_save', array(
    'path'       => '/server_report_file/file_check_results',
    'controller' => 'LegacyApiBundle:Server:saveFileCheckResults',
    'methods'    => array('POST'),
));

########################################################################################################################
# Chat Fields
########################################################################################################################

$collection->create('api_chat_fields_get', array(
    'path'         => '/chat_fields/{id}',
    'controller'   => 'LegacyApiBundle:ChatFields:getCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_chat_fields_create', array(
    'path'       => '/chat_fields',
    'controller' => 'LegacyApiBundle:ChatFields:saveCustomField',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_chat_fields_delete', array(
    'path'         => '/chat_fields/{id}',
    'controller'   => 'LegacyApiBundle:ChatFields:deleteCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_chat_fields_save', array(
    'path'         => '/chat_fields/{id}',
    'controller'   => 'LegacyApiBundle:ChatFields:saveCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_chat_fields', array(
    'path'       => '/chat_fields',
    'controller' => 'LegacyApiBundle:ChatFields:list',
    'methods'    => array('GET'),
));

$collection->create('api_chat_fields_setenabled', array(
    'path'       => '/chat_fields/set-enabled/{field_id}/{is_enabled}',
    'controller' => 'LegacyApiBundle:ChatFields:toggleField',
    'methods'    => array('POST'),
));

$collection->create('api_chat_fields_update_order', array(
    'path'       => '/chat_fields/display-order',
    'controller' => 'LegacyApiBundle:ChatFields:saveDisplayOrder',
    'methods'    => array('POST'),
));

########################################################################################################################
# Chat Setup
########################################################################################################################

$collection->create('api_chat_setup', array(
    'path'       => '/chat_setup',
    'controller' => 'LegacyApiBundle:ChatSetup:chatSetup',
    'methods'    => array('GET'),
));

$collection->create('api_chat_setup_toggle', array(
    'path'       => '/chat_setup/toggle_chat/{is_enabled}',
    'controller' => 'LegacyApiBundle:ChatSetup:toggleChat',
    'methods'    => array('POST'),
));

########################################################################################################################
# Chat Departments
########################################################################################################################

$collection->create('api_chat_deps', array(
    'path'       => '/chat_deps',
    'controller' => 'LegacyApiBundle:ChatDeps:list',
    'methods'    => array('GET'),
));

$collection->create('api_chat_deps_create', array(
    'path'       => '/chat_deps',
    'controller' => 'LegacyApiBundle:ChatDeps:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_chat_deps_order', array(
    'path'       => '/chat_deps/display_order',
    'controller' => 'LegacyApiBundle:ChatDeps:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create('api_chat_deps_get', array(
    'path'       => '/chat_deps/{id}',
    'controller' => 'LegacyApiBundle:ChatDeps:get',
    'methods'    => array('GET'),
));

$collection->create('api_chat_deps_save', array(
    'path'       => '/chat_deps/{id}',
    'controller' => 'LegacyApiBundle:ChatDeps:save',
    'methods'    => array('POST'),
));

$collection->create('api_chat_deps_remove', array(
    'path'       => '/chat_deps/{id}',
    'controller' => 'LegacyApiBundle:ChatDeps:remove',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Api Keys
########################################################################################################################

$collection->create('api_api_keys', array(
    'path'       => '/api_keys',
    'controller' => 'LegacyApiBundle:ApiKeys:list',
    'methods'    => array('GET'),
));

$collection->create('api_api_keys_create', array(
    'path'       => '/api_keys',
    'controller' => 'LegacyApiBundle:ApiKeys:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_api_keys_get', array(
    'path'       => '/api_keys/{id}',
    'controller' => 'LegacyApiBundle:ApiKeys:get',
    'methods'    => array('GET'),
));

$collection->create('api_api_keys_save', array(
    'path'       => '/api_keys/{id}',
    'controller' => 'LegacyApiBundle:ApiKeys:save',
    'methods'    => array('POST', 'PUT'),
));

$collection->create('api_api_keys_delete', array(
    'path'       => '/api_keys/{id}',
    'controller' => 'LegacyApiBundle:ApiKeys:remove',
    'methods'    => array('DELETE'),
));

$collection->create('api_api_keys_logs', array(
    'path'       => '/api_keys/{id}/logs',
    'controller' => 'LegacyApiBundle:ApiKeys:getLogs',
    'methods'    => array('GET'),
));

$collection->create('api_api_keys_regenerate', array(
    'path'       => '/api_keys/regenerate/{id}',
    'controller' => 'LegacyApiBundle:ApiKeys:regenerate',
    'methods'    => array('POST'),
));

$collection->create('api_api_keys_replay_log_entry', array(
    'path'       => '/api_keys/replay/{logEntryId}',
    'controller' => 'LegacyApiBundle:ApiKeys:replayLogEntry',
    'methods'    => array('GET'),
));

########################################################################################################################
# Tasks
########################################################################################################################

$collection->create('api_tasks_settings_get', array(
    'path'       => '/tasks/settings',
    'controller' => 'LegacyApiBundle:Tasks:settings',
    'methods'    => array('GET'),
));

$collection->create('api_tasks_settings_set', array(
    'path'       => '/tasks/settings',
    'controller' => 'LegacyApiBundle:Tasks:updateSettings',
    'methods'    => array('PUT'),
));

########################################################################################################################
# Problems
########################################################################################################################

$collection->create('api_problems_settings_get', array(
    'path'       => '/problems/settings',
    'controller' => 'LegacyApiBundle:Problems:settings',
    'methods'    => array('GET'),
));

$collection->create('api_problems_settings_set', array(
    'path'       => '/problems/settings',
    'controller' => 'LegacyApiBundle:Problems:updateSettings',
    'methods'    => array('PUT'),
));
########################################################################################################################
# CRM User Fields
########################################################################################################################

$collection->create('api_user_fields_get', array(
    'path'         => '/user_fields/{id}',
    'controller'   => 'LegacyApiBundle:UserFields:getCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_user_fields_create', array(
    'path'       => '/user_fields',
    'controller' => 'LegacyApiBundle:UserFields:saveCustomField',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_user_fields_save', array(
    'path'         => '/user_fields/{id}',
    'controller'   => 'LegacyApiBundle:UserFields:saveCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_user_fields_delete', array(
    'path'         => '/user_fields/{id}',
    'controller'   => 'LegacyApiBundle:UserFields:deleteCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_user_fields', array(
    'path'       => '/user_fields',
    'controller' => 'LegacyApiBundle:UserFields:list',
    'methods'    => array('GET'),
));

$collection->create('api_user_fields_setenabled', array(
    'path'       => '/user_fields/set-enabled/{field_id}/{is_enabled}',
    'controller' => 'LegacyApiBundle:UserFields:toggleField',
    'methods'    => array('POST'),
));

$collection->create('api_user_fields_update_order', array(
    'path'       => '/user_fields/display-order',
    'controller' => 'LegacyApiBundle:UserFields:saveDisplayOrder',
    'methods'    => array('POST'),
));

########################################################################################################################
# CRM New Custom Fields
########################################################################################################################

$collection->create('api_custom_fields', array(
    'path'       => '/custom_fields',
    'controller' => 'LegacyApiBundle:CustomFields:list',
    'methods'    => array('GET'),
));

$collection->create(
    'api_common_custom_fields_get',
    array(
        'path'         => '/custom_fields/{objectType}/{objectId}',
        'controller'   => 'LegacyApiBundle:CustomFields:getCommonFields',
        'requirements' => array(
            'objectType' => implode(
                '|',
                array_keys(\Application\LegacyApiBundle\Controller\CustomFieldsController::$allowed_common)
            ),
            'id' => '\\d+',
        ),
        'methods' => array('GET'),
    )
);

$collection->create(
    'api_common_custom_fields_set',
    array(
        'path'         => '/custom_fields/{objectType}/{objectId}',
        'controller'   => 'LegacyApiBundle:CustomFields:setCommonField',
        'requirements' => array(
            'objectType' => implode(
                '|',
                array_keys(\Application\LegacyApiBundle\Controller\CustomFieldsController::$allowed_common)
            ),
            'id' => '\\d+',
        ),
        'methods' => array('POST'),
    )
);

$collection->create('api_custom_fields_children', array(
    'path'         => '/custom_fields/{id}/children',
    'controller'   => 'LegacyApiBundle:CustomFields:children',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_custom_fields_children', array(
    'path'         => '/custom_fields/{id}/children',
    'controller'   => 'LegacyApiBundle:CustomFields:addChild',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_custom_fields_get', array(
    'path'         => '/custom_fields/{id}',
    'controller'   => 'LegacyApiBundle:CustomFields:get',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_custom_fields_save', array(
    'path'         => '/custom_fields/{id}',
    'controller'   => 'LegacyApiBundle:CustomFields:save',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('id' => 0),
    'methods'      => array('PUT', 'POST'),
));

$collection->create('api_custom_fields_delete', array(
    'path'         => '/custom_fields/{id}',
    'controller'   => 'LegacyApiBundle:CustomFields:delete',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_custom_fields_update_order', array(
    'path'       => '/custom_fields/display-order',
    'controller' => 'LegacyApiBundle:CustomFields:saveDisplayOrder',
    'methods'    => array('POST'),
));

$collection->create(
    'api_custom_fields_delete_option',
    array(
        'path'       => '/custom_fields/option',
        'controller' => 'LegacyApiBundle:CustomFields:deleteOption',
        'methods'    => array('DELETE'),
    )
);

########################################################################################################################
# CRM Organization Fields
########################################################################################################################

$collection->create('api_org_fields_get', array(
    'path'         => '/org_fields/{id}',
    'controller'   => 'LegacyApiBundle:OrgFields:getCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_org_fields_create', array(
    'path'       => '/org_fields',
    'controller' => 'LegacyApiBundle:OrgFields:saveCustomField',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_org_fields_save', array(
    'path'         => '/org_fields/{id}',
    'controller'   => 'LegacyApiBundle:OrgFields:saveCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_org_fields_delete', array(
    'path'         => '/org_fields/{id}',
    'controller'   => 'LegacyApiBundle:OrgFields:deleteCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_org_fields', array(
    'path'       => '/org_fields',
    'controller' => 'LegacyApiBundle:OrgFields:list',
    'methods'    => array('GET'),
));

$collection->create('api_org_fields_setenabled', array(
    'path'       => '/org_fields/set-enabled/{field_id}/{is_enabled}',
    'controller' => 'LegacyApiBundle:OrgFields:toggleField',
    'methods'    => array('POST'),
));

$collection->create('api_org_fields_update_order', array(
    'path'       => '/org_fields/display-order',
    'controller' => 'LegacyApiBundle:OrgFields:saveDisplayOrder',
    'methods'    => array('POST'),
));

########################################################################################################################
# CRM Banning
########################################################################################################################

$collection->create('api_banning', array(
    'path'       => '/banning',
    'controller' => 'LegacyApiBundle:Banning:list',
    'methods'    => array('GET'),
));

$collection->create('api_banning_email_export', array(
    'path'       => '/banning/export_emails',
    'controller' => 'LegacyApiBundle:Banning:exportEmails',
    'methods'    => array('GET'),
));

$collection->create('api_banning_email_import', array(
    'path'       => '/banning/import_emails',
    'controller' => 'LegacyApiBundle:Banning:importEmails',
    'methods'    => array('POST'),
));

$collection->create('api_banning_ip_create', array(
    'path'       => '/banning_ip',
    'controller' => 'LegacyApiBundle:Banning:saveIp',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_banning_email_create', array(
    'path'       => '/banning_email',
    'controller' => 'LegacyApiBundle:Banning:saveEmail',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_banning_ip_get', array(
    'path'       => '/banning_ip/{id}',
    'controller' => 'LegacyApiBundle:Banning:getIp',
    'methods'    => array('GET'),
));

$collection->create('api_banning_email_get', array(
    'path'       => '/banning_email/{id}',
    'controller' => 'LegacyApiBundle:Banning:getEmail',
    'methods'    => array('GET'),
));

$collection->create('api_banning_ip_save', array(
    'path'       => '/banning_ip/{id}',
    'controller' => 'LegacyApiBundle:Banning:saveIp',
    'methods'    => array('POST'),
));

$collection->create('api_banning_email_save', array(
    'path'       => '/banning_email/{id}',
    'controller' => 'LegacyApiBundle:Banning:saveEmail',
    'methods'    => array('POST'),
));

$collection->create('api_banning_ip_remove_all', array(
    'path'       => '/banning_ip',
    'controller' => 'LegacyApiBundle:Banning:removeIp',
    'methods'    => array('DELETE'),
    'defaults'   => array('id' => null),
));

$collection->create('api_banning_email_remove_all', array(
    'path'       => '/banning_email',
    'controller' => 'LegacyApiBundle:Banning:removeEmail',
    'methods'    => array('DELETE'),
    'defaults'   => array('id' => null),
));

$collection->create('api_banning_ip_remove', array(
    'path'       => '/banning_ip/{id}',
    'controller' => 'LegacyApiBundle:Banning:removeIp',
    'methods'    => array('DELETE'),
));

$collection->create('api_banning_email_remove', array(
    'path'       => '/banning_email/{id}',
    'controller' => 'LegacyApiBundle:Banning:removeEmail',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# CRM User Groups
########################################################################################################################

$collection->create('api_user_groups_list', array(
    'path'       => '/user_groups',
    'controller' => 'LegacyApiBundle:Usergroups:list',
    'defaults'   => array('type' => 'user'),
    'methods'    => array('GET'),
));

$collection->create('api_usergroups_non_sys_list', array(
    'path'       => '/non_sys_usergroups',
    'controller' => 'LegacyApiBundle:Usergroups:list',
    'defaults'   => array('type' => 'non_sys_user'),
    'methods'    => array('GET'),
));

$collection->create('api_user_groups_get', array(
    'path'         => '/user_groups/{id}',
    'controller'   => 'LegacyApiBundle:Usergroups:get',
    'requirements' => array('id' => '(\\d+|[a-z0-9_\.\-]+)'),
    'methods'      => array('GET'),
));

$collection->create('api_user_groups_delete', array(
    'path'         => '/user_groups/{id}',
    'controller'   => 'LegacyApiBundle:Usergroups:delete',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_user_groups_create', array(
    'path'       => '/user_groups',
    'controller' => 'LegacyApiBundle:Usergroups:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_user_groups_save', array(
    'path'         => '/user_groups/{id}',
    'controller'   => 'LegacyApiBundle:Usergroups:save',
    'requirements' => array('id' => '(\\d+|[a-z0-9_\.\-]+)'),
    'methods'      => array('POST'),
));

########################################################################################################################
# CRM Import CSV
########################################################################################################################

$collection->create('api_import_csv_upload', array(
    'path'       => '/import_csv_upload',
    'controller' => 'LegacyApiBundle:CsvUpload:upload',
    'methods'    => array('POST'),
));

$collection->create('api_import_csv_import', array(
    'path'       => '/import_csv_import',
    'controller' => 'LegacyApiBundle:CsvUpload:import',
    'methods'    => array('POST'),
));

$collection->create('api_import_csv_status', array(
    'path'       => '/import_csv_status',
    'controller' => 'LegacyApiBundle:CsvUpload:status',
    'methods'    => array('GET'),
));

$collection->create('api_import_csv_logs', array(
    'path'       => '/import_csv_logs',
    'controller' => 'LegacyApiBundle:CsvUpload:logs',
    'methods'    => array('GET'),
));

$collection->create('api_import_csv_clean', array(
    'path'       => '/import_csv_clean',
    'controller' => 'LegacyApiBundle:CsvUpload:clean',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# CRM Export CSV
########################################################################################################################

$collection->create('api_export_csv_start', array(
    'path'       => '/export/start',
    'controller' => 'LegacyApiBundle:CsvExport:start',
    'methods'    => array('POST'),
));

$collection->create('api_export_csv_stop', array(
    'path'       => '/export/stop',
    'controller' => 'LegacyApiBundle:CsvExport:stop',
    'methods'    => array('POST'),
));

$collection->create('api_export_csv_status', array(
    'path'       => '/export/status',
    'controller' => 'LegacyApiBundle:CsvExport:status',
    'methods'    => array('GET'),
));

$collection->create('api_export_list_files', array(
    'path'       => '/export/list',
    'controller' => 'LegacyApiBundle:CsvExport:list',
    'methods'    => array('GET'),
));

########################################################################################################################
# CRM User Rules
########################################################################################################################

$collection->create('api_user_rules', array(
    'path'       => '/user_rules',
    'controller' => 'LegacyApiBundle:UserRules:list',
    'methods'    => array('GET'),
));

$collection->create('api_user_rules_create', array(
    'path'       => '/user_rules',
    'controller' => 'LegacyApiBundle:UserRules:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_user_rules_get', array(
    'path'       => '/user_rules/{id}',
    'controller' => 'LegacyApiBundle:UserRules:get',
    'methods'    => array('GET'),
));

$collection->create('api_user_rules_save', array(
    'path'       => '/user_rules/{id}',
    'controller' => 'LegacyApiBundle:UserRules:save',
    'methods'    => array('POST'),
));

$collection->create('api_user_rules_delete', array(
    'path'       => '/user_rules/{id}',
    'controller' => 'LegacyApiBundle:UserRules:remove',
    'methods'    => array('DELETE'),
));

$collection->create('api_user_rules_apply', array(
    'path'       => '/user_rules_apply/{id}/page_{page_id}',
    'controller' => 'LegacyApiBundle:UserRules:apply',
    'methods'    => array('GET'),
));

########################################################################################################################
# Login Logs
########################################################################################################################

$collection->create('api_login_logs', array(
    'path'         => '/login_logs/{agent_id}',
    'controller'   => 'LegacyApiBundle:LoginLogs:list',
    'requirements' => array('agent_id' => '\\d+'),
    'defaults'     => array('agent_id' => '0'),
    'methods'      => array('GET'),
));

########################################################################################################################
# Languages
########################################################################################################################

$collection->create('api_langs', array(
    'path'       => '/langs',
    'controller' => 'LegacyApiBundle:Languages:list',
    'methods'    => array('GET'),
));

$collection->create('api_langs_masstickets', array(
    'path'       => '/langs/tools/mass-update-tickets',
    'controller' => 'LegacyApiBundle:Languages:massUpdateTickets',
    'methods'    => array('POST'),
));

$collection->create('api_langs_massusers', array(
    'path'       => '/langs/tools/mass-update-users',
    'controller' => 'LegacyApiBundle:Languages:massUpdateUsers',
    'methods'    => array('POST'),
));

$collection->create('api_langs_setdefault', array(
    'path'       => '/langs/{id}/set-default',
    'controller' => 'LegacyApiBundle:Languages:setDefaultLang',
    'methods'    => array('POST'),
));

$collection->create('api_langs_install', array(
    'path'         => '/langs/{id}/install',
    'controller'   => 'LegacyApiBundle:Languages:installLang',
    'methods'      => array('POST'),
    'requirements' => array('id' => '[a-z_]+'),
));

$collection->create('api_langs_delete', array(
    'path'         => '/langs/{id}/uninstall',
    'controller'   => 'LegacyApiBundle:Languages:uninstallLang',
    'methods'      => array('POST'),
    'requirements' => array('id' => '\d+|[a-z_]+'),
));

$collection->create('api_langs_getinfo', array(
    'path'         => '/langs/{id}',
    'controller'   => 'LegacyApiBundle:Languages:getLang',
    'methods'      => array('GET'),
    'requirements' => array('id' => '\d+|[a-z_]+'),
));

$collection->create('api_langs_saveinfo', array(
    'path'         => '/langs/{id}',
    'controller'   => 'LegacyApiBundle:Languages:saveLang',
    'methods'      => array('POST'),
    'requirements' => array('id' => '\d+|[a-z_]+'),
));

$collection->create('api_langs_savephrases', array(
    'path'         => '/langs/{id}/phrases',
    'controller'   => 'LegacyApiBundle:Languages:savePhraseSet',
    'methods'      => array('POST'),
    'requirements' => array('id' => '\d+|[a-z_]+'),
));

$collection->create('api_langs_getphrasegroups', array(
    'path'       => '/langs/phrases-groups',
    'controller' => 'LegacyApiBundle:Languages:getPhraseGroups',
    'methods'    => array('GET'),
));

$collection->create('api_langs_getphrase_all', array(
    'path'         => '/langs/phrases/{phrase_id}',
    'controller'   => 'LegacyApiBundle:Languages:getPhrase',
    'requirements' => array('phrase_id' => '[a-zA-Z0-9\-_\.]+'),
    'defaults'     => array('for_lang'  => '-1'),
    'methods'      => array('GET'),
));

$collection->create('api_langs_getphrase', array(
    'path'         => '/langs/phrases/{phrase_id}/{for_lang}',
    'controller'   => 'LegacyApiBundle:Languages:getPhrase',
    'defaults'     => array('for_lang'  => '-1'),
    'requirements' => array('phrase_id' => '[a-zA-Z0-9\-_\.]+', 'for_lang' => '\d+|[a-z_]+'),
    'methods'      => array('GET'),
));

$collection->create('api_langs_savephrase', array(
    'path'       => '/langs/phrases/{phrase_id}',
    'controller' => 'LegacyApiBundle:Languages:savePhrase',
    'methods'    => array('POST'),
));

$collection->create('api_langs_getphrases', array(
    'path'         => '/langs/{id}/{group_id}',
    'controller'   => 'LegacyApiBundle:Languages:getPhrases',
    'methods'      => array('GET'),
    'requirements' => array('id' => '\d+|[a-z_]+', 'group_id' => '[a-zA-Z0-9\-_\.]+'),
));

########################################################################################################################
# Templates
########################################################################################################################

$collection->create('api_templates_getinfo', array(
    'path'       => '/templates-info',
    'controller' => 'LegacyApiBundle:Templates:getTemplateInfo',
    'methods'    => array('GET'),
));

$collection->create('api_templates_get', array(
    'path'       => '/templates/{name}',
    'controller' => 'LegacyApiBundle:Templates:getTemplate',
    'methods'    => array('GET'),
));

$collection->create('api_templates_update', array(
    'path'       => '/templates/{name}',
    'controller' => 'LegacyApiBundle:Templates:setTemplate',
    'methods'    => array('POST'),
));

$collection->create('api_templates_delete', array(
    'path'       => '/templates/{name}',
    'controller' => 'LegacyApiBundle:Templates:deleteTemplate',
    'methods'    => array('DELETE'),
));

########################################################################################################################
# Email Templates
########################################################################################################################

$collection->create('api_templates_email_getinfo', array(
    'path'       => '/email-templates-info',
    'controller' => 'LegacyApiBundle:Templates:getEmailTemplateInfo',
    'methods'    => array('GET'),
));

########################################################################################################################
# Save Log
########################################################################################################################

$collection->create('api_savelog_logjserror', array(
    'path'       => '/log-js-error',
    'controller' => 'LegacyApiBundle:SaveLog:logJsError',
    'methods'    => array('POST'),
));

########################################################################################################################
# Widget Selections
########################################################################################################################

$collection->create('api_widget_selections', array(
    'path'       => '/widget/selections',
    'controller' => 'LegacyApiBundle:WidgetSelections:get',
    'methods'    => array('GET'),
));

$collection->create('api_widget_selections_save', array(
    'path'       => '/widget/selections',
    'controller' => 'LegacyApiBundle:WidgetSelections:save',
    'methods'    => array('POST'),
));

########################################################################################################################
# Reports Overview
########################################################################################################################

$collection->create('api_reports_overview_get_data', array(
    'path'       => '/reports/overview/data/{type}',
    'controller' => 'LegacyApiBundle:ReportsOverview:getData',
    'methods'    => array('GET'),
));

$collection->create('api_reports_overview_update_stats', array(
    'path'       => '/reports/overview/get-stats/{type}',
    'controller' => 'LegacyApiBundle:ReportsOverview:getStats',
    'methods'    => array('GET'),
));

########################################################################################################################
# Report Builder
########################################################################################################################

$collection->create('api_reports_builder_list', array(
    'path'       => '/reports/builder',
    'controller' => 'LegacyApiBundle:ReportsBuilder:list',
    'methods'    => array('GET'),
));

$collection->create('api_reports_builder_list_custom', array(
    'path'       => '/reports/builder/custom',
    'controller' => 'LegacyApiBundle:ReportsBuilder:listCustom',
    'methods'    => array('GET'),
));

$collection->create('api_reports_builder_list_builtIn', array(
    'path'       => '/reports/builder/builtIn',
    'controller' => 'LegacyApiBundle:ReportsBuilder:listBuiltIn',
    'methods'    => array('GET'),
));

$collection->create('api_reports_builder_get_group_params', array(
    'path'       => '/reports/builder/group-params',
    'controller' => 'LegacyApiBundle:ReportsBuilder:getGroupParams',
    'methods'    => array('GET'),
));

$collection->create('api_reports_builder_get', array(
    'path'         => '/reports/builder/{id}',
    'controller'   => 'LegacyApiBundle:ReportsBuilder:get',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_reports_builder_delete', array(
    'path'         => '/reports/builder/{id}',
    'controller'   => 'LegacyApiBundle:ReportsBuilder:delete',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_reports_builder_create', array(
    'path'       => '/reports/builder',
    'controller' => 'LegacyApiBundle:ReportsBuilder:save',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_reports_builder_save', array(
    'path'         => '/reports/builder/{id}',
    'controller'   => 'LegacyApiBundle:ReportsBuilder:save',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_reports_builder_clone', array(
    'path'         => '/reports/builder/clone/{id}',
    'controller'   => 'LegacyApiBundle:ReportsBuilder:clone',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_reports_builder_test', array(
    'path'         => '/reports/builder/test/{id}',
    'controller'   => 'LegacyApiBundle:ReportsBuilder:test',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_reports_builder_parse', array(
    'path'       => '/reports/builder/parse',
    'controller' => 'LegacyApiBundle:ReportsBuilder:parse',
    'methods'    => array('POST'),
));

$collection->create('api_reports_builder_download', array(
    'path'       => '/reports/builder/download/{id}/{type}',
    'controller' => 'LegacyApiBundle:ReportsBuilder:download',
    'methods'    => array('GET'),
));

########################################################################################################################
# Report Agent Activity
########################################################################################################################

$collection->create('api_reports_agent_activity_list', array(
    'path'       => '/reports/agent-activity/{agent_or_team_id}/{date}',
    'controller' => 'LegacyApiBundle:ReportsAgentActivity:list',
    'defaults'   => array('agent_or_team_id' => 'all', 'date' => ''),
    'methods'    => array('GET'),
));

########################################################################################################################
# Report Agent Hours
########################################################################################################################

$collection->create('api_reports_agent_hours_list', array(
    'path'       => '/reports/agent-hours/{date1}/{date2}',
    'controller' => 'LegacyApiBundle:ReportsAgentHours:list',
    'defaults'   => array('date1' => '', 'date2' => ''),
    'methods'    => array('GET'),
));

########################################################################################################################
# Report Ticket Satisfaction
########################################################################################################################

$collection->create('api_reports_ticket_satisfaction_list', array(
    'path'       => '/reports/ticket-satisfaction/{page}',
    'controller' => 'LegacyApiBundle:ReportsTicketSatisfaction:list',
    'defaults'   => array('page' => '0'),
    'methods'    => array('GET'),
));

$collection->create('api_reports_ticket_satisfaction_summary', array(
    'path'       => '/reports/ticket-satisfaction/summary/{date}',
    'controller' => 'LegacyApiBundle:ReportsTicketSatisfaction:summary',
    'defaults'   => array('date' => ''),
    'methods'    => array('GET'),
));

########################################################################################################################
# Report Billing
########################################################################################################################

$collection->create('api_reports_billing_get', array(
    'path'       => '/reports/billing/{id}',
    'controller' => 'LegacyApiBundle:ReportsBilling:get',
    'methods'    => array('GET'),
));

########################################################################################################################
# Report Dashboards
########################################################################################################################

$collection->create('dashboards_list', array(
    'path'         => '/dashboards',
    'controller'   => 'LegacyApiBundle:Dashboard:list',
    'defaults'     => array('action' => 'list'),
    'methods'      => array('GET',),
));

$collection->create('dashboards_get', array(
    'path'         => '/dashboards/{id}',
    'controller'   => 'LegacyApiBundle:Dashboard:get',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'get'),
    'methods'      => array('GET',),
));

$collection->create('dashboard_create', array(
    'path'         => '/dashboards',
    'controller'   => 'LegacyApiBundle:Dashboard:save',
    'defaults'     => array('action' => 'save', 'id' => 0),
    'methods'      => array('POST',),
));

$collection->create('dashboard_update', array(
    'path'         => '/dashboards/{id}',
    'controller'   => 'LegacyApiBundle:Dashboard:save',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'save'),
    'methods'      => array('POST',),
));

$collection->create('dashboard_clone', array(
    'path'         => '/dashboards/clone/{id}',
    'controller'   => 'LegacyApiBundle:Dashboard:clone',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'clone'),
    'methods'      => array('POST',),
));

$collection->create('dashboard_delete', array(
    'path'         => '/dashboards/{id}',
    'controller'   => 'LegacyApiBundle:Dashboard:delete',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'delete'),
    'methods'      => array('DELETE',),
));

########################################################################################################################
# Report Dashboards Wdigets
########################################################################################################################

$collection->create('dashboard_widget_get    ', array(
    'path'         => '/dashboards/widgets/{id}',
    'controller'   => 'LegacyApiBundle:DashboardWidget:getWidget',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'getWidget'),
    'methods'      => array('GET',),
));

$collection->create('dashboard_widget_save', array(
    'path'         => '/dashboards/widgets/{id}',
    'controller'   => 'LegacyApiBundle:DashboardWidget:saveWidget',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'saveWidget'),
    'methods'      => array('POST',),
));

$collection->create('dashboard_widget_create', array(
    'path'         => '/dashboards/{id}/widgets',
    'controller'   => 'LegacyApiBundle:DashboardWidget:addWidget',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'addWidget'),
    'methods'      => array('POST',),
));

$collection->create('dashboard_widget_delete    ', array(
    'path'         => '/dashboards/widgets/{id}',
    'controller'   => 'LegacyApiBundle:DashboardWidget:deleteWidget',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'deleteWidget'),
    'methods'      => array('DELETE',),
));

$collection->create('dashboard_widget_reports_list', array(
    'path'         => '/dashboards/widgets/reports/list',
    'controller'   => 'LegacyApiBundle:DashboardWidget:reportsList',
    'defaults'     => array('action' => 'reportsList'),
    'methods'      => array('GET',),
));

########################################################################################################################
# Report Dashboards Reports
########################################################################################################################

$collection->create('dashboards_reports_list', array(
    'path'         => '/dashboards/reports',
    'controller'   => 'LegacyApiBundle:DashboardReport:list',
    'defaults'     => array('action' => 'list'),
    'methods'      => array('GET',),
));

$collection->create('dashboard_reports_get', array(
    'path'         => '/dashboards/reports/{id}',
    'controller'   => 'LegacyApiBundle:DashboardReport:get',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'get'),
    'methods'      => array('GET',),
));


$collection->create('dashboard_reports_create', array(
    'path'         => '/dashboards/reports/{dashboard_id}',
    'controller'   => 'LegacyApiBundle:DashboardReport:create',
    'defaults'     => array('action' => 'create'),
    'requirements' => array('dashboard_id' => '\\d+'),
    'methods'      => array('POST',),
));

$collection->create('dashboard_reports_update', array(
    'path'         => '/dashboards/reports/{id}/save',
    'controller'   => 'LegacyApiBundle:DashboardReport:save',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'save'),
    'methods'      => array('POST',),
));


$collection->create('dashboards_reports_clone', array(
    'path'         => '/dashboards/reports/clone/{id}/{dashboard_id}',
    'controller'   => 'LegacyApiBundle:DashboardReport:clone',
    'requirements' => array('id' => '\\d+', 'dashboard_id' => '\\d+'),
    'defaults'     => array('action' => 'clone'),
    'methods'      => array('POST',),
));

$collection->create('dashboards_reports_delete    ', array(
    'path'         => '/dashboards/reports/{id}',
    'controller'   => 'LegacyApiBundle:DashboardReport:delete',
    'requirements' => array('id' => '\\d+'),
    'defaults'     => array('action' => 'delete'),
    'methods'      => array('DELETE',),
));

########################################################################################################################
# Report Dashboards Permissions
########################################################################################################################

$collection->create('dashboards_permissions_list', array(
    'path'         => '/dashboards/permissions/{id}',
    'controller'   => 'LegacyApiBundle:DashboardPermissions:list',
    'defaults'     => array('action' => 'list'),
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET',),
));

$collection->create('dashboards_permissions_list_new_dashboard', array(
    'path'         => '/dashboards/permissions',
    'controller'   => 'LegacyApiBundle:DashboardPermissions:list',
    'defaults'     => array('action' => 'list'),
    'methods'      => array('GET',),
));

$collection->create('dashboards_permissions_save', array(
    'path'         => '/dashboards/permissions/{id}',
    'controller'   => 'LegacyApiBundle:DashboardPermissions:save',
    'defaults'     => array('action' => 'save'),
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST',),
));


########################################################################################################################
# Plugins
########################################################################################################################

$collection->create('api_plugins_package_list', array(
    'path'       => '/plugins/packages',
    'controller' => 'LegacyApiBundle:Plugins:listPackages',
    'methods'    => array('GET'),
));

$collection->create('api_plugins_package_getinstaller', array(
    'path'         => '/plugins/packages/{name}/installer',
    'controller'   => 'LegacyApiBundle:Plugins:getPackageInstaller',
    'requirements' => array('name' => '[a-z0-9\._]+'),
    'methods'      => array('GET'),
));

########################################################################################################################
# Blobs
########################################################################################################################

$collection->create('api_blobs_upload', array(
    'path'       => '/blobs',
    'controller' => 'LegacyApiBundle:Blobs:upload',
    'methods'    => array('PUT', 'POST'),
));

$collection->create('api_blobs_get', array(
    'path'       => '/blobs/{id}/{auth}',
    'controller' => 'LegacyApiBundle:Blobs:getInfo',
    'methods'    => array('GET'),
));

$collection->create(
    'api_blobs_delete',
    array(
        'path'       => '/blobs/{id}/{auth}',
        'controller' => 'LegacyApiBundle:Blobs:delete',
        'methods'    => array('DELETE'),
    )
);

########################################################################################################################
# My
########################################################################################################################

$collection->create('api_my_session_renewtoken', array(
    'path'       => '/my/session/renew-request-token',
    'controller' => 'LegacyApiBundle:MySession:renewRequestToken',
    'methods'    => array('GET'),
));

########################################################################################################################
# Apps
########################################################################################################################

$collection->create('api_apps', array(
    'path'       => '/apps',
    'controller' => 'LegacyApiBundle:Apps:list',
    'methods'    => array('GET'),
));

$collection->create('api_apps_resync_packages', array(
    'path'       => '/apps/resync-packages',
    'controller' => 'LegacyApiBundle:Apps:resyncPackages',
    'methods'    => array('POST'),
));

$collection->create('api_apps_upload_package', array(
    'path'       => '/apps/upload-package',
    'controller' => 'LegacyApiBundle:Apps:uploadPackage',
    'methods'    => array('POST'),
));

$collection->create('api_apps_custom_new', array(
    'path'       => '/apps/custom',
    'controller' => 'LegacyApiBundle:Apps:createCustomApp',
    'methods'    => array('PUT'),
));

$collection->create('api_apps_custom_getassets', array(
    'path'         => '/apps/custom/{id}/assets',
    'controller'   => 'LegacyApiBundle:Apps:getCustomAssets',
    'methods'      => array('GET'),
    'requirements' => array('id' => '\d+'),
));

$collection->create('api_apps_package', array(
    'path'         => '/apps/packages/{name}',
    'controller'   => 'LegacyApiBundle:Apps:getPackage',
    'methods'      => array('GET'),
    'requirements' => array('name' => '[a-zA-Z0-9_\-\.]+'),
));

$collection->create('api_apps_package_delete', array(
    'path'         => '/apps/packages/{name}',
    'controller'   => 'LegacyApiBundle:Apps:deletePackage',
    'methods'      => array('DELETE'),
    'requirements' => array('name' => '[a-zA-Z0-9_\-\.]+'),
));

$collection->create('api_apps_instance', array(
    'path'         => '/apps/instances/{id}',
    'controller'   => 'LegacyApiBundle:Apps:getInstance',
    'methods'      => array('GET'),
    'requirements' => array('id' => '\d+'),
));

$collection->create('api_apps_instance_update', array(
    'path'         => '/apps/instances/{id}',
    'controller'   => 'LegacyApiBundle:Apps:updateInstance',
    'methods'      => array('POST'),
    'requirements' => array('id' => '\d+'),
));

$collection->create('api_apps_instance_uninstall', array(
    'path'         => '/apps/instances/{id}',
    'controller'   => 'LegacyApiBundle:Apps:uninstallInstance',
    'methods'      => array('DELETE'),
    'requirements' => array('id' => '\d+'),
));

$collection->create('api_apps_install', array(
    'path'       => '/apps/packages/{name}',
    'controller' => 'LegacyApiBundle:Apps:installPackage',
    'methods'    => array('PUT'),
));

$collection->create('api_apps_package_exec', array(
    'path'         => '/apps/packages/{name}/{action}',
    'controller'   => 'LegacyApiBundle:Apps:execPackage',
    'defaults'     => array('action' => 'default'),
    'methods'      => array('GET', 'POST', 'PUT', 'DELETE'),
    'requirements' => array('name' => '[a-zA-Z0-9_\-\.]+'),
));

$collection->create('api_apps_instance_exec', array(
    'path'         => '/apps/instances/{id}/{action}',
    'controller'   => 'LegacyApiBundle:Apps:execInstance',
    'defaults'     => array('action' => 'default'),
    'methods'      => array('GET', 'POST', 'PUT', 'DELETE'),
    'requirements' => array('id' => '\d+'),
));

$collection->create('api_apps_jira', array(
    'path'       => '/apps/jira',
    'controller' => 'LegacyApiBundle:Apps:jiraSettings',
    'methods'    => array('GET'),
));

########################################################################################################################
# Reset Demo
########################################################################################################################

$collection->create('api_reset_demo_run', array(
    'path'       => '/reset-demo',
    'controller' => 'LegacyApiBundle:ResetDemo:run',
    'methods'    => array('POST'),
));

$collection->create('api_reset_demo_status', array(
    'path'       => '/reset-demo/status',
    'controller' => 'LegacyApiBundle:ResetDemo:status',
    'methods'    => array('GET'),
));

##############################################################################################
# Importers
##############################################################################################

$collection->create(
    'api_server_importers_list',
    array(
        'path'       => '/server/importers',
        'controller' => 'LegacyApiBundle:Importers:list',
        'methods'    => array('GET'),
    )
);

$collection->create(
    'api_server_importers_get',
    array(
        'path'       => '/server/importers/{id}',
        'controller' => 'LegacyApiBundle:Importers:get',
        'methods'    => array('GET'),
    )
);

$collection->create(
    'api_server_importers_getlog',
    array(
        'path'       => '/server/importers/{id}/download-log',
        'controller' => 'LegacyApiBundle:Importers:downloadLog',
        'methods'    => array('GET'),
    )
);

$collection->create(
    'api_server_importers_save',
    array(
        'path'       => '/server/importers/{id}',
        'controller' => 'LegacyApiBundle:Importers:save',
        'methods'    => array('PUT'),
    )
);

$collection->create(
    'api_server_importers_test',
    array(
        'path'       => '/server/importers/{id}/test',
        'controller' => 'LegacyApiBundle:Importers:test',
        'methods'    => array('GET'),
    )
);

$collection->create(
    'api_server_importers_start',
    array(
        'path'       => '/server/importers/{id}/start',
        'controller' => 'LegacyApiBundle:Importers:start',
        'methods'    => array('GET'),
    )
);

##############################################################################################
# Billing Fields
##############################################################################################

$collection->create('api_billing_fields_get', array(
    'path'         => '/billing_fields/{id}',
    'controller'   => 'LegacyApiBundle:BillingFields:getCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('GET'),
));

$collection->create('api_billing_fields_create', array(
    'path'       => '/billing_fields',
    'controller' => 'LegacyApiBundle:BillingFields:saveCustomField',
    'defaults'   => array('id' => '0'),
    'methods'    => array('PUT'),
));

$collection->create('api_billing_fields_save', array(
    'path'         => '/billing_fields/{id}',
    'controller'   => 'LegacyApiBundle:BillingFields:saveCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('POST'),
));

$collection->create('api_billing_fields_delete', array(
    'path'         => '/billing_fields/{id}',
    'controller'   => 'LegacyApiBundle:BillingFields:deleteCustomField',
    'requirements' => array('id' => '\\d+'),
    'methods'      => array('DELETE'),
));

$collection->create('api_billing_fields', array(
    'path'       => '/billing_fields',
    'controller' => 'LegacyApiBundle:BillingFields:list',
    'methods'    => array('GET'),
));

$collection->create('api_billing_fields_setenabled', array(
    'path'       => '/billing_fields/set-enabled/{field_id}/{is_enabled}',
    'controller' => 'LegacyApiBundle:BillingFields:toggleField',
    'methods'    => array('POST'),
));

$collection->create('api_billing_fields_update_order', array(
    'path'       => '/billing_fields/display-order',
    'controller' => 'LegacyApiBundle:BillingFields:saveDisplayOrder',
    'methods'    => array('POST'),
));

return $collection;
