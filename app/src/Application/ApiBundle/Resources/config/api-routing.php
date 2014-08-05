<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('api', array(
	'path'        => '/',
	'controller'  => 'ApiBundle:Test:About',
	'methods'     => array('GET'),
));

$collection->create('api_discover', array(
	'path'        => '/discover',
	'controller'  => 'ApiBundle:Test:discover',
	'methods'     => array('GET'),
));

$collection->create('api_test', array(
	'path'        => '/test',
	'controller'  => 'ApiBundle:Test:test',
	'methods'     => array('GET'),
));

$collection->create('api_test_post', array(
	'path'        => '/test',
	'controller'  => 'ApiBundle:Test:postTest',
	'methods'     => array('POST'),
));

$collection->create('api_deskpro_time', array(
	'path'        => '/deskpro/time',
	'controller'  => 'ApiBundle:Deskpro:time',
	'methods'     => array('GET'),
));

$collection->create('api_deskpro_info', array(
	'path'        => '/deskpro/info',
	'controller'  => 'ApiBundle:Misc:helpdeskInfo',
	'methods'     => array('GET'),
));

$collection->create('api_me_lastlogin', array(
	'path'        => '/me/last-login',
	'controller'  => 'ApiBundle:Misc:getLastLogin',
	'methods'     => array('GET'),
));

$collection->create('api_token_exchange', array(
	'path'        => '/token-exchange',
	'controller'  => 'ApiBundle:Misc:tokenExchange',
	'methods'     => array('POST'),
));

$collection->create('api_token_renew', array(
	'path'        => '/renew-token',
	'controller'  => 'ApiBundle:Misc:renewToken',
	'methods'     => array('POST'),
));

$collection->create('api_profile_inhelpstate', array(
	'path'        => '/profile/inhelp/{id}/{state}',
	'controller'  => 'ApiBundle:Profile:saveInhelpState',
	'methods'     => array('POST'),
));

$collection->create('api_docs', array(
	'path'        => '/docs',
	'controller'  => 'ApiBundle:Docs:list',
	'methods'     => array('GET'),
));

$collection->create('api_docs_get', array(
	'path'        => '/docs/{id}',
	'controller'  => 'ApiBundle:Docs:get',
	'methods'     => array('GET'),
));

########################################################################################################################
# General
########################################################################################################################

$collection->create('api_misc_upload', array(
	'path'        => '/misc/upload',
	'controller'  => 'ApiBundle:Misc:upload',
	'methods'     => array('POST'),
));

$collection->create('api_misc_session_person', array(
	'path'        => '/misc/session-person/{session_code}',
	'controller'  => 'ApiBundle:Misc:getSessionPerson',
	'methods'     => array('GET'),
));

$collection->create('api_misc_rate_limit', array(
	'path'        => '/misc/rate-limit',
	'controller'  => 'ApiBundle:Misc:getRateLimit',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_new', array(
	'path'        => '/tickets',
	'controller'  => 'ApiBundle:Ticket:newTicket',
	'methods'     => array('POST'),
));

$collection->create('api_tickets_ticket', array(
	'path'          => '/tickets/{ticket_id}',
	'controller'    => 'ApiBundle:Ticket:getTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_post', array(
	'path'          => '/tickets/{ticket_id}',
	'controller'    => 'ApiBundle:Ticket:postTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_delete', array(
	'path'          => '/tickets/{ticket_id}',
	'controller'    => 'ApiBundle:Ticket:deleteTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tickets_ticket_logs', array(
	'path'          => '/tickets/{ticket_id}/logs',
	'controller'    => 'ApiBundle:Ticket:getTicketLogs',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_messages', array(
	'path'          => '/tickets/{ticket_id}/messages',
	'controller'    => 'ApiBundle:Ticket:getTicketMessages',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_messages_post', array(
	'path'          => '/tickets/{ticket_id}/messages',
	'controller'    => 'ApiBundle:Ticket:replyTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_message', array(
	'path'          => '/tickets/{ticket_id}/messages/{message_id}',
	'controller'    => 'ApiBundle:Ticket:getTicketMessage',
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_message_details', array(
	'path'          => '/tickets/{ticket_id}/messages/{message_id}/details',
	'controller'    => 'ApiBundle:Ticket:getTicketMessageDetails',
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_undelete', array(
	'path'          => '/tickets/{ticket_id}/undelete',
	'controller'    => 'ApiBundle:Ticket:undeleteTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_split', array(
	'path'          => '/tickets/{ticket_id}/split',
	'controller'    => 'ApiBundle:Ticket:splitTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_claim', array(
	'path'          => '/tickets/{ticket_id}/claim',
	'controller'    => 'ApiBundle:Ticket:claimTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_merge', array(
	'path'          => '/tickets/{ticket_id}/merge/{merge_ticket_id}',
	'controller'    => 'ApiBundle:Ticket:mergeTicket',
	'requirements'  => array('ticket_id' => '\\d+', 'merge_ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_spam', array(
	'path'          => '/tickets/{ticket_id}/spam',
	'controller'    => 'ApiBundle:Ticket:spamTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_unspam', array(
	'path'          => '/tickets/{ticket_id}/unspam',
	'controller'    => 'ApiBundle:Ticket:unspamTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_lock', array(
	'path'          => '/tickets/{ticket_id}/lock',
	'controller'    => 'ApiBundle:Ticket:lockTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_unlock', array(
	'path'          => '/tickets/{ticket_id}/unlock',
	'controller'    => 'ApiBundle:Ticket:unlockTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_tasks', array(
	'path'          => '/tickets/{ticket_id}/tasks',
	'controller'    => 'ApiBundle:Ticket:getTicketTasks',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_tasks_post', array(
	'path'          => '/tickets/{ticket_id}/tasks',
	'controller'    => 'ApiBundle:Ticket:postTicketTasks',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_billing_charges', array(
	'path'          => '/tickets/{ticket_id}/billing-charges',
	'controller'    => 'ApiBundle:Ticket:getTicketBillingCharges',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_billing_charges_post', array(
	'path'          => '/tickets/{ticket_id}/billing-charges',
	'controller'    => 'ApiBundle:Ticket:postTicketBillingCharges',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_billing_charge', array(
	'path'          => '/tickets/{ticket_id}/billing-charges/{charge_id}',
	'controller'    => 'ApiBundle:Ticket:getTicketBillingCharge',
	'requirements'  => array('ticket_id' => '\\d+', 'charge_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_billing_charge_delete', array(
	'path'          => '/tickets/{ticket_id}/billing-charges/{charge_id}',
	'controller'    => 'ApiBundle:Ticket:deleteTicketBillingCharge',
	'requirements'  => array('ticket_id' => '\\d+', 'charge_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tickets_ticket_slas', array(
	'path'          => '/tickets/{ticket_id}/slas',
	'controller'    => 'ApiBundle:Ticket:getTicketSlas',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_slas_post', array(
	'path'          => '/tickets/{ticket_id}/slas',
	'controller'    => 'ApiBundle:Ticket:postTicketSlas',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_sla', array(
	'path'          => '/tickets/{ticket_id}/slas/{ticket_sla_id}',
	'controller'    => 'ApiBundle:Ticket:getTicketSla',
	'requirements'  => array('ticket_id' => '\\d+', 'ticket_sla_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_sla_delete', array(
	'path'          => '/tickets/{ticket_id}/slas/{ticket_sla_id}',
	'controller'    => 'ApiBundle:Ticket:deleteTicketSla',
	'requirements'  => array('ticket_id' => '\\d+', 'ticket_sla_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tickets_ticket_participants', array(
	'path'          => '/tickets/{ticket_id}/participants',
	'controller'    => 'ApiBundle:Ticket:getParticipants',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_participants_post', array(
	'path'          => '/tickets/{ticket_id}/participants',
	'controller'    => 'ApiBundle:Ticket:postParticipants',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_participant', array(
	'path'          => '/tickets/{ticket_id}/participants/{person_id}',
	'controller'    => 'ApiBundle:Ticket:getParticipant',
	'requirements'  => array('ticket_id' => '\\d+', 'person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_participant_delete', array(
	'path'          => '/tickets/{ticket_id}/participants/{person_id}',
	'controller'    => 'ApiBundle:Ticket:deleteParticipant',
	'requirements'  => array('ticket_id' => '\\d+', 'person_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tickets_ticket_labels', array(
	'path'          => '/tickets/{ticket_id}/labels',
	'controller'    => 'ApiBundle:Ticket:getLabels',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_labels_post', array(
	'path'          => '/tickets/{ticket_id}/labels',
	'controller'    => 'ApiBundle:Ticket:postLabels',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets_ticket_label', array(
	'path'          => '/tickets/{ticket_id}/labels/{label}',
	'controller'    => 'ApiBundle:Ticket:getLabel',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_ticket_label_delete', array(
	'path'          => '/tickets/{ticket_id}/labels/{label}',
	'controller'    => 'ApiBundle:Ticket:deleteLabel',
	'requirements'  => array('ticket_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tickets_fields', array(
	'path'        => '/tickets/fields',
	'controller'  => 'ApiBundle:Ticket:getFields',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_departments', array(
	'path'        => '/tickets/departments',
	'controller'  => 'ApiBundle:Ticket:getDepartments',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_products', array(
	'path'        => '/tickets/products',
	'controller'  => 'ApiBundle:Ticket:getProducts',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_categories', array(
	'path'        => '/tickets/categories',
	'controller'  => 'ApiBundle:Ticket:getCategories',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_priorities', array(
	'path'        => '/tickets/priorities',
	'controller'  => 'ApiBundle:Ticket:getPriorities',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_workflows', array(
	'path'        => '/tickets/workflows',
	'controller'  => 'ApiBundle:Ticket:getWorkflows',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_slas', array(
	'path'        => '/tickets/slas',
	'controller'  => 'ApiBundle:Ticket:getSlas',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_sla', array(
	'path'          => '/tickets/slas/{sla_id}',
	'controller'    => 'ApiBundle:Ticket:getSla',
	'requirements'  => array('sla_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_sla_people', array(
	'path'          => '/tickets/slas/{sla_id}/people',
	'controller'    => 'ApiBundle:Ticket:getSlaPeople',
	'requirements'  => array('sla_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tickets_sla_organizations', array(
	'path'          => '/tickets/slas/{sla_id}/organizations',
	'controller'    => 'ApiBundle:Ticket:getSlaOrganizations',
	'requirements'  => array('sla_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_textsnippets_list', array(
	'path'        => '/text-snippets/{typename}',
	'controller'  => 'ApiBundle:TextSnippets:filterSnippets',
	'methods'     => array('GET'),
));

$collection->create('api_textsnippets_new', array(
	'path'        => '/text-snippets/{typename}',
	'controller'  => 'ApiBundle:TextSnippets:saveSnippet',
	'defaults'    => array('snippet_id' => '0'),
	'methods'     => array('POST'),
));

$collection->create('api_textsnippets_edit', array(
	'path'          => '/text-snippets/{typename}/{snippet_id}',
	'controller'    => 'ApiBundle:TextSnippets:saveSnippet',
	'requirements'  => array('snippet_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_textsnippets_del', array(
	'path'          => '/text-snippets/{typename}/{snippet_id}',
	'controller'    => 'ApiBundle:TextSnippets:deleteSnippet',
	'requirements'  => array('snippet_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_textsnippets_get', array(
	'path'          => '/text-snippets/{typename}/{snippet_id}',
	'controller'    => 'ApiBundle:TextSnippets:getSnippet',
	'requirements'  => array('snippet_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_textsnippets_cats_list', array(
	'path'        => '/text-snippets/{typename}/categories',
	'controller'  => 'ApiBundle:TextSnippets:listCategories',
	'methods'     => array('GET'),
));

$collection->create('api_textsnippets_cats_new', array(
	'path'        => '/text-snippets/{typename}/categories',
	'controller'  => 'ApiBundle:TextSnippets:saveCategory',
	'defaults'    => array('category_id' => '0'),
	'methods'     => array('POST'),
));

$collection->create('api_textsnippets_cats_edit', array(
	'path'          => '/text-snippets/{typename}/categories/{category_id}',
	'controller'    => 'ApiBundle:TextSnippets:saveCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_textsnippets_cats_get', array(
	'path'          => '/text-snippets/{typename}/categories/{category_id}',
	'controller'    => 'ApiBundle:TextSnippets:getCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_textsnippets_cats_del', array(
	'path'          => '/text-snippets/{typename}/categories/{category_id}',
	'controller'    => 'ApiBundle:TextSnippets:deleteCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_open_tickets_newticketmessage', array(
	'path'          => '/open/tickets/new-ticket-message',
	'controller'    => 'ApiBundle:OpenTicket:newTicketMessage',
	'requirements'  => array('sla_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tickets', array(
	'path'        => '/tickets',
	'controller'  => 'ApiBundle:TicketSearch:search',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_quickstats', array(
	'path'        => '/tickets/quick-stats',
	'controller'  => 'ApiBundle:TicketSearch:getQuickStats',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_filters', array(
	'path'        => '/tickets/filters',
	'controller'  => 'ApiBundle:TicketSearch:getFilters',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_filter_counts', array(
	'path'        => '/tickets/filters/counts',
	'controller'  => 'ApiBundle:TicketSearch:getFilterCounts',
	'methods'     => array('GET'),
));

$collection->create('api_tickets_filter', array(
	'path'          => '/tickets/filters/{filter_id}',
	'controller'    => 'ApiBundle:TicketSearch:getFilter',
	'requirements'  => array('filter_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people', array(
	'path'        => '/people',
	'controller'  => 'ApiBundle:Person:search',
	'methods'     => array('GET'),
));

$collection->create('api_people_post', array(
	'path'        => '/people',
	'controller'  => 'ApiBundle:Person:newPerson',
	'methods'     => array('POST'),
));

$collection->create('api_people_person', array(
	'path'          => '/people/{person_id}',
	'controller'    => 'ApiBundle:Person:getPerson',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_post', array(
	'path'          => '/people/{person_id}',
	'controller'    => 'ApiBundle:Person:postPerson',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_delete', array(
	'path'          => '/people/{person_id}',
	'controller'    => 'ApiBundle:Person:deletePerson',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_person_merge', array(
	'path'          => '/people/{person_id}/merge/{other_person_id}',
	'controller'    => 'ApiBundle:Person:mergePerson',
	'requirements'  => array('person_id' => '\\d+', 'other_person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_logintoken', array(
	'path'          => '/people/{person_id}/login-token',
	'controller'    => 'ApiBundle:Person:getLoginToken',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_picture', array(
	'path'          => '/people/{person_id}/picture',
	'controller'    => 'ApiBundle:Person:getPersonPicture',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_picture_post', array(
	'path'          => '/people/{person_id}/picture',
	'controller'    => 'ApiBundle:Person:postPersonPicture',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_picture_delete', array(
	'path'          => '/people/{person_id}/picture',
	'controller'    => 'ApiBundle:Person:deletePersonPicture',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_person_emails', array(
	'path'          => '/people/{person_id}/emails',
	'controller'    => 'ApiBundle:Person:getPersonEmails',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_emails_post', array(
	'path'          => '/people/{person_id}/emails',
	'controller'    => 'ApiBundle:Person:postPersonEmails',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_email', array(
	'path'          => '/people/{person_id}/emails/{email_id}',
	'controller'    => 'ApiBundle:Person:getPersonEmail',
	'requirements'  => array('person_id' => '\\d+', 'email_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_email_post', array(
	'path'          => '/people/{person_id}/emails/{email_id}',
	'controller'    => 'ApiBundle:Person:postPersonEmail',
	'requirements'  => array('person_id' => '\\d+', 'email_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_email_delete', array(
	'path'          => '/people/{person_id}/emails/{email_id}',
	'controller'    => 'ApiBundle:Person:deletePersonEmail',
	'requirements'  => array('person_id' => '\\d+', 'email_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_person_vcard', array(
	'path'          => '/people/{person_id}/vcard',
	'controller'    => 'ApiBundle:Person:getPersonVcard',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_tickets', array(
	'path'          => '/people/{person_id}/tickets',
	'controller'    => 'ApiBundle:Person:getPersonTickets',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_chats', array(
	'path'          => '/people/{person_id}/chats',
	'controller'    => 'ApiBundle:Person:getPersonChats',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_activity_stream', array(
	'path'          => '/people/{person_id}/activity-stream',
	'controller'    => 'ApiBundle:Person:getPersonActivityStream',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_reset_password', array(
	'path'          => '/people/{person_id}/reset-password',
	'controller'    => 'ApiBundle:Person:resetPassword',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_clear_session', array(
	'path'          => '/people/{person_id}/clear-session',
	'controller'    => 'ApiBundle:Person:clearSession',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_slas', array(
	'path'          => '/people/{person_id}/slas',
	'controller'    => 'ApiBundle:Person:getPersonSlas',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_slas_post', array(
	'path'          => '/people/{person_id}/slas',
	'controller'    => 'ApiBundle:Person:postPersonSlas',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_sla', array(
	'path'          => '/people/{person_id}/slas/{sla_id}',
	'controller'    => 'ApiBundle:Person:getPersonSla',
	'requirements'  => array('person_id' => '\\d+', 'sla_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_sla_delete', array(
	'path'          => '/people/{person_id}/slas/{sla_id}',
	'controller'    => 'ApiBundle:Person:deletePersonSla',
	'requirements'  => array('person_id' => '\\d+', 'sla_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_person_notes', array(
	'path'          => '/people/{person_id}/notes',
	'controller'    => 'ApiBundle:Person:getPersonNotes',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_notes_post', array(
	'path'          => '/people/{person_id}/notes',
	'controller'    => 'ApiBundle:Person:postPersonNotes',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_billing_charges', array(
	'path'          => '/people/{person_id}/billing-charges',
	'controller'    => 'ApiBundle:Person:getPersonBillingCharges',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_contact_details', array(
	'path'          => '/people/{person_id}/contact-details',
	'controller'    => 'ApiBundle:Person:getPersonContactDetails',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_contact_details_post', array(
	'path'          => '/people/{person_id}/contact-details',
	'controller'    => 'ApiBundle:Person:postPersonContactDetails',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_contact_detail', array(
	'path'          => '/people/{person_id}/contact-details/{contact_id}',
	'controller'    => 'ApiBundle:Person:getPersonContactDetail',
	'requirements'  => array('person_id' => '\\d+', 'contact_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_contact_detail_delete', array(
	'path'          => '/people/{person_id}/contact-details/{contact_id}',
	'controller'    => 'ApiBundle:Person:deletePersonContactDetail',
	'requirements'  => array('person_id' => '\\d+', 'contact_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_person_groups', array(
	'path'          => '/people/{person_id}/groups',
	'controller'    => 'ApiBundle:Person:getPersonGroups',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_groups_post', array(
	'path'          => '/people/{person_id}/groups',
	'controller'    => 'ApiBundle:Person:postPersonGroups',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_group', array(
	'path'          => '/people/{person_id}/groups/{usergroup_id}',
	'controller'    => 'ApiBundle:Person:getPersonGroup',
	'requirements'  => array('person_id' => '\\d+', 'usergroup_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_group_delete', array(
	'path'          => '/people/{person_id}/groups/{usergroup_id}',
	'controller'    => 'ApiBundle:Person:deletePersonGroup',
	'requirements'  => array('person_id' => '\\d+', 'usergroup_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_person_labels', array(
	'path'          => '/people/{person_id}/labels',
	'controller'    => 'ApiBundle:Person:getPersonLabels',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_labels_post', array(
	'path'          => '/people/{person_id}/labels',
	'controller'    => 'ApiBundle:Person:postPersonLabels',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_people_person_label', array(
	'path'          => '/people/{person_id}/labels/{label}',
	'controller'    => 'ApiBundle:Person:getPersonLabel',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_people_person_label_delete', array(
	'path'          => '/people/{person_id}/labels/{label}',
	'controller'    => 'ApiBundle:Person:deletePersonLabel',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_people_fields', array(
	'path'        => '/people/fields',
	'controller'  => 'ApiBundle:Person:getFields',
	'methods'     => array('GET'),
));

$collection->create('api_people_groups', array(
	'path'        => '/people/groups',
	'controller'  => 'ApiBundle:Person:getGroups',
	'methods'     => array('GET'),
));

$collection->create('api_agents_list', array(
	'path'        => '/agents',
	'controller'  => 'ApiBundle:Agents:listAgents',
	'methods'     => array('GET'),
));

$collection->create('api_agents_list_deleted', array(
	'path'        => '/agents/deleted',
	'controller'  => 'ApiBundle:Agents:listDeletedAgents',
	'methods'     => array('GET'),
));

$collection->create('api_agents_get_deleted', array(
	'path'        => '/agents/deleted/{id}',
	'controller'  => 'ApiBundle:Agents:getDeletedAgent',
	'methods'     => array('GET'),
));

$collection->create('api_agents_undelete', array(
	'path'        => '/agents/deleted/{id}/undelete',
	'controller'  => 'ApiBundle:Agents:undeleteAgent',
	'methods'     => array('POST'),
));

$collection->create('api_agents_get', array(
	'path'        => '/agents/{id}',
	'controller'  => 'ApiBundle:Agents:getAgent',
	'methods'     => array('GET'),
));

$collection->create('api_agents_delete', array(
	'path'        => '/agents/{id}/delete',
	'controller'  => 'ApiBundle:Agents:deleteAgent',
	'defaults'    => array('mode' => 'delete'),
	'methods'     => array('DELETE'),
));

$collection->create('api_agents_deletetouse', array(
	'path'        => '/agents/{id}/delete/to-user',
	'controller'  => 'ApiBundle:Agents:deleteAgent',
	'defaults'    => array('mode' => 'user'),
	'methods'     => array('DELETE'),
));

$collection->create('api_agents_save', array(
	'path'        => '/agents/{id}',
	'controller'  => 'ApiBundle:Agents:saveAgent',
	'methods'     => array('POST'),
));

$collection->create('api_agents_save_profile', array(
	'path'        => '/agents/{id}/profile',
	'controller'  => 'ApiBundle:Agents:saveAgentProfile',
	'methods'     => array('POST'),
));

$collection->create('api_agents_resetpassword', array(
	'path'        => '/agents/{id}/reset-password',
	'controller'  => 'ApiBundle:Agents:resetPassword',
	'methods'     => array('POST'),
));

$collection->create('api_agents_getlogintoken', array(
	'path'        => '/agents/{id}/login-token',
	'controller'  => 'ApiBundle:Agents:generateLoginToken',
	'methods'     => array('GET'),
));

$collection->create('api_agents_create', array(
	'path'        => '/agents',
	'controller'  => 'ApiBundle:Agents:saveAgent',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_agents_create_bulk', array(
	'path'        => '/agents_bulk',
	'controller'  => 'ApiBundle:Agents:bulkCreateAgents',
	'methods'     => array('POST'),
));

$collection->create('api_agents_notifyprefs_gettables', array(
	'path'        => '/agents/{id}/notify-prefs/get-tables',
	'controller'  => 'ApiBundle:Agents:getNotifyPrefs',
	'methods'     => array('GET'),
));

$collection->create('api_agent_teams_list', array(
	'path'        => '/agent_teams',
	'controller'  => 'ApiBundle:AgentTeams:listTeams',
	'methods'     => array('GET'),
));

$collection->create('api_agent_teams_get', array(
	'path'        => '/agent_teams/{id}',
	'controller'  => 'ApiBundle:AgentTeams:getTeam',
	'methods'     => array('GET'),
));

$collection->create('api_agent_teams_update', array(
	'path'        => '/agent_teams/{id}',
	'controller'  => 'ApiBundle:AgentTeams:saveTeam',
	'methods'     => array('POST'),
));

$collection->create('api_agent_teams_create', array(
	'path'        => '/agent_teams',
	'controller'  => 'ApiBundle:AgentTeams:saveTeam',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_agent_teams_delete', array(
	'path'        => '/agent_teams/{id}',
	'controller'  => 'ApiBundle:AgentTeams:deleteTeam',
	'methods'     => array('DELETE'),
));

$collection->create('api_agentgroups_list', array(
	'path'        => '/agent_groups',
	'controller'  => 'ApiBundle:AgentGroups:list',
	'methods'     => array('GET'),
));

$collection->create('api_agentgroups_get', array(
	'path'        => '/agent_groups/{id}',
	'controller'  => 'ApiBundle:AgentGroups:getGroup',
	'methods'     => array('GET'),
));

$collection->create('api_agentgroups_save', array(
	'path'        => '/agent_groups/{id}',
	'controller'  => 'ApiBundle:AgentGroups:saveGroup',
	'methods'     => array('POST'),
));

$collection->create('api_agentgroups_create', array(
	'path'        => '/agent_groups',
	'controller'  => 'ApiBundle:AgentGroups:saveGroup',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_agentgroups_del', array(
	'path'        => '/agent_groups/{id}',
	'controller'  => 'ApiBundle:AgentGroups:deleteGroup',
	'methods'     => array('DELETE'),
));

$collection->create('api_agentgroups_enable', array(
	'path'        => '/agent_groups/{id}/enable',
	'controller'  => 'ApiBundle:AgentGroups:toggleGroup',
	'defaults'    => array('is_enabled' => true),
	'methods'     => array('POST'),
));

$collection->create('api_agentgroups_disable', array(
	'path'        => '/agent_groups/{id}/disable',
	'controller'  => 'ApiBundle:AgentGroups:toggleGroup',
	'defaults'    => array('is_enabled' => false),
	'methods'     => array('POST'),
));

$collection->create('api_agentgroups_getperms', array(
	'path'        => '/agent_groups/all/permissions',
	'controller'  => 'ApiBundle:AgentGroups:getAllPerms',
	'defaults'    => array(),
	'methods'     => array('GET'),
));

$collection->create('api_combiner', array(
	'path'        => '/api_caller',
	'controller'  => 'ApiBundle:ApiCombiner:get',
	'methods'     => array('GET'),
));

$collection->create('api_organizations', array(
	'path'        => '/organizations',
	'controller'  => 'ApiBundle:Organization:search',
	'methods'     => array('GET'),
));

$collection->create('api_organizations_post', array(
	'path'        => '/organizations',
	'controller'  => 'ApiBundle:Organization:newOrganization',
	'methods'     => array('POST'),
));

$collection->create('api_organizations_organization', array(
	'path'          => '/organizations/{organization_id}',
	'controller'    => 'ApiBundle:Organization:getOrganization',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_post', array(
	'path'          => '/organizations/{organization_id}',
	'controller'    => 'ApiBundle:Organization:postOrganization',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_delete', array(
	'path'          => '/organizations/{organization_id}',
	'controller'    => 'ApiBundle:Organization:deleteOrganization',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_organization_picture', array(
	'path'          => '/organizations/{organization_id}/picture',
	'controller'    => 'ApiBundle:Organization:getOrganizationPicture',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_picture_post', array(
	'path'          => '/organizations/{organization_id}/picture',
	'controller'    => 'ApiBundle:Organization:postOrganizationPicture',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_picture_delete', array(
	'path'          => '/organizations/{organization_id}/picture',
	'controller'    => 'ApiBundle:Organization:deleteOrganizationPicture',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_organization_activity_stream', array(
	'path'          => '/organizations/{organization_id}/activity-stream',
	'controller'    => 'ApiBundle:Organization:getOrganizationActivityStream',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_members', array(
	'path'          => '/organizations/{organization_id}/members',
	'controller'    => 'ApiBundle:Organization:getOrganizationMembers',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_tickets', array(
	'path'          => '/organizations/{organization_id}/tickets',
	'controller'    => 'ApiBundle:Organization:getOrganizationTickets',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_chats', array(
	'path'          => '/organizations/{organization_id}/chats',
	'controller'    => 'ApiBundle:Organization:getOrganizationChats',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_slas', array(
	'path'          => '/organizations/{organization_id}/slas',
	'controller'    => 'ApiBundle:Organization:getOrganizationSlas',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_slas_post', array(
	'path'          => '/organizations/{organization_id}/slas',
	'controller'    => 'ApiBundle:Organization:postOrganizationSlas',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_sla', array(
	'path'          => '/organizations/{organization_id}/slas/{sla_id}',
	'controller'    => 'ApiBundle:Organization:getOrganizationSla',
	'requirements'  => array('organization_id' => '\\d+', 'sla_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_sla_delete', array(
	'path'          => '/organizations/{organization_id}/slas/{sla_id}',
	'controller'    => 'ApiBundle:Organization:deleteOrganizationSla',
	'requirements'  => array('organization_id' => '\\d+', 'sla_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_organization_billing_charges', array(
	'path'          => '/organizations/{organization_id}/billing-charges',
	'controller'    => 'ApiBundle:Organization:getOrganizationBillingCharges',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_email_domains', array(
	'path'          => '/organizations/{organization_id}/email-domains',
	'controller'    => 'ApiBundle:Organization:getOrganizationEmailDomains',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_email_domains_post', array(
	'path'          => '/organizations/{organization_id}/email-domains',
	'controller'    => 'ApiBundle:Organization:postOrganizationEmailDomains',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_email_domain', array(
	'path'          => '/organizations/{organization_id}/email-domains/{domain}',
	'controller'    => 'ApiBundle:Organization:getOrganizationEmailDomain',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_email_domain_move_users', array(
	'path'          => '/organizations/{organization_id}/email-domains/{domain}/move-users',
	'controller'    => 'ApiBundle:Organization:postOrganizationEmailDomainMoveUsers',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_email_domain_move_taken_users', array(
	'path'          => '/organizations/{organization_id}/email-domains/{domain}/move-taken-users',
	'controller'    => 'ApiBundle:Organization:postOrganizationEmailDomainMoveTakenUsers',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_email_domain_delete', array(
	'path'          => '/organizations/{organization_id}/email-domains/{domain}',
	'controller'    => 'ApiBundle:Organization:deleteOrganizationEmailDomain',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_organization_contact_details', array(
	'path'          => '/organizations/{organization_id}/contact-details',
	'controller'    => 'ApiBundle:Organization:getOrganizationContactDetails',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_contact_details_post', array(
	'path'          => '/organizations/{organization_id}/contact-details',
	'controller'    => 'ApiBundle:Organization:postOrganizationContactDetails',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_contact_detail', array(
	'path'          => '/organizations/{organization_id}/contact-details/{contact_id}',
	'controller'    => 'ApiBundle:Organization:getOrganizationContactDetail',
	'requirements'  => array('organization_id' => '\\d+', 'contact_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_contact_detail_delete', array(
	'path'          => '/organizations/{organization_id}/contact-details/{contact_id}',
	'controller'    => 'ApiBundle:Organization:deleteOrganizationContactDetail',
	'requirements'  => array('organization_id' => '\\d+', 'contact_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_organization_groups', array(
	'path'          => '/organizations/{organization_id}/groups',
	'controller'    => 'ApiBundle:Organization:getOrganizationGroups',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_groups_post', array(
	'path'          => '/organizations/{organization_id}/groups',
	'controller'    => 'ApiBundle:Organization:postOrganizationGroups',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_group', array(
	'path'          => '/organizations/{organization_id}/groups/{usergroup_id}',
	'controller'    => 'ApiBundle:Organization:getOrganizationGroup',
	'requirements'  => array('organization_id' => '\\d+', 'usergroup_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_group_delete', array(
	'path'          => '/organizations/{organization_id}/groups/{usergroup_id}',
	'controller'    => 'ApiBundle:Organization:deleteOrganizationGroup',
	'requirements'  => array('organization_id' => '\\d+', 'usergroup_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_organization_labels', array(
	'path'          => '/organizations/{organization_id}/labels',
	'controller'    => 'ApiBundle:Organization:getOrganizationLabels',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_labels_post', array(
	'path'          => '/organizations/{organization_id}/labels',
	'controller'    => 'ApiBundle:Organization:postOrganizationLabels',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_organizations_organization_label', array(
	'path'          => '/organizations/{organization_id}/labels/{label}',
	'controller'    => 'ApiBundle:Organization:getOrganizationLabel',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_organizations_organization_label_delete', array(
	'path'          => '/organizations/{organization_id}/labels/{label}',
	'controller'    => 'ApiBundle:Organization:deleteOrganizationLabel',
	'requirements'  => array('organization_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_organizations_fields', array(
	'path'        => '/organizations/fields',
	'controller'  => 'ApiBundle:Organization:getFields',
	'methods'     => array('GET'),
));

$collection->create('api_organizations_groups', array(
	'path'        => '/organizations/groups',
	'controller'  => 'ApiBundle:Organization:getGroups',
	'methods'     => array('GET'),
));

$collection->create('api_chats', array(
	'path'        => '/chats',
	'controller'  => 'ApiBundle:Chat:search',
	'methods'     => array('GET'),
));

$collection->create('api_chats_chat', array(
	'path'          => '/chats/{chat_id}',
	'controller'    => 'ApiBundle:Chat:getChat',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_chats_chat_post', array(
	'path'          => '/chats/{chat_id}',
	'controller'    => 'ApiBundle:Chat:postChat',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_chats_chat_leave', array(
	'path'          => '/chats/{chat_id}/leave',
	'controller'    => 'ApiBundle:Chat:leaveChat',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_chats_chat_end', array(
	'path'          => '/chats/{chat_id}/end',
	'controller'    => 'ApiBundle:Chat:endChat',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_chats_chat_messages', array(
	'path'          => '/chats/{chat_id}/messages',
	'controller'    => 'ApiBundle:Chat:getMessages',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_chats_chat_messages_post', array(
	'path'          => '/chats/{chat_id}/messages',
	'controller'    => 'ApiBundle:Chat:newMessage',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_chats_chat_participants', array(
	'path'          => '/chats/{chat_id}/participants',
	'controller'    => 'ApiBundle:Chat:getParticipants',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_chats_chat_participants_post', array(
	'path'          => '/chats/{chat_id}/participants',
	'controller'    => 'ApiBundle:Chat:postParticipants',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_chats_chat_participant', array(
	'path'          => '/chats/{chat_id}/participants/{person_id}',
	'controller'    => 'ApiBundle:Chat:getParticipant',
	'requirements'  => array('chat_id' => '\\d+', 'person_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_chats_chat_participant_delete', array(
	'path'          => '/chats/{chat_id}/participants/{person_id}',
	'controller'    => 'ApiBundle:Chat:deleteParticipant',
	'requirements'  => array('chat_id' => '\\d+', 'person_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_chats_chat_labels', array(
	'path'          => '/chats/{chat_id}/labels',
	'controller'    => 'ApiBundle:Chat:getChatLabels',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_chats_chat_labels_post', array(
	'path'          => '/chats/{chat_id}/labels',
	'controller'    => 'ApiBundle:Chat:postChatLabels',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_chats_chat_label', array(
	'path'          => '/chats/{chat_id}/labels/{label}',
	'controller'    => 'ApiBundle:Chat:getChatLabel',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_chats_chat_label_delete', array(
	'path'          => '/chats/{chat_id}/labels/{label}',
	'controller'    => 'ApiBundle:Chat:deleteChatLabel',
	'requirements'  => array('chat_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_downloads', array(
	'path'        => '/downloads',
	'controller'  => 'ApiBundle:Download:search',
	'methods'     => array('GET'),
));

$collection->create('api_downloads_post', array(
	'path'        => '/downloads',
	'controller'  => 'ApiBundle:Download:newDownload',
	'methods'     => array('POST'),
));

$collection->create('api_downloads_download', array(
	'path'          => '/downloads/{download_id}',
	'controller'    => 'ApiBundle:Download:getDownload',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_download_post', array(
	'path'          => '/downloads/{download_id}',
	'controller'    => 'ApiBundle:Download:postDownload',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_downloads_download_delete', array(
	'path'          => '/downloads/{download_id}',
	'controller'    => 'ApiBundle:Download:deleteDownload',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_downloads_download_comments', array(
	'path'          => '/downloads/{download_id}/comments',
	'controller'    => 'ApiBundle:Download:getDownloadComments',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_download_comments_new', array(
	'path'          => '/downloads/{download_id}/comments',
	'controller'    => 'ApiBundle:Download:newDownloadComment',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_downloads_download_comments_comment', array(
	'path'          => '/downloads/{download_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Download:getDownloadComment',
	'requirements'  => array('download_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_download_comments_comment_post', array(
	'path'          => '/downloads/{download_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Download:postDownloadComment',
	'requirements'  => array('download_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_downloads_download_comments_comment_delete', array(
	'path'          => '/downloads/{download_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Download:deleteDownloadComment',
	'requirements'  => array('download_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_downloads_download_labels', array(
	'path'          => '/downloads/{download_id}/labels',
	'controller'    => 'ApiBundle:Download:getDownloadLabels',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_download_labels_post', array(
	'path'          => '/downloads/{download_id}/labels',
	'controller'    => 'ApiBundle:Download:postDownloadLabels',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_downloads_download_label', array(
	'path'          => '/downloads/{download_id}/labels/{label}',
	'controller'    => 'ApiBundle:Download:getDownloadLabel',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_download_label_delete', array(
	'path'          => '/downloads/{download_id}/labels/{label}',
	'controller'    => 'ApiBundle:Download:deleteDownloadLabel',
	'requirements'  => array('download_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_downloads_validating_comments', array(
	'path'        => '/downloads/validating-comments',
	'controller'  => 'ApiBundle:Download:getValidatingComments',
	'methods'     => array('GET'),
));

$collection->create('api_downloads_categories', array(
	'path'        => '/downloads/categories',
	'controller'  => 'ApiBundle:Download:getCategories',
	'methods'     => array('GET'),
));

$collection->create('api_downloads_categories_post', array(
	'path'        => '/downloads/categories',
	'controller'  => 'ApiBundle:Download:postCategories',
	'methods'     => array('POST'),
));

$collection->create('api_downloads_category', array(
	'path'          => '/downloads/categories/{category_id}',
	'controller'    => 'ApiBundle:Download:getCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_category_post', array(
	'path'          => '/downloads/categories/{category_id}',
	'controller'    => 'ApiBundle:Download:postCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_downloads_category_delete', array(
	'path'          => '/downloads/categories/{category_id}',
	'controller'    => 'ApiBundle:Download:deleteCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_downloads_category_downloads', array(
	'path'          => '/downloads/categories/{category_id}/downloads',
	'controller'    => 'ApiBundle:Download:getCategoryDownloads',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_category_groups', array(
	'path'          => '/downloads/categories/{category_id}/groups',
	'controller'    => 'ApiBundle:Download:getCategoryGroups',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_category_groups_post', array(
	'path'          => '/downloads/categories/{category_id}/groups',
	'controller'    => 'ApiBundle:Download:postCategoryGroups',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_downloads_category_group', array(
	'path'          => '/downloads/categories/{category_id}/groups/{group_id}',
	'controller'    => 'ApiBundle:Download:getCategoryGroup',
	'requirements'  => array('category_id' => '\\d+', 'group_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_downloads_category_group_delete', array(
	'path'          => '/downloads/categories/{category_id}/groups/{group_id}',
	'controller'    => 'ApiBundle:Download:deleteCategoryGroup',
	'requirements'  => array('category_id' => '\\d+', 'group_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_news', array(
	'path'        => '/news',
	'controller'  => 'ApiBundle:News:search',
	'methods'     => array('GET'),
));

$collection->create('api_news_post', array(
	'path'        => '/news',
	'controller'  => 'ApiBundle:News:newNews',
	'methods'     => array('POST'),
));

$collection->create('api_news_news', array(
	'path'          => '/news/{news_id}',
	'controller'    => 'ApiBundle:News:getNews',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_news_post', array(
	'path'          => '/news/{news_id}',
	'controller'    => 'ApiBundle:News:postNews',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_news_news_delete', array(
	'path'          => '/news/{news_id}',
	'controller'    => 'ApiBundle:News:deleteNews',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_news_news_comments', array(
	'path'          => '/news/{news_id}/comments',
	'controller'    => 'ApiBundle:News:getNewsComments',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_news_comments_new', array(
	'path'          => '/news/{news_id}/comments',
	'controller'    => 'ApiBundle:News:newNewsComment',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_news_news_comments_comment', array(
	'path'          => '/news/{news_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:News:getNewsComment',
	'requirements'  => array('news_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_news_comments_comment_post', array(
	'path'          => '/news/{news_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:News:postNewsComment',
	'requirements'  => array('news_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_news_news_comments_comment_delete', array(
	'path'          => '/news/{news_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:News:deleteNewsComment',
	'requirements'  => array('news_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_news_news_labels', array(
	'path'          => '/news/{news_id}/labels',
	'controller'    => 'ApiBundle:News:getNewsLabels',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_news_labels_post', array(
	'path'          => '/news/{news_id}/labels',
	'controller'    => 'ApiBundle:News:postNewsLabels',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_news_news_label', array(
	'path'          => '/news/{news_id}/labels/{label}',
	'controller'    => 'ApiBundle:News:getNewsLabel',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_news_label_delete', array(
	'path'          => '/news/{news_id}/labels/{label}',
	'controller'    => 'ApiBundle:News:deleteNewsLabel',
	'requirements'  => array('news_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_news_validating_comments', array(
	'path'        => '/news/validating-comments',
	'controller'  => 'ApiBundle:News:getValidatingComments',
	'methods'     => array('GET'),
));

$collection->create('api_news_categories', array(
	'path'        => '/news/categories',
	'controller'  => 'ApiBundle:News:getCategories',
	'methods'     => array('GET'),
));

$collection->create('api_news_categories_post', array(
	'path'        => '/news/categories',
	'controller'  => 'ApiBundle:News:postCategories',
	'methods'     => array('POST'),
));

$collection->create('api_news_category', array(
	'path'          => '/news/categories/{category_id}',
	'controller'    => 'ApiBundle:News:getCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_category_post', array(
	'path'          => '/news/categories/{category_id}',
	'controller'    => 'ApiBundle:News:postCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_news_category_delete', array(
	'path'          => '/news/categories/{category_id}',
	'controller'    => 'ApiBundle:News:deleteCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_news_category_news', array(
	'path'          => '/news/categories/{category_id}/news',
	'controller'    => 'ApiBundle:News:getCategoryNews',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_category_groups', array(
	'path'          => '/news/categories/{category_id}/groups',
	'controller'    => 'ApiBundle:News:getCategoryGroups',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_category_groups_post', array(
	'path'          => '/news/categories/{category_id}/groups',
	'controller'    => 'ApiBundle:News:postCategoryGroups',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_news_category_group', array(
	'path'          => '/news/categories/{category_id}/groups/{group_id}',
	'controller'    => 'ApiBundle:News:getCategoryGroup',
	'requirements'  => array('category_id' => '\\d+', 'group_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_news_category_group_delete', array(
	'path'          => '/news/categories/{category_id}/groups/{group_id}',
	'controller'    => 'ApiBundle:News:deleteCategoryGroup',
	'requirements'  => array('category_id' => '\\d+', 'group_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb', array(
	'path'        => '/kb',
	'controller'  => 'ApiBundle:Kb:search',
	'methods'     => array('GET'),
));

$collection->create('api_kb_post', array(
	'path'        => '/kb',
	'controller'  => 'ApiBundle:Kb:newArticle',
	'methods'     => array('POST'),
));

$collection->create('api_kb_article', array(
	'path'          => '/kb/{article_id}',
	'controller'    => 'ApiBundle:Kb:getArticle',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_post', array(
	'path'          => '/kb/{article_id}',
	'controller'    => 'ApiBundle:Kb:postArticle',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_article_delete', array(
	'path'          => '/kb/{article_id}',
	'controller'    => 'ApiBundle:Kb:deleteArticle',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb_article_votes', array(
	'path'          => '/kb/{article_id}/votes',
	'controller'    => 'ApiBundle:Kb:getArticleVotes',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_comments', array(
	'path'          => '/kb/{article_id}/comments',
	'controller'    => 'ApiBundle:Kb:getArticleComments',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_comments_new', array(
	'path'          => '/kb/{article_id}/comments',
	'controller'    => 'ApiBundle:Kb:newArticleComment',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_article_comments_comment', array(
	'path'          => '/kb/{article_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Kb:getArticleComment',
	'requirements'  => array('article_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_comments_comment_post', array(
	'path'          => '/kb/{article_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Kb:postArticleComment',
	'requirements'  => array('article_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_article_comments_comment_delete', array(
	'path'          => '/kb/{article_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Kb:deleteArticleComment',
	'requirements'  => array('article_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb_article_attachments', array(
	'path'          => '/kb/{article_id}/attachments',
	'controller'    => 'ApiBundle:Kb:getArticleAttachments',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_attachments_post', array(
	'path'          => '/kb/{article_id}/attachments',
	'controller'    => 'ApiBundle:Kb:newArticleAttachment',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_article_attachment', array(
	'path'          => '/kb/{article_id}/attachments/{attachment_id}',
	'controller'    => 'ApiBundle:Kb:getArticleAttachment',
	'requirements'  => array('article_id' => '\\d+', 'attachment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_attachment_delete', array(
	'path'          => '/kb/{article_id}/attachments/{attachment_id}',
	'controller'    => 'ApiBundle:Kb:deleteArticleAttachment',
	'requirements'  => array('article_id' => '\\d+', 'attachment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb_article_labels', array(
	'path'          => '/kb/{article_id}/labels',
	'controller'    => 'ApiBundle:Kb:getArticleLabels',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_labels_post', array(
	'path'          => '/kb/{article_id}/labels',
	'controller'    => 'ApiBundle:Kb:postArticleLabels',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_article_label', array(
	'path'          => '/kb/{article_id}/labels/{label}',
	'controller'    => 'ApiBundle:Kb:getArticleLabel',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_article_label_delete', array(
	'path'          => '/kb/{article_id}/labels/{label}',
	'controller'    => 'ApiBundle:Kb:deleteArticleLabel',
	'requirements'  => array('article_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb_validating_comments', array(
	'path'        => '/kb/validating-comments',
	'controller'  => 'ApiBundle:Kb:getValidatingComments',
	'methods'     => array('GET'),
));

$collection->create('api_kb_categories', array(
	'path'        => '/kb/categories',
	'controller'  => 'ApiBundle:Kb:getCategories',
	'methods'     => array('GET'),
));

$collection->create('api_kb_categories_post', array(
	'path'        => '/kb/categories',
	'controller'  => 'ApiBundle:Kb:postCategories',
	'methods'     => array('POST'),
));

$collection->create('api_kb_category', array(
	'path'          => '/kb/categories/{category_id}',
	'controller'    => 'ApiBundle:Kb:getCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_category_post', array(
	'path'          => '/kb/categories/{category_id}',
	'controller'    => 'ApiBundle:Kb:postCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_category_delete', array(
	'path'          => '/kb/categories/{category_id}',
	'controller'    => 'ApiBundle:Kb:deleteCategory',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb_category_articles', array(
	'path'          => '/kb/categories/{category_id}/articles',
	'controller'    => 'ApiBundle:Kb:getCategoryArticles',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_category_groups', array(
	'path'          => '/kb/categories/{category_id}/groups',
	'controller'    => 'ApiBundle:Kb:getCategoryGroups',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_category_groups_post', array(
	'path'          => '/kb/categories/{category_id}/groups',
	'controller'    => 'ApiBundle:Kb:postCategoryGroups',
	'requirements'  => array('category_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_kb_category_group', array(
	'path'          => '/kb/categories/{category_id}/groups/{group_id}',
	'controller'    => 'ApiBundle:Kb:getCategoryGroup',
	'requirements'  => array('category_id' => '\\d+', 'group_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_kb_category_group_delete', array(
	'path'          => '/kb/categories/{category_id}/groups/{group_id}',
	'controller'    => 'ApiBundle:Kb:deleteCategoryGroup',
	'requirements'  => array('category_id' => '\\d+', 'group_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_kb_fields', array(
	'path'        => '/kb/fields',
	'controller'  => 'ApiBundle:Kb:getFields',
	'methods'     => array('GET'),
));

$collection->create('api_kb_products', array(
	'path'        => '/kb/products',
	'controller'  => 'ApiBundle:Kb:getProducts',
	'methods'     => array('GET'),
));

$collection->create('api_feedback', array(
	'path'        => '/feedback',
	'controller'  => 'ApiBundle:Feedback:search',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_post', array(
	'path'        => '/feedback',
	'controller'  => 'ApiBundle:Feedback:newFeedback',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_feedback', array(
	'path'          => '/feedback/{feedback_id}',
	'controller'    => 'ApiBundle:Feedback:getFeedback',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_post', array(
	'path'          => '/feedback/{feedback_id}',
	'controller'    => 'ApiBundle:Feedback:postFeedback',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_feedback_feedback_delete', array(
	'path'          => '/feedback/{feedback_id}',
	'controller'    => 'ApiBundle:Feedback:deleteFeedback',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_feedback_feedback_votes', array(
	'path'          => '/feedback/{feedback_id}/votes',
	'controller'    => 'ApiBundle:Feedback:getFeedbackVotes',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_comments', array(
	'path'          => '/feedback/{feedback_id}/comments',
	'controller'    => 'ApiBundle:Feedback:getFeedbackComments',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_comments_new', array(
	'path'          => '/feedback/{feedback_id}/comments',
	'controller'    => 'ApiBundle:Feedback:newFeedbackComment',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_feedback_feedback_comments_comment', array(
	'path'          => '/feedback/{feedback_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Feedback:getFeedbackComment',
	'requirements'  => array('feedback_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_comments_comment_post', array(
	'path'          => '/feedback/{feedback_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Feedback:postFeedbackComment',
	'requirements'  => array('feedback_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_feedback_feedback_comments_comment_delete', array(
	'path'          => '/feedback/{feedback_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Feedback:deleteFeedbackComment',
	'requirements'  => array('feedback_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_feedback_feedback_merge', array(
	'path'          => '/feedback/{feedback_id}/merge/{other_feedback_id}',
	'controller'    => 'ApiBundle:Feedback:mergeFeedback',
	'requirements'  => array('feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_feedback_feedback_attachments', array(
	'path'          => '/feedback/{feedback_id}/attachments',
	'controller'    => 'ApiBundle:Feedback:getFeedbackAttachments',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_attachments_post', array(
	'path'          => '/feedback/{feedback_id}/attachments',
	'controller'    => 'ApiBundle:Feedback:newFeedbackAttachment',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_feedback_feedback_attachment', array(
	'path'          => '/feedback/{feedback_id}/attachments/{attachment_id}',
	'controller'    => 'ApiBundle:Feedback:getFeedbackAttachment',
	'requirements'  => array('feedback_id' => '\\d+', 'attachment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_attachment_delete', array(
	'path'          => '/feedback/{feedback_id}/attachments/{attachment_id}',
	'controller'    => 'ApiBundle:Feedback:deleteFeedbackAttachment',
	'requirements'  => array('feedback_id' => '\\d+', 'attachment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_feedback_feedback_labels', array(
	'path'          => '/feedback/{feedback_id}/labels',
	'controller'    => 'ApiBundle:Feedback:getFeedbackLabels',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_labels_post', array(
	'path'          => '/feedback/{feedback_id}/labels',
	'controller'    => 'ApiBundle:Feedback:postFeedbackLabels',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_feedback_feedback_label', array(
	'path'          => '/feedback/{feedback_id}/labels/{label}',
	'controller'    => 'ApiBundle:Feedback:getFeedbackLabel',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_feedback_feedback_label_delete', array(
	'path'          => '/feedback/{feedback_id}/labels/{label}',
	'controller'    => 'ApiBundle:Feedback:deleteFeedbackLabel',
	'requirements'  => array('feedback_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_feedback_validating_comments', array(
	'path'        => '/feedback/validating-comments',
	'controller'  => 'ApiBundle:Feedback:getValidatingComments',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_categories', array(
	'path'        => '/feedback/categories',
	'controller'  => 'ApiBundle:Feedback:getCategories',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_status_categories', array(
	'path'        => '/feedback/status-categories',
	'controller'  => 'ApiBundle:Feedback:getStatusCategories',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_user_categories', array(
	'path'        => '/feedback/user-categories',
	'controller'  => 'ApiBundle:Feedback:getUserCategories',
	'methods'     => array('GET'),
));

$collection->create('api_tasks', array(
	'path'        => '/tasks',
	'controller'  => 'ApiBundle:Task:search',
	'methods'     => array('GET'),
));

$collection->create('api_tasks_post', array(
	'path'        => '/tasks',
	'controller'  => 'ApiBundle:Task:newTask',
	'methods'     => array('POST'),
));

$collection->create('api_tasks_task', array(
	'path'          => '/tasks/{task_id}',
	'controller'    => 'ApiBundle:Task:getTask',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_post', array(
	'path'          => '/tasks/{task_id}',
	'controller'    => 'ApiBundle:Task:postTask',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tasks_task_delete', array(
	'path'          => '/tasks/{task_id}',
	'controller'    => 'ApiBundle:Task:deleteTask',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tasks_task_associations', array(
	'path'          => '/tasks/{task_id}/associations',
	'controller'    => 'ApiBundle:Task:getTaskAssociations',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_associations_post', array(
	'path'          => '/tasks/{task_id}/associations',
	'controller'    => 'ApiBundle:Task:postTaskAssociations',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tasks_task_associated_item', array(
	'path'          => '/tasks/{task_id}/associations/{assoc_id}',
	'controller'    => 'ApiBundle:Task:getTaskAssociation',
	'requirements'  => array('task_id' => '\\d+', 'assoc_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_comments', array(
	'path'          => '/tasks/{task_id}/comments',
	'controller'    => 'ApiBundle:Task:getTaskComments',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_comments_post', array(
	'path'          => '/tasks/{task_id}/comments',
	'controller'    => 'ApiBundle:Task:postTaskComments',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tasks_task_comment', array(
	'path'          => '/tasks/{task_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Task:getTaskComment',
	'requirements'  => array('task_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_associated_item_delete', array(
	'path'          => '/tasks/{task_id}/comments/{comment_id}',
	'controller'    => 'ApiBundle:Task:deleteTaskComment',
	'requirements'  => array('task_id' => '\\d+', 'comment_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_tasks_task_labels', array(
	'path'          => '/tasks/{task_id}/labels',
	'controller'    => 'ApiBundle:Task:getTaskLabels',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_labels_post', array(
	'path'          => '/tasks/{task_id}/labels',
	'controller'    => 'ApiBundle:Task:postTaskLabels',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_tasks_task_label', array(
	'path'          => '/tasks/{task_id}/labels/{label}',
	'controller'    => 'ApiBundle:Task:getTaskLabel',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_tasks_task_label_delete', array(
	'path'          => '/tasks/{task_id}/labels/{label}',
	'controller'    => 'ApiBundle:Task:deleteTaskLabel',
	'requirements'  => array('task_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_glossary', array(
	'path'        => '/glossary',
	'controller'  => 'ApiBundle:Glossary:list',
	'methods'     => array('GET'),
));

$collection->create('api_glossary_lookup', array(
	'path'        => '/glossary/lookup',
	'controller'  => 'ApiBundle:Glossary:lookup',
	'methods'     => array('GET'),
));

$collection->create('api_glossary_post', array(
	'path'        => '/glossary',
	'controller'  => 'ApiBundle:Glossary:newWord',
	'methods'     => array('POST'),
));

$collection->create('api_glossary_word', array(
	'path'          => '/glossary/{word_id}',
	'controller'    => 'ApiBundle:Glossary:getWord',
	'requirements'  => array('word_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_glossary_word_delete', array(
	'path'          => '/glossary/{word_id}',
	'controller'    => 'ApiBundle:Glossary:deleteWord',
	'requirements'  => array('word_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_glossary_definition', array(
	'path'          => '/glossary/definitions/{definition_id}',
	'controller'    => 'ApiBundle:Glossary:getDefinition',
	'requirements'  => array('definition_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('api_glossary_definition_post', array(
	'path'          => '/glossary/definitions/{definition_id}',
	'controller'    => 'ApiBundle:Glossary:postDefinition',
	'requirements'  => array('definition_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('api_glossary_definition_delete', array(
	'path'          => '/glossary/definitions/{definition_id}',
	'controller'    => 'ApiBundle:Glossary:deleteDefinition',
	'requirements'  => array('definition_id' => '\\d+'),
	'methods'       => array('DELETE'),
));

$collection->create('api_dismiss_activity', array(
	'path'        => '/activity/dismiss',
	'controller'  => 'ApiBundle:Activity:dismiss',
	'methods'     => array('POST'),
));

$collection->create('api_get_activity', array(
	'path'          => '/activity/{since}',
	'controller'    => 'ApiBundle:Activity:getActivity',
	'requirements'  => array('since' => '\\d+'),
	'methods'       => array('GET'),
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
		'path'        => "/{$info['route_url']}_labels",
		'controller'  => "ApiBundle:{$info['controller']}:list",
		'methods'     => array('GET'),
	));

	$collection->create("api_{$info['route']}_labels_get", array(
		'path'        => "/{$info['route_url']}_labels/get",
		'controller'  => "ApiBundle:{$info['controller']}:get",
		'methods'     => array('GET'),
	));

	$collection->create("api_{$info['route']}_labels_save", array(
		'path'        => "/{$info['route_url']}_labels/save",
		'controller'  => "ApiBundle:{$info['controller']}:save",
		'methods'     => array('POST'),
	));

	$collection->create("api_{$info['route']}_labels_add", array(
		'path'        => "/{$info['route_url']}_labels",
		'controller'  => "ApiBundle:{$info['controller']}:add",
		'methods'     => array('POST'),
	));

	$collection->create("api_{$info['route']}_labels_remove", array(
		'path'        => "/{$info['route_url']}_labels",
		'controller'  => "ApiBundle:{$info['controller']}:remove",
		'methods'     => array('DELETE'),
	));
}

########################################################################################################################
# Round Robin
########################################################################################################################

$collection->create('api_roundrobins_list', array(
	'path'        => '/round_robin',
	'controller'  => 'ApiBundle:RoundRobin:list',
	'methods'     => array('GET'),
));

$collection->create('api_roundrobins_settings', array(
	'path'        => '/round_robin/settings',
	'controller'  => 'ApiBundle:RoundRobin:settings',
	'methods'     => array('GET', 'PUT'),
));

$collection->create('api_roundrobins_triggers', array(
	'path'        => '/round_robin/triggers/{id}',
	'controller'  => 'ApiBundle:RoundRobin:checkTriggers',
	'defaults'    => array('id' => null),
	'methods'     => array('GET'),
));

$collection->create('api_roundrobins_get', array(
	'path'        => '/round_robin/{id}',
	'controller'  => 'ApiBundle:RoundRobin:get',
	'methods'     => array('GET'),
));

$collection->create('api_roundrobins_set', array(
	'path'        => '/round_robin/{id}',
	'controller'  => 'ApiBundle:RoundRobin:set',
	'methods'     => array('POST', 'PUT'),
	'defaults'    => array('id' => 0),
));

$collection->create('api_roundrobins_delete', array(
	'path'        => '/round_robin/{id}',
	'controller'  => 'ApiBundle:RoundRobin:delete',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Start Settings
########################################################################################################################

$collection->create('api_startsettings_set', array(
	'path'        => '/start-settings',
	'controller'  => 'ApiBundle:Settings:setStartSettings',
	'methods'     => array('POST'),
));

$collection->create('api_startsettings_setinitial', array(
	'path'        => '/start-settings/set-initial',
	'controller'  => 'ApiBundle:Settings:setDoneInitial',
	'methods'     => array('POST'),
));

########################################################################################################################
# Settings
########################################################################################################################

$collection->create('api_settings_values_get', array(
	'path'        => '/settings/values/{name}',
	'controller'  => 'ApiBundle:Settings:getValue',
	'methods'     => array('GET'),
));

$collection->create('api_settings_values_set', array(
	'path'        => '/settings/values/{name}',
	'controller'  => 'ApiBundle:Settings:setValue',
	'methods'     => array('POST'),
));

########################################################################################################################
# Portal App Settings
########################################################################################################################

$collection->create('api_settings_portalapps', array(
	'path'        => '/settings/portal/{app}',
	'controller'  => 'ApiBundle:Settings:portalAppSettings',
	'methods'     => array('GET'),
));

$collection->create('api_settings_portalapps_save', array(
	'path'        => '/settings/portal/{app}',
	'controller'  => 'ApiBundle:Settings:savePortalAppSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# Server Settings
########################################################################################################################

$collection->create('api_server_settings', array(
	'path'        => '/server_settings',
	'controller'  => 'ApiBundle:Settings:serverSettings',
	'methods'     => array('GET'),
));

$collection->create('api_server_settings_save', array(
	'path'        => '/server_settings',
	'controller'  => 'ApiBundle:Settings:saveServerSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# General Settings
########################################################################################################################

$collection->create('api_general_settings', array(
	'path'        => '/general_settings',
	'controller'  => 'ApiBundle:Settings:generalSettings',
	'methods'     => array('GET'),
));

$collection->create('api_general_settings_save', array(
	'path'        => '/general_settings',
	'controller'  => 'ApiBundle:Settings:saveGeneralSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# Registration Settings
########################################################################################################################

$collection->create('api_reg_settings', array(
	'path'        => '/registration_settings',
	'controller'  => 'ApiBundle:Settings:registrationSettings',
	'methods'     => array('GET'),
));

$collection->create('api_reg_settings_save', array(
	'path'        => '/registration_settings',
	'controller'  => 'ApiBundle:Settings:saveRegistrationSettings',
	'methods'     => array('POST'),
));

$collection->create('api_pass_settings', array(
	'path'        => '/password_settings',
	'controller'  => 'ApiBundle:Settings:passwordSettings',
	'methods'     => array('GET'),
));

$collection->create('api_pass_settings_save', array(
	'path'        => '/password_settings',
	'controller'  => 'ApiBundle:Settings:savePasswordSettings',
	'methods'     => array('POST'),
));


########################################################################################################################
# Portal Settings
########################################################################################################################

$collection->create('api_portal_settings', array(
	'path'        => '/portal_settings',
	'controller'  => 'ApiBundle:Settings:portalSettings',
	'methods'     => array('GET'),
));

$collection->create('api_portal_settings_save', array(
	'path'        => '/portal_settings',
	'controller'  => 'ApiBundle:Settings:savePortalSettings',
	'methods'     => array('POST'),
));

$collection->create('api_portal_settings_setfavicon', array(
	'path'         => '/portal_settings/favicon/{blob_id}/{blob_auth}',
	'controller'   => 'ApiBundle:Settings:saveCustomFavicon',
	'methods'      => array('POST'),
	'requirements' => array('blob_id' => '\\d+', 'blob_auth' => '[a-zA-Z0-9]+'),
));

########################################################################################################################
# Advanced Settings
########################################################################################################################

$collection->create('api_all_settings_raw', array(
	'path'        => '/all_settings_raw',
	'controller'  => 'ApiBundle:Settings:allSettingsRaw',
	'methods'     => array('GET'),
));

$collection->create('api_all_settings_raw_save', array(
	'path'        => '/all_settings_raw',
	'controller'  => 'ApiBundle:Settings:saveAllSettingsRaw',
	'methods'     => array('POST'),
));

########################################################################################################################
# Elastic Search
########################################################################################################################

$collection->create('api_elastic_settings', array(
	'path'        => '/elastic-search/settings',
	'controller'  => 'ApiBundle:ElasticSearch:getSettings',
	'methods'     => array('GET'),
));

$collection->create('api_elastic_settings_save', array(
	'path'        => '/elastic-search/settings',
	'controller'  => 'ApiBundle:ElasticSearch:saveSettings',
	'methods'     => array('POST'),
));

$collection->create('api_elastic_settings_test', array(
	'path'        => '/elastic-search/settings/test',
	'controller'  => 'ApiBundle:ElasticSearch:testSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# License
########################################################################################################################

$collection->create('api_dp_license', array(
	'path'        => '/dp_license',
	'controller'  => 'ApiBundle:License:getLicense',
	'methods'     => array('GET'),
));

$collection->create('api_dp_license_save', array(
	'path'        => '/dp_license',
	'controller'  => 'ApiBundle:License:setLicense',
	'methods'     => array('POST'),
));

$collection->create('api_dp_keyfile', array(
	'path'         => '/dp_license/keyfile.{_format}',
	'controller'   => 'ApiBundle:License:downloadKeyfile',
	'methods'      => array('GET'),
	'requirements' => array('_format' => 'txt|json'),
));

$collection->create('api_dp_license_supportrequest', array(
	'path'         => '/dp_license/support-request',
	'controller'   => 'ApiBundle:License:sendSupportRequest',
	'methods'      => array('POST'),
));

$collection->create('api_dp_license_versioninfo', array(
	'path'         => '/dp_license/version-info',
	'controller'   => 'ApiBundle:License:getVersionInfo',
	'methods'      => array('GET'),
));

$collection->create('api_dp_license_latestversion', array(
	'path'         => '/dp_license/latest-version-info',
	'controller'   => 'ApiBundle:License:getLatestVersion',
	'methods'      => array('GET'),
));

$collection->create('api_dp_license_news', array(
	'path'         => '/dp_license/news',
	'controller'   => 'ApiBundle:License:getNews',
	'methods'      => array('GET'),
));

########################################################################################################################
# Ticket Settings
########################################################################################################################

$collection->create('api_ticket_settings', array(
	'path'        => '/ticket_settings',
	'controller'  => 'ApiBundle:Settings:ticketSettings',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_settings_save', array(
	'path'        => '/ticket_settings',
	'controller'  => 'ApiBundle:Settings:saveTicketSettings',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_fwd_settings', array(
	'path'        => '/ticket_settings/fwd',
	'controller'  => 'ApiBundle:Settings:ticketFwdSettings',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_fwd_settings_save', array(
	'path'        => '/ticket_settings/fwd',
	'controller'  => 'ApiBundle:Settings:saveTicketFwdSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# Reg Settings
########################################################################################################################

$collection->create('api_registration_settings', array(
	'path'        => '/registraton_settings',
	'controller'  => 'ApiBundle:Settings:registrationSettings',
	'methods'     => array('GET'),
));

$collection->create('api_registration_settings_save', array(
	'path'        => '/registraton_settings',
	'controller'  => 'ApiBundle:Settings:saveRegistrationSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# Products
########################################################################################################################

$collection->create('api_products', array(
	'path'        => '/products',
	'controller'  => 'ApiBundle:TicketFields:listPriorities',
	'methods'     => array('GET'),
));

########################################################################################################################
# Ticket Departments
########################################################################################################################

$collection->create('api_ticket_deps', array(
	'path'        => '/ticket_deps',
	'controller'  => 'ApiBundle:TicketDeps:list',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_deps_create', array(
	'path'        => '/ticket_deps',
	'controller'  => 'ApiBundle:TicketDeps:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_ticket_deps_order', array(
	'path'        => '/ticket_deps/display_order',
	'controller'  => 'ApiBundle:TicketDeps:saveDisplayOrder',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_deps_settings', array(
	'path'        => '/ticket_deps/settings',
	'controller'  => 'ApiBundle:TicketDeps:getSettings',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_deps_settingssave', array(
	'path'        => '/ticket_deps/settings',
	'controller'  => 'ApiBundle:TicketDeps:saveSettings',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_deps_get', array(
	'path'        => '/ticket_deps/{id}',
	'controller'  => 'ApiBundle:TicketDeps:get',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_deps_save', array(
	'path'        => '/ticket_deps/{id}',
	'controller'  => 'ApiBundle:TicketDeps:save',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_deps_remove', array(
	'path'        => '/ticket_deps/{id}',
	'controller'  => 'ApiBundle:TicketDeps:remove',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Ticket Layouts
########################################################################################################################

$collection->create('api_ticket_layout_get', array(
	'path'         => '/ticket_layouts/{dep_id}',
	'controller'   => 'ApiBundle:TicketLayouts:get',
	'requirements' => array('dep_id' => '\\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_ticket_layout_getdefault', array(
	'path'         => '/ticket_layouts/default',
	'controller'   => 'ApiBundle:TicketLayouts:get',
	'defaults'     => array('dep_id' => '0'),
	'methods'      => array('GET'),
));

$collection->create('api_ticket_layout_save', array(
	'path'         => '/ticket_layouts/{dep_id}',
	'controller'   => 'ApiBundle:TicketLayouts:save',
	'requirements' => array('dep_id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_layout_delete', array(
	'path'         => '/ticket_layouts/{dep_id}',
	'controller'   => 'ApiBundle:TicketLayouts:delete',
	'requirements' => array('dep_id' => '\\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_ticket_layout_savedefault', array(
	'path'        => '/ticket_layouts/default',
	'controller'  => 'ApiBundle:TicketLayouts:save',
	'defaults'    => array('dep_id' => '0'),
	'methods'     => array('POST'),
));

$collection->create('api_ticket_layout_stats', array(
	'path'        => '/ticket_layouts/stats',
	'controller'  => 'ApiBundle:TicketLayouts:getLayoutStats',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_layout_field_status', array(
	'path'        => '/ticket_layouts/fields/{field_id}',
	'controller'  => 'ApiBundle:TicketLayouts:getFieldStatus',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_layout_field_status_save', array(
	'path'        => '/ticket_layouts/fields/{field_id}',
	'controller'  => 'ApiBundle:TicketLayouts:saveFieldStatus',
	'methods'     => array('POST'),
));

########################################################################################################################
# Products
########################################################################################################################

$collection->create('api_products', array(
	'path'        => '/ticket_prods',
	'controller'  => 'ApiBundle:TicketFields:listProducts',
	'methods'     => array('GET'),
));

$collection->create('api_products_save', array(
	'path'        => '/ticket_prods',
	'controller'  => 'ApiBundle:TicketFields:saveProducts',
	'methods'     => array('POST'),
));

########################################################################################################################
# Ticket Categoriesw
########################################################################################################################

$collection->create('api_ticket_cats', array(
	'path'        => '/ticket_cats',
	'controller'  => 'ApiBundle:TicketFields:listCategories',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_cats_save', array(
	'path'        => '/ticket_cats',
	'controller'  => 'ApiBundle:TicketFields:saveCategories',
	'methods'     => array('POST'),
));

########################################################################################################################
# Ticket Workflows
########################################################################################################################

$collection->create('api_ticket_works', array(
	'path'        => '/ticket_works',
	'controller'  => 'ApiBundle:TicketFields:listWorkflows',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_works_save', array(
	'path'        => '/ticket_works',
	'controller'  => 'ApiBundle:TicketFields:saveWorkflows',
	'methods'     => array('POST'),
));

########################################################################################################################
# Ticket Priorities
########################################################################################################################

$collection->create('api_ticket_pris', array(
	'path'        => '/ticket_pris',
	'controller'  => 'ApiBundle:TicketFields:listPriorities',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_pris_save', array(
	'path'        => '/ticket_pris',
	'controller'  => 'ApiBundle:TicketFields:savePriorities',
	'methods'     => array('POST'),
));

########################################################################################################################
# Ticket Statuses
########################################################################################################################

$collection->create('api_ticket_statuses_stats', array(
	'path'        => '/ticket_statuses/stats',
	'controller'  => 'ApiBundle:TicketStatuses:getStats',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_statuses_closed', array(
	'path'        => '/ticket_statuses/closed',
	'controller'  => 'ApiBundle:TicketStatuses:getClosedInfo',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_statuses_closed_savesettings', array(
	'path'        => '/ticket_statuses/closed/settings',
	'controller'  => 'ApiBundle:TicketStatuses:saveClosedSettings',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_statuses_closed_resetsearch', array(
	'path'        => '/ticket_statuses/closed/reset-search-tables',
	'controller'  => 'ApiBundle:TicketStatuses:resetSearchTables',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_statuses_deleted', array(
	'path'        => '/ticket_statuses/deleted',
	'controller'  => 'ApiBundle:TicketStatuses:getDeletedInfo',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_statuses_deleted_purge', array(
	'path'        => '/ticket_statuses/deleted/purge',
	'controller'  => 'ApiBundle:TicketStatuses:purgeDeleted',
	'methods'     => array('DELETE'),
));

$collection->create('api_ticket_statuses_deleted_savesettings', array(
	'path'        => '/ticket_statuses/deleted/settings',
	'controller'  => 'ApiBundle:TicketStatuses:saveDeletedSettings',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_statuses_spam', array(
	'path'        => '/ticket_statuses/spam',
	'controller'  => 'ApiBundle:TicketStatuses:getSpamInfo',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_statuses_spam_purge', array(
	'path'        => '/ticket_statuses/spam/purge',
	'controller'  => 'ApiBundle:TicketStatuses:purgeSpam',
	'methods'     => array('DELETE'),
));

$collection->create('api_ticket_statuses_spam_savesettings', array(
	'path'        => '/ticket_statuses/spam/settings',
	'controller'  => 'ApiBundle:TicketStatuses:saveSpamSettings',
	'methods'     => array('POST'),
));

########################################################################################################################
# Ticket SLAs
########################################################################################################################

$collection->create('api_ticket_slas', array(
	'path'        => '/ticket_slas',
	'controller'  => 'ApiBundle:TicketSlas:list',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_slascreate', array(
	'path'        => '/ticket_slas',
	'controller'  => 'ApiBundle:TicketSlas:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_ticket_slasget', array(
	'path'        => '/ticket_slas/{id}',
	'controller'  => 'ApiBundle:TicketSlas:get',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_slasupdate', array(
	'path'        => '/ticket_slas/{id}',
	'controller'  => 'ApiBundle:TicketSlas:save',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_slasdelete', array(
	'path'        => '/ticket_slas/{id}',
	'controller'  => 'ApiBundle:TicketSlas:delete',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Ticket Urgencies
########################################################################################################################

$collection->create('api_ticket_urgencies', array(
	'path'        => '/ticket_urgencies',
	'controller'  => 'ApiBundle:TicketUrgencies:list',
	'methods'     => array('GET'),
));

########################################################################################################################
# Ticket Fields
########################################################################################################################

$collection->create('api_ticket_fields_get', array(
	'path'         => '/ticket_fields/{id}',
	'controller'   => 'ApiBundle:TicketFields:getCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_ticket_fields_create', array(
	'path'       => '/ticket_fields',
	'controller' => 'ApiBundle:TicketFields:saveCustomField',
	'defaults'   => array('id' => '0'),
	'methods'    => array('PUT'),
));

$collection->create('api_ticket_fields_save', array(
	'path'         => '/ticket_fields/{id}',
	'controller'   => 'ApiBundle:TicketFields:saveCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_fields_delete', array(
	'path'         => '/ticket_fields/{id}',
	'controller'   => 'ApiBundle:TicketFields:deleteCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_ticket_fields', array(
	'path'        => '/ticket_fields',
	'controller'  => 'ApiBundle:TicketFields:list',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_fields_setenabled', array(
	'path'        => '/ticket_fields/set-enabled/{field_id}/{is_enabled}',
	'controller'  => 'ApiBundle:TicketFields:toggleField',
	'methods'     => array('POST'),
));

########################################################################################################################
# Email Accounts
########################################################################################################################

$collection->create('api_emailaccounts', array(
	'path'        => '/email_accounts',
	'controller'  => 'ApiBundle:EmailAccounts:list',
	'methods'     => array('GET'),
));

$collection->create('api_emailaccounts_create', array(
	'path'        => '/email_accounts',
	'controller'  => 'ApiBundle:EmailAccounts:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_emailaccounts_test', array(
	'path'        => '/email_accounts/test-account',
	'controller'  => 'ApiBundle:EmailAccounts:testAccount',
	'methods'     => array('POST'),
));

$collection->create('api_emailaccounts_testoutgoing', array(
	'path'        => '/email_accounts/test-outgoing-account',
	'controller'  => 'ApiBundle:EmailAccounts:testOutgoingAccount',
	'methods'     => array('POST'),
));

$collection->create('api_emailaccounts_get', array(
	'path'        => '/email_accounts/{id}',
	'controller'  => 'ApiBundle:EmailAccounts:get',
	'methods'     => array('GET'),
));

$collection->create('api_emailaccounts_remove', array(
	'path'        => '/email_accounts/{id}',
	'controller'  => 'ApiBundle:EmailAccounts:remove',
	'methods'     => array('DELETE'),
));

$collection->create('api_emailaccounts_save', array(
	'path'        => '/email_accounts/{id}',
	'controller'  => 'ApiBundle:EmailAccounts:save',
	'methods'     => array('POST'),
));

########################################################################################################################
# Email Status
########################################################################################################################

$collection->create('api_emailstatus_sourcelist', array(
	'path'        => '/email_status/sources',
	'controller'  => 'ApiBundle:EmailStatus:listSources',
	'methods'     => array('GET'),
));

$collection->create('api_emailstatus_source_get', array(
	'path'         => '/email_status/sources/{id}',
	'controller'   => 'ApiBundle:EmailStatus:getSourceInfo',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_emailstatus_source_reprocess', array(
	'path'         => '/email_status/sources/{id}/reprocess',
	'controller'   => 'ApiBundle:EmailStatus:reprocessEmailSource',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_emailstatus_source_delete', array(
	'path'         => '/email_status/sources/{id}',
	'controller'   => 'ApiBundle:EmailStatus:deleteEmailSource',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_emailstatus_sendmaillist', array(
	'path'        => '/email_status/sendmail',
	'controller'  => 'ApiBundle:EmailStatus:listSendmail',
	'methods'     => array('GET'),
));

$collection->create('api_emailstatus_sendmail_delete', array(
	'path'         => '/email_status/sendmail/{id}',
	'controller'   => 'ApiBundle:EmailStatus:deleteSendmail',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_emailstatus_sendmail_resend', array(
	'path'         => '/email_status/sendmail/{id}/resend',
	'controller'   => 'ApiBundle:EmailStatus:resendSendmail',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_emailstatus_sendmail_get', array(
	'path'         => '/email_status/sendmail/{id}',
	'controller'   => 'ApiBundle:EmailStatus:getSendmailInfo',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('GET'),
));

########################################################################################################################
# Audit Log
########################################################################################################################

$collection->create('api_auditlog_list', array(
	'path'        => '/audit_log',
	'controller'  => 'ApiBundle:AuditLog:list',
	'methods'     => array('GET'),
));

$collection->create('api_auditlog_detail', array(
	'path'         => '/audit_log/{id}',
	'controller'   => 'ApiBundle:AuditLog:getDetail',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('GET'),
));

########################################################################################################################
# Ticket Triggers
########################################################################################################################

$collection->create('api_ticket_triggers_getcustomactions', array(
	'path'        => '/ticket_triggers/get-custom-actions',
	'controller'  => 'ApiBundle:TicketTriggers:getCustomActions',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_triggers_getspecial', array(
	'path'         => '/ticket_triggers/{special_type}/{id}',
	'controller'   => 'ApiBundle:TicketTriggers:get',
	'requirements' => array('special_type' => '(departments|departments_changed|email_accounts)', 'id' => '\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_ticket_triggers_updatespecial', array(
	'path'         => '/ticket_triggers/{special_type}/{id}',
	'controller'   => 'ApiBundle:TicketTriggers:save',
	'requirements' => array('special_type' => '(departments|departments_changed|email_accounts)', 'id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers', array(
	'path'         => '/ticket_triggers/{type}',
	'controller'   => 'ApiBundle:TicketTriggers:list',
	'requirements' => array('type' => '(all|newticket|newreply|update)'),
	'methods'      => array('GET'),
));

$collection->create('api_ticket_triggers_updateorder', array(
	'path'        => '/ticket_triggers/run_order',
	'controller'  => 'ApiBundle:TicketTriggers:saveRunOrder',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_triggers_create', array(
	'path'        => '/ticket_triggers',
	'controller'  => 'ApiBundle:TicketTriggers:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_ticket_triggers_get', array(
	'path'         => '/ticket_triggers/{id}',
	'controller'   => 'ApiBundle:TicketTriggers:get',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_ticket_triggers_update', array(
	'path'         => '/ticket_triggers/{id}',
	'controller'   => 'ApiBundle:TicketTriggers:save',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_delete', array(
	'path'         => '/ticket_triggers/{id}',
	'controller'   => 'ApiBundle:TicketTriggers:delete',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_ticket_triggers_enabletriggergroup', array(
	'path'         => '/ticket_triggers/{special_type}/enable',
	'defaults'     => array('is_enabled' => true),
	'controller'   => 'ApiBundle:TicketTriggers:toggleTriggerGroup',
	'requirements' => array('special_type' => '(departments|departments_changed|email_accounts)', 'id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_disabletriggergroup', array(
	'path'         => '/ticket_triggers/{special_type}/disable',
	'defaults'     => array('is_enabled' => false),
	'controller'   => 'ApiBundle:TicketTriggers:toggleTriggerGroup',
	'requirements' => array('special_type' => '(departments|departments_changed|email_accounts)', 'id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_enabletrigger', array(
	'path'         => '/ticket_triggers/{id}/enable',
	'defaults'     => array('is_enabled' => true),
	'controller'   => 'ApiBundle:TicketTriggers:toggleTrigger',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_ticket_triggers_disabletrigger', array(
	'path'         => '/ticket_triggers/{id}/disable',
	'defaults'     => array('is_enabled' => false),
	'controller'   => 'ApiBundle:TicketTriggers:toggleTrigger',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('POST'),
));

########################################################################################################################
# Ticket Escalations
########################################################################################################################

$collection->create('api_ticket_escalations', array(
	'path'         => '/ticket_escalations',
	'controller'   => 'ApiBundle:TicketEscalations:list',
	'methods'      => array('GET'),
));

$collection->create('api_ticket_escalations_updateorder', array(
	'path'        => '/ticket_escalations/run_order',
	'controller'  => 'ApiBundle:TicketEscalations:saveRunOrder',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_escalations_create', array(
	'path'        => '/ticket_escalations',
	'controller'  => 'ApiBundle:TicketEscalations:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_ticket_escalations_get', array(
	'path'        => '/ticket_escalations/{id}',
	'controller'  => 'ApiBundle:TicketEscalations:get',
	'methods'     => array('GET'),
));

$collection->create('api_ticket_escalations_update', array(
	'path'        => '/ticket_escalations/{id}',
	'controller'  => 'ApiBundle:TicketEscalations:save',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_escalations_delete', array(
	'path'        => '/ticket_escalations/{id}',
	'controller'  => 'ApiBundle:TicketEscalations:delete',
	'methods'     => array('DELETE'),
));

$collection->create('api_ticket_escalations_enable', array(
	'path'        => '/ticket_escalations/{id}/enable',
	'defaults'    => array('is_enabled' => true),
	'controller'  => 'ApiBundle:TicketEscalations:toggleEscalation',
	'methods'     => array('POST'),
));

$collection->create('api_ticket_escalations_disable', array(
	'path'        => '/ticket_escalations/{id}/disable',
	'defaults'    => array('is_enabled' => false),
	'controller'  => 'ApiBundle:TicketEscalations:toggleEscalation',
	'methods'     => array('POST'),
));

########################################################################################################################
# Ticket Filters
########################################################################################################################

$collection->create('api_ticket_filters', array(
	'path'         => '/ticket_filters',
	'controller'   => 'ApiBundle:TicketFilters:list',
	'methods'      => array('GET'),
));

$collection->create('api_ticket_filters_create', array(
	'path'         => '/ticket_filters',
	'defaults'     => array('id' => '0'),
	'controller'   => 'ApiBundle:TicketFilters:save',
	'methods'      => array('PUT'),
));

$collection->create('api_ticket_filters_savedisplayorder', array(
	'path'         => '/ticket_filters/display_order',
	'controller'   => 'ApiBundle:TicketFilters:saveDisplayOrder',
	'methods'      => array('POST'),
));

$collection->create('api_ticket_filters_get', array(
	'path'         => '/ticket_filters/{id}',
	'requirements' => array('id' => '\\d+'),
	'controller'   => 'ApiBundle:TicketFilters:get',
	'methods'      => array('GET'),
));

$collection->create('api_ticket_filters_save', array(
	'path'         => '/ticket_filters/{id}',
	'requirements' => array('id' => '\\d+'),
	'controller'   => 'ApiBundle:TicketFilters:save',
	'methods'      => array('POST'),
));

$collection->create('api_ticket_filters_delete', array(
	'path'         => '/ticket_filters/{id}',
	'requirements' => array('id' => '\\d+'),
	'controller'   => 'ApiBundle:TicketFilters:remove',
	'methods'      => array('DELETE'),
));

########################################################################################################################
# Ticket Macros
########################################################################################################################

$collection->create('api_ticket_macros', array(
	'path'         => '/ticket_macros',
	'controller'   => 'ApiBundle:TicketMacros:list',
	'methods'      => array('GET'),
));

$collection->create('api_ticket_macros_create', array(
	'path'         => '/ticket_macros',
	'defaults'     => array('id' => '0'),
	'controller'   => 'ApiBundle:TicketMacros:save',
	'methods'      => array('PUT'),
));

$collection->create('api_ticket_macros_get', array(
	'path'         => '/ticket_macros/{id}',
	'controller'   => 'ApiBundle:TicketMacros:get',
	'methods'      => array('GET'),
));

$collection->create('api_ticket_macros_save', array(
	'path'         => '/ticket_macros/{id}',
	'controller'   => 'ApiBundle:TicketMacros:save',
	'methods'      => array('POST'),
));

$collection->create('api_ticket_macros_delete', array(
	'path'         => '/ticket_macros/{id}',
	'controller'   => 'ApiBundle:TicketMacros:remove',
	'methods'      => array('DELETE'),
));

########################################################################################################################
# Feedback Statuses
########################################################################################################################

$collection->create('api_feedback_statuses', array(
	'path'       => '/feedback_statuses',
	'controller' => 'ApiBundle:FeedbackStatuses:list',
	'methods'    => array('GET'),
));


$collection->create('api_feedback_statuses_order', array(
	'path'        => '/feedback_statuses/display_order',
	'controller'  => 'ApiBundle:FeedbackStatuses:saveDisplayOrder',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_statuses_get', array(
	'path'        => '/feedback_statuses/{id}',
	'controller'  => 'ApiBundle:FeedbackStatuses:get',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_statuses_create', array(
	'path'        => '/feedback_statuses',
	'controller'  => 'ApiBundle:FeedbackStatuses:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_feedback_statuses_save', array(
	'path'        => '/feedback_statuses/{id}',
	'controller'  => 'ApiBundle:FeedbackStatuses:save',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_statuses_delete', array(
	'path'        => '/feedback_statuses/{id}',
	'controller'  => 'ApiBundle:FeedbackStatuses:remove',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Feedback Types
########################################################################################################################

$collection->create('api_feedback_types', array(
	'path'       => '/feedback_types',
	'controller' => 'ApiBundle:FeedbackTypes:list',
	'methods'    => array('GET'),
));

$collection->create('api_feedback_types_order', array(
	'path'        => '/feedback_types/display_order',
	'controller'  => 'ApiBundle:FeedbackTypes:saveDisplayOrder',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_types_get', array(
	'path'        => '/feedback_types/{id}',
	'controller'  => 'ApiBundle:FeedbackTypes:get',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_types_create', array(
	'path'        => '/feedback_types',
	'controller'  => 'ApiBundle:FeedbackTypes:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_feedback_types_save', array(
	'path'        => '/feedback_types/{id}',
	'controller'  => 'ApiBundle:FeedbackTypes:save',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_types_delete', array(
	'path'        => '/feedback_types/{id}',
	'controller'  => 'ApiBundle:FeedbackTypes:remove',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Feedback Categories
########################################################################################################################

$collection->create('api_feedback_categories', array(
	'path'       => '/feedback_categories',
	'controller' => 'ApiBundle:FeedbackCategories:list',
	'methods'    => array('GET'),
));

$collection->create('api_feedback_categories_order', array(
	'path'        => '/feedback_categories/display_order',
	'controller'  => 'ApiBundle:FeedbackCategories:saveDisplayOrder',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_categories_get', array(
	'path'        => '/feedback_categories/{id}',
	'controller'  => 'ApiBundle:FeedbackCategories:get',
	'methods'     => array('GET'),
));

$collection->create('api_feedback_categories_create', array(
	'path'        => '/feedback_categories',
	'controller'  => 'ApiBundle:FeedbackCategories:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_feedback_categories_save', array(
	'path'        => '/feedback_categories/{id}',
	'controller'  => 'ApiBundle:FeedbackCategories:save',
	'methods'     => array('POST'),
));

$collection->create('api_feedback_categories_delete', array(
	'path'        => '/feedback_categories/{id}',
	'controller'  => 'ApiBundle:FeedbackCategories:remove',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Twitter Setup
########################################################################################################################

$collection->create('api_twitter_setup', array(
	'path'       => '/twitter_setup',
	'controller' => 'ApiBundle:TwitterSetup:twitterSetup',
	'methods'    => array('GET'),
));

$collection->create('api_twitter_setup_save', array(
	'path'        => '/twitter_setup',
	'controller'  => 'ApiBundle:TwitterSetup:save',
	'methods'     => array('POST'),
));

########################################################################################################################
# Twitter Accounts
########################################################################################################################

$collection->create('api_twitter_accounts', array(
	'path'       => '/twitter_accounts',
	'controller' => 'ApiBundle:TwitterAccounts:list',
	'methods'    => array('GET'),
));

$collection->create('api_twitter_accounts_get', array(
	'path'        => '/twitter_accounts/{id}',
	'controller'  => 'ApiBundle:TwitterAccounts:get',
	'methods'     => array('GET'),
));

$collection->create('api_twitter_accounts_create', array(
	'path'        => '/twitter_accounts',
	'controller'  => 'ApiBundle:TwitterAccounts:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_twitter_accounts_save', array(
	'path'        => '/twitter_accounts/{id}',
	'controller'  => 'ApiBundle:TwitterAccounts:save',
	'methods'     => array('POST'),
));

$collection->create('api_twitter_accounts_delete', array(
	'path'        => '/twitter_accounts/{id}',
	'controller'  => 'ApiBundle:TwitterAccounts:remove',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Server Requirements
########################################################################################################################

$collection->create('api_server_reqs', array(
	'path'       => '/server_reqs',
	'controller' => 'ApiBundle:Server:getServerReqs',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server PHP Info
########################################################################################################################

$collection->create('api_server_php_info', array(
	'path'       => '/server_php_info',
	'controller' => 'ApiBundle:Server:getPhpInfo',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server Mysql Info
########################################################################################################################

$collection->create('api_server_mysql_info', array(
	'path'       => '/server_mysql_info',
	'controller' => 'ApiBundle:Server:getMysqlInfo',
	'methods'    => array('GET'),
));

$collection->create('api_server_mysql_info_schemadiff', array(
	'path'       => '/server_mysql_info/schema-diff',
	'controller' => 'ApiBundle:Server:getMysqlSchemaDiff',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server Mysql Status
########################################################################################################################

$collection->create('api_server_mysql_status', array(
	'path'       => '/server_mysql_status',
	'controller' => 'ApiBundle:Server:getMysqlStatus',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server Mysql Sort Order
########################################################################################################################

$collection->create('api_server_mysql_sort_order', array(
	'path'       => '/server_mysql_sort_order',
	'controller' => 'ApiBundle:Server:getMysqlSortOrder',
	'methods'    => array('GET'),
));

$collection->create('api_server_mysql_sort_order_save', array(
	'path'       => '/server_mysql_sort_order',
	'controller' => 'ApiBundle:Server:saveMysqlSortOrder',
	'methods'     => array('POST'),
));

$collection->create('api_server_mysql_sort_order_status', array(
	'path'       => '/server_mysql_sort_order_status',
	'controller' => 'ApiBundle:Server:getMysqlSortOrderStatus',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server
########################################################################################################################

$collection->create('api_server_cron_status', array(
	'path'        => '/server/cron-status',
	'controller'  => 'ApiBundle:Server:cronStatus',
	'methods'     => array('GET'),
));

$collection->create('api_server_error_status', array(
	'path'        => '/server/error-status',
	'controller'  => 'ApiBundle:Server:errorStatus',
	'methods'     => array('GET'),
));

$collection->create('api_server_apc_status', array(
	'path'        => '/server/apc-status',
	'controller'  => 'ApiBundle:Server:apcStatus',
	'methods'     => array('GET'),
));

$collection->create('api_server_autoupdate_begin', array(
	'path'        => '/server/updates/auto',
	'controller'  => 'ApiBundle:Server:beginAutomaticUpdate',
	'methods'     => array('PUT'),
));

$collection->create('api_server_autoupdate_abort', array(
	'path'        => '/server/updates/auto',
	'controller'  => 'ApiBundle:Server:abortAutomaticUpdate',
	'methods'     => array('DELETE'),
));

$collection->create('api_server_autoupdate_status', array(
	'path'        => '/server/updates/auto',
	'controller'  => 'ApiBundle:Server:getAutomaticUpdateStatus',
	'methods'     => array('GET'),
));

########################################################################################################################
# Server Error Logs
########################################################################################################################

$collection->create('api_server_error_logs', array(
	'path'       => '/server_error_logs',
	'controller' => 'ApiBundle:Server:listErrorLogs',
	'methods'    => array('GET'),
));

$collection->create('api_server_error_logs_get', array(
	'path'        => '/server_error_logs/{id}',
	'controller'  => 'ApiBundle:Server:getErrorLogs',
	'methods'     => array('GET'),
));

$collection->create('api_server_error_logs_delete', array(
	'path'       => '/server_error_logs',
	'controller' => 'ApiBundle:Server:removeErrorLogs',
	'methods'    => array('DELETE'),
));

########################################################################################################################
# Server Task Queue
########################################################################################################################

$collection->create('api_server_task_queue', array(
	'path'       => '/server_task_queue',
	'controller' => 'ApiBundle:Server:getTaskQueue',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server Cron
########################################################################################################################

$collection->create('api_server_cron', array(
	'path'       => '/server_cron',
	'controller' => 'ApiBundle:Server:listCron',
	'methods'    => array('GET'),
));

$collection->create('api_server_cron_logs', array(
	'path'       => '/server_cron/logs',
	'controller' => 'ApiBundle:Server:logsCron',
	'methods'    => array('GET'),
));

$collection->create('api_server_cron_logs_delete', array(
	'path'       => '/server_cron/logs',
	'controller' => 'ApiBundle:Server:removeCron',
	'methods'    => array('DELETE'),
));

########################################################################################################################
# Server File Uploads
########################################################################################################################

$collection->create('api_server_file_uploads', array(
	'path'       => '/server_file_uploads',
	'controller' => 'ApiBundle:Server:getFileUploads',
	'methods'    => array('GET'),
));

$collection->create('api_server_test_file_uploads', array(
	'path'       => '/server_file_uploads',
	'controller' => 'ApiBundle:Server:testFileUpload',
	'methods'    => array('POST'),
));

$collection->create('api_server_switch_file_uploads_storage', array(
	'path'       => '/server_file_uploads/switch',
	'controller' => 'ApiBundle:Server:switchFileStorage',
	'methods'    => array('POST'),
));

$collection->create('api_server_switch_file_uploads_storage_status', array(
	'path'       => '/server_file_uploads/switch_status',
	'controller' => 'ApiBundle:Server:switchFileStorageStatus',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server File Integrity
########################################################################################################################

$collection->create('api_server_file_check', array(
	'path'       => '/server_file_check',
	'controller' => 'ApiBundle:Server:listFileCheck',
	'methods'    => array('GET'),
));

$collection->create('api_server_file_check_get', array(
	'path'       => '/server_file_check/{id}',
	'controller' => 'ApiBundle:Server:getFileCheck',
	'methods'    => array('GET'),
));

########################################################################################################################
# Server Report File
########################################################################################################################

$collection->create('api_server_report_file_get', array(
	'path'       => '/server_report_file',
	'controller' => 'ApiBundle:Server:getReportFile',
	'methods'    => array('GET'),
));

$collection->create('api_server_report_file_check_save', array(
	'path'       => '/server_report_file/file_check_results',
	'controller' => 'ApiBundle:Server:saveFileCheckResults',
	'methods'    => array('POST'),
));

########################################################################################################################
# Chat Fields
########################################################################################################################

$collection->create('api_chat_fields_get', array(
	'path'         => '/chat_fields/{id}',
	'controller'   => 'ApiBundle:ChatFields:getCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_chat_fields_create', array(
	'path'       => '/chat_fields',
	'controller' => 'ApiBundle:ChatFields:saveCustomField',
	'defaults'   => array('id' => '0'),
	'methods'    => array('PUT'),
));

$collection->create('api_chat_fields_delete', array(
	'path'         => '/chat_fields/{id}',
	'controller'   => 'ApiBundle:ChatFields:deleteCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_chat_fields_save', array(
	'path'         => '/chat_fields/{id}',
	'controller'   => 'ApiBundle:ChatFields:saveCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_chat_fields', array(
	'path'        => '/chat_fields',
	'controller'  => 'ApiBundle:ChatFields:list',
	'methods'     => array('GET'),
));

$collection->create('api_chat_fields_setenabled', array(
	'path'        => '/chat_fields/set-enabled/{field_id}/{is_enabled}',
	'controller'  => 'ApiBundle:ChatFields:toggleField',
	'methods'     => array('POST'),
));

$collection->create('api_chat_fields_update_order', array(
	'path'        => '/chat_fields/display-order',
	'controller'  => 'ApiBundle:ChatFields:saveDisplayOrder',
	'methods'     => array('POST')
));

########################################################################################################################
# Chat Setup
########################################################################################################################

$collection->create('api_chat_setup', array(
	'path'         => '/chat_setup',
	'controller'   => 'ApiBundle:ChatSetup:chatSetup',
	'methods'      => array('GET'),
));

$collection->create('api_chat_setup_toggle', array(
	'path'         => '/chat_setup/toggle_chat/{is_enabled}',
	'controller'   => 'ApiBundle:ChatSetup:toggleChat',
	'methods'      => array('POST'),
));

########################################################################################################################
# Chat Departments
########################################################################################################################

$collection->create('api_chat_deps', array(
	'path'        => '/chat_deps',
	'controller'  => 'ApiBundle:ChatDeps:list',
	'methods'     => array('GET'),
));

$collection->create('api_chat_deps_create', array(
	'path'        => '/chat_deps',
	'controller'  => 'ApiBundle:ChatDeps:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_chat_deps_order', array(
	'path'        => '/chat_deps/display_order',
	'controller'  => 'ApiBundle:ChatDeps:saveDisplayOrder',
	'methods'     => array('POST'),
));

$collection->create('api_chat_deps_get', array(
	'path'        => '/chat_deps/{id}',
	'controller'  => 'ApiBundle:ChatDeps:get',
	'methods'     => array('GET'),
));

$collection->create('api_chat_deps_save', array(
	'path'        => '/chat_deps/{id}',
	'controller'  => 'ApiBundle:ChatDeps:save',
	'methods'     => array('POST'),
));

$collection->create('api_chat_deps_remove', array(
	'path'        => '/chat_deps/{id}',
	'controller'  => 'ApiBundle:ChatDeps:remove',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# Api Keys
########################################################################################################################

$collection->create('api_api_keys', array(
	'path'       => '/api_keys',
	'controller' => 'ApiBundle:ApiKeys:list',
	'methods'    => array('GET'),
));

$collection->create('api_api_keys_create', array(
	'path'        => '/api_keys',
	'controller'  => 'ApiBundle:ApiKeys:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_api_keys_get', array(
	'path'        => '/api_keys/{id}',
	'controller'  => 'ApiBundle:ApiKeys:get',
	'methods'     => array('GET'),
));

$collection->create('api_api_keys_save', array(
	'path'        => '/api_keys/{id}',
	'controller'  => 'ApiBundle:ApiKeys:save',
	'methods'     => array('POST'),
));

$collection->create('api_api_keys_delete', array(
	'path'        => '/api_keys/{id}',
	'controller'  => 'ApiBundle:ApiKeys:remove',
	'methods'     => array('DELETE'),
));

$collection->create('api_api_keys_regenerate', array(
	'path'        => '/api_keys/regenerate/{id}',
	'controller'  => 'ApiBundle:ApiKeys:regenerate',
	'methods'     => array('POST'),
));

########################################################################################################################
# CRM User Fields
########################################################################################################################

$collection->create('api_user_fields_get', array(
	'path'         => '/user_fields/{id}',
	'controller'   => 'ApiBundle:UserFields:getCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_user_fields_create', array(
	'path'       => '/user_fields',
	'controller' => 'ApiBundle:UserFields:saveCustomField',
	'defaults'   => array('id' => '0'),
	'methods'    => array('PUT'),
));

$collection->create('api_user_fields_save', array(
	'path'         => '/user_fields/{id}',
	'controller'   => 'ApiBundle:UserFields:saveCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_user_fields_delete', array(
	'path'         => '/user_fields/{id}',
	'controller'   => 'ApiBundle:UserFields:deleteCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_user_fields', array(
	'path'        => '/user_fields',
	'controller'  => 'ApiBundle:UserFields:list',
	'methods'     => array('GET'),
));

$collection->create('api_user_fields_setenabled', array(
	'path'        => '/user_fields/set-enabled/{field_id}/{is_enabled}',
	'controller'  => 'ApiBundle:UserFields:toggleField',
	'methods'     => array('POST'),
));

$collection->create('api_user_fields_update_order', array(
	'path'        => '/user_fields/display-order',
	'controller'  => 'ApiBundle:UserFields:saveDisplayOrder',
	'methods'     => array('POST'),
));

########################################################################################################################
# CRM Organization Fields
########################################################################################################################

$collection->create('api_org_fields_get', array(
	'path'         => '/org_fields/{id}',
	'controller'   => 'ApiBundle:OrgFields:getCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_org_fields_create', array(
	'path'       => '/org_fields',
	'controller' => 'ApiBundle:OrgFields:saveCustomField',
	'defaults'   => array('id' => '0'),
	'methods'    => array('PUT'),
));

$collection->create('api_org_fields_save', array(
	'path'         => '/org_fields/{id}',
	'controller'   => 'ApiBundle:OrgFields:saveCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_org_fields_delete', array(
	'path'         => '/org_fields/{id}',
	'controller'   => 'ApiBundle:OrgFields:deleteCustomField',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('DELTE'),
));

$collection->create('api_org_fields', array(
	'path'        => '/org_fields',
	'controller'  => 'ApiBundle:OrgFields:list',
	'methods'     => array('GET'),
));

$collection->create('api_org_fields_setenabled', array(
	'path'        => '/org_fields/set-enabled/{field_id}/{is_enabled}',
	'controller'  => 'ApiBundle:OrgFields:toggleField',
	'methods'     => array('POST'),
));

$collection->create('api_org_fields_update_order', array(
	'path'        => '/org_fields/display-order',
	'controller'  => 'ApiBundle:OrgFields:saveDisplayOrder',
	'methods'     => array('POST'),
));

########################################################################################################################
# CRM Banning
########################################################################################################################

$collection->create('api_banning', array(
	'path'        => '/banning',
	'controller'  => 'ApiBundle:Banning:list',
	'methods'     => array('GET'),
));

$collection->create('api_banning_email_export', array(
	'path'        => '/banning/export_emails',
	'controller'  => 'ApiBundle:Banning:exportEmails',
	'methods'     => array('GET'),
));

$collection->create('api_banning_email_import', array(
	'path'        => '/banning/import_emails',
	'controller'  => 'ApiBundle:Banning:importEmails',
	'methods'     => array('POST'),
));

$collection->create('api_banning_ip_create', array(
	'path'        => '/banning_ip',
	'controller'  => 'ApiBundle:Banning:saveIp',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_banning_email_create', array(
	'path'        => '/banning_email',
	'controller'  => 'ApiBundle:Banning:saveEmail',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_banning_ip_get', array(
	'path'        => '/banning_ip/{id}',
	'controller'  => 'ApiBundle:Banning:getIp',
	'methods'     => array('GET'),
));

$collection->create('api_banning_email_get', array(
	'path'        => '/banning_email/{id}',
	'controller'  => 'ApiBundle:Banning:getEmail',
	'methods'     => array('GET'),
));

$collection->create('api_banning_ip_save', array(
	'path'        => '/banning_ip/{id}',
	'controller'  => 'ApiBundle:Banning:saveIp',
	'methods'     => array('POST'),
));

$collection->create('api_banning_email_save', array(
	'path'        => '/banning_email/{id}',
	'controller'  => 'ApiBundle:Banning:saveEmail',
	'methods'     => array('POST'),
));


$collection->create('api_banning_ip_remove_all', array(
	'path'        => '/banning_ip',
	'controller'  => 'ApiBundle:Banning:removeIp',
	'methods'     => array('DELETE'),
	'defaults'    => array('id' => null),
));

$collection->create('api_banning_email_remove_all', array(
	'path'        => '/banning_email',
	'controller'  => 'ApiBundle:Banning:removeEmail',
	'methods'     => array('DELETE'),
	'defaults'    => array('id' => null),
));

$collection->create('api_banning_ip_remove', array(
	'path'        => '/banning_ip/{id}',
	'controller'  => 'ApiBundle:Banning:removeIp',
	'methods'     => array('DELETE'),
));

$collection->create('api_banning_email_remove', array(
	'path'        => '/banning_email/{id}',
	'controller'  => 'ApiBundle:Banning:removeEmail',
	'methods'     => array('DELETE'),
));

########################################################################################################################
# CRM User Groups
########################################################################################################################

$collection->create('api_user_groups_list', array(
	'path'        => '/user_groups',
	'controller'  => 'ApiBundle:Usergroups:list',
	'defaults'    => array('type' => 'user'),
	'methods'     => array('GET'),
));

$collection->create('api_usergroups_non_sys_list', array(
	'path'        => '/non_sys_usergroups',
	'controller'  => 'ApiBundle:Usergroups:list',
	'defaults'    => array('type' => 'non_sys_user'),
	'methods'     => array('GET'),
));

$collection->create('api_user_groups_get', array(
	'path'         => '/user_groups/{id}',
	'controller'   => 'ApiBundle:Usergroups:get',
	'requirements' => array('id' => '(\\d+|[a-z0-9_\.\-]+)'),
	'methods'      => array('GET'),
));

$collection->create('api_user_groups_delete', array(
	'path'         => '/user_groups/{id}',
	'controller'   => 'ApiBundle:Usergroups:delete',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_user_groups_create', array(
	'path'       => '/user_groups',
	'controller' => 'ApiBundle:Usergroups:save',
	'defaults'   => array('id' => '0'),
	'methods'    => array('PUT'),
));

$collection->create('api_user_groups_save', array(
	'path'         => '/user_groups/{id}',
	'controller'   => 'ApiBundle:Usergroups:save',
	'requirements' => array('id' => '(\\d+|[a-z0-9_\.\-]+)'),
	'methods'      => array('POST'),
));

########################################################################################################################
# CRM Import CSV
########################################################################################################################

$collection->create('api_import_csv_upload', array(
	'path'        => '/import_csv_upload',
	'controller'  => 'ApiBundle:CsvUpload:upload',
	'methods'     => array('POST'),
));

$collection->create('api_import_csv_import', array(
	'path'        => '/import_csv_import',
	'controller'  => 'ApiBundle:CsvUpload:import',
	'methods'     => array('POST'),
));

$collection->create('api_import_csv_status', array(
	'path'        => '/import_csv_status',
	'controller'  => 'ApiBundle:CsvUpload:status',
	'methods'     => array('GET'),
));

########################################################################################################################
# CRM User Rules
########################################################################################################################

$collection->create('api_user_rules', array(
	'path'       => '/user_rules',
	'controller' => 'ApiBundle:UserRules:list',
	'methods'    => array('GET'),
));

$collection->create('api_user_rules_create', array(
	'path'        => '/user_rules',
	'controller'  => 'ApiBundle:UserRules:save',
	'defaults'    => array('id' => '0'),
	'methods'     => array('PUT'),
));

$collection->create('api_user_rules_get', array(
	'path'        => '/user_rules/{id}',
	'controller'  => 'ApiBundle:UserRules:get',
	'methods'     => array('GET'),
));

$collection->create('api_user_rules_save', array(
	'path'        => '/user_rules/{id}',
	'controller'  => 'ApiBundle:UserRules:save',
	'methods'     => array('POST'),
));

$collection->create('api_user_rules_delete', array(
	'path'        => '/user_rules/{id}',
	'controller'  => 'ApiBundle:UserRules:remove',
	'methods'     => array('DELETE'),
));

$collection->create('api_user_rules_apply', array(
	'path'        => '/user_rules_apply/{id}/page_{page_id}',
	'controller'  => 'ApiBundle:UserRules:apply',
	'methods'     => array('GET'),
));

########################################################################################################################
# Login Logs
########################################################################################################################

$collection->create('api_login_logs', array(
	'path'         => '/login_logs/{agent_id}',
	'controller'   => 'ApiBundle:LoginLogs:list',
	'requirements' => array('agent_id' => '\\d+'),
	'defaults'     => array('agent_id' => '0'),
	'methods'      => array('GET'),
));

########################################################################################################################
# Languages
########################################################################################################################

$collection->create('api_langs', array(
	'path'        => '/langs',
	'controller'  => 'ApiBundle:Languages:list',
	'methods'     => array('GET'),
));

$collection->create('api_langs_masstickets', array(
	'path'        => '/langs/tools/mass-update-tickets',
	'controller'  => 'ApiBundle:Languages:massUpdateTickets',
	'methods'     => array('POST'),
));

$collection->create('api_langs_massusers', array(
	'path'        => '/langs/tools/mass-update-users',
	'controller'  => 'ApiBundle:Languages:massUpdateUsers',
	'methods'     => array('POST'),
));

$collection->create('api_langs_setdefault', array(
	'path'        => '/langs/{id}/set-default',
	'controller'  => 'ApiBundle:Languages:setDefaultLang',
	'methods'     => array('POST'),
));

$collection->create('api_langs_install', array(
	'path'         => '/langs/{id}/install',
	'controller'   => 'ApiBundle:Languages:installLang',
	'methods'      => array('POST'),
	'requirements' => array('id' => '[a-z]+')
));

$collection->create('api_langs_delete', array(
	'path'         => '/langs/{id}/uninstall',
	'controller'   => 'ApiBundle:Languages:uninstallLang',
	'methods'      => array('POST'),
	'requirements' => array('id' => '\d+|[a-z]+')
));

$collection->create('api_langs_getinfo', array(
	'path'         => '/langs/{id}',
	'controller'   => 'ApiBundle:Languages:getLang',
	'methods'      => array('GET'),
	'requirements' => array('id' => '\d+|[a-z]+')
));

$collection->create('api_langs_saveinfo', array(
	'path'         => '/langs/{id}',
	'controller'   => 'ApiBundle:Languages:saveLang',
	'methods'      => array('POST'),
	'requirements' => array('id' => '\d+|[a-z]+')
));

$collection->create('api_langs_savephrases', array(
	'path'         => '/langs/{id}/phrases',
	'controller'   => 'ApiBundle:Languages:savePhraseSet',
	'methods'      => array('POST'),
	'requirements' => array('id' => '\d+|[a-z]+')
));

$collection->create('api_langs_getphrasegroups', array(
	'path'        => '/langs/phrases-groups',
	'controller'  => 'ApiBundle:Languages:getPhraseGroups',
	'methods'     => array('GET'),
));

$collection->create('api_langs_getphrase_all', array(
	'path'         => '/langs/phrases/{phrase_id}',
	'controller'   => 'ApiBundle:Languages:getPhrase',
	'requirements' => array('phrase_id' => '[a-zA-Z0-9\-_\.]+'),
	'defaults'     => array('for_lang' => '-1'),
	'methods'      => array('GET'),
));

$collection->create('api_langs_getphrase', array(
	'path'         => '/langs/phrases/{phrase_id}/{for_lang}',
	'controller'   => 'ApiBundle:Languages:getPhrase',
	'defaults'     => array('for_lang' => '-1'),
	'requirements' => array('phrase_id' => '[a-zA-Z0-9\-_\.]+', 'for_lang' => '\d+|[a-z]+'),
	'methods'      => array('GET'),
));

$collection->create('api_langs_savephrase', array(
	'path'        => '/langs/phrases/{phrase_id}',
	'controller'  => 'ApiBundle:Languages:savePhrase',
	'methods'     => array('POST'),
));

$collection->create('api_langs_getphrases', array(
	'path'         => '/langs/{id}/{group_id}',
	'controller'   => 'ApiBundle:Languages:getPhrases',
	'methods'      => array('GET'),
	'requirements' => array('id' => '\d+|[a-z]+', 'group_id' => '[a-zA-Z0-9\-_\.]+')
));

########################################################################################################################
# Templates
########################################################################################################################

$collection->create('api_templates_getinfo', array(
	'path'        => '/templates-info',
	'controller'  => 'ApiBundle:Templates:getTemplateInfo',
	'methods'     => array('GET'),
));

$collection->create('api_templates_get', array(
	'path'        => '/templates/{name}',
	'controller'  => 'ApiBundle:Templates:getTemplate',
	'methods'     => array('GET'),
));

$collection->create('api_templates_update', array(
	'path'        => '/templates/{name}',
	'controller'  => 'ApiBundle:Templates:setTemplate',
	'methods'     => array('POST'),
));

$collection->create('api_templates_delete', array(
	'path'        => '/templates/{name}',
	'controller'  => 'ApiBundle:Templates:deleteTemplate',
	'methods'     => array('DELETE'),
));


########################################################################################################################
# Email Templates
########################################################################################################################

$collection->create('api_templates_email_getinfo', array(
	'path'        => '/email-templates-info',
	'controller'  => 'ApiBundle:Templates:getEmailTemplateInfo',
	'methods'     => array('GET'),
));

########################################################################################################################
# Save Log
########################################################################################################################

$collection->create('api_savelog_logjserror', array(
	'path'        => '/log-js-error',
	'controller'  => 'ApiBundle:SaveLog:logJsError',
	'methods'     => array('POST'),
));

########################################################################################################################
# Widget Selections
########################################################################################################################

$collection->create('api_widget_selections', array(
	'path'        => '/widget/selections',
	'controller'  => 'ApiBundle:WidgetSelections:get',
	'methods'     => array('GET'),
));

$collection->create('api_widget_selections_save', array(
	'path'        => '/widget/selections',
	'controller'  => 'ApiBundle:WidgetSelections:save',
	'methods'     => array('POST'),
));

########################################################################################################################
# Reports Overview
########################################################################################################################

$collection->create('api_reports_overview_get_data', array(
	'path'        => '/reports/overview/data/{type}',
	'controller'  => 'ApiBundle:ReportsOverview:getData',
	'methods'     => array('GET'),
));

$collection->create('api_reports_overview_update_stats', array(
	'path'        => '/reports/overview/get-stats/{type}',
	'controller'  => 'ApiBundle:ReportsOverview:getStats',
	'methods'     => array('GET'),
));

########################################################################################################################
# Report Builder
########################################################################################################################

$collection->create('api_reports_builder_list', array(
	'path'        => '/reports/builder',
	'controller'  => 'ApiBundle:ReportsBuilder:list',
	'methods'     => array('GET'),
));

$collection->create('api_reports_builder_list_custom', array(
	'path'        => '/reports/builder/custom',
	'controller'  => 'ApiBundle:ReportsBuilder:listCustom',
	'methods'     => array('GET'),
));

$collection->create('api_reports_builder_list_builtIn', array(
	'path'        => '/reports/builder/builtIn',
	'controller'  => 'ApiBundle:ReportsBuilder:listBuiltIn',
	'methods'     => array('GET'),
));

$collection->create('api_reports_builder_get_group_params', array(
	'path'        => '/reports/builder/group-params',
	'controller'  => 'ApiBundle:ReportsBuilder:getGroupParams',
	'methods'     => array('GET'),
));

$collection->create('api_reports_builder_get', array(
	'path'         => '/reports/builder/{id}',
	'controller'   => 'ApiBundle:ReportsBuilder:get',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('GET'),
));

$collection->create('api_reports_builder_delete', array(
	'path'         => '/reports/builder/{id}',
	'controller'   => 'ApiBundle:ReportsBuilder:delete',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('DELETE'),
));

$collection->create('api_reports_builder_create', array(
	'path'       => '/reports/builder',
	'controller' => 'ApiBundle:ReportsBuilder:save',
	'defaults'   => array('id' => '0'),
	'methods'    => array('PUT'),
));

$collection->create('api_reports_builder_save', array(
	'path'         => '/reports/builder/{id}',
	'controller'   => 'ApiBundle:ReportsBuilder:save',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_reports_builder_clone', array(
	'path'         => '/reports/builder/clone/{id}',
	'controller'   => 'ApiBundle:ReportsBuilder:clone',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_reports_builder_test', array(
	'path'         => '/reports/builder/test/{id}',
	'controller'   => 'ApiBundle:ReportsBuilder:test',
	'requirements' => array('id' => '\\d+'),
	'methods'      => array('POST'),
));

$collection->create('api_reports_builder_parse', array(
	'path'         => '/reports/builder/parse',
	'controller'   => 'ApiBundle:ReportsBuilder:parse',
	'methods'      => array('POST'),
));

$collection->create('api_reports_builder_download', array(
	'path'         => '/reports/builder/download/{id}/{type}',
	'controller'   => 'ApiBundle:ReportsBuilder:download',
	'methods'      => array('GET'),
));

########################################################################################################################
# Report Agent Activity
########################################################################################################################

$collection->create('api_reports_agent_activity_list', array(
	'path'        => '/reports/agent-activity/{agent_or_team_id}/{date}',
	'controller'  => 'ApiBundle:ReportsAgentActivity:list',
	'defaults'    => array('agent_or_team_id' => 'all', 'date' => ''),
	'methods'     => array('GET'),
));

########################################################################################################################
# Report Agent Hours
########################################################################################################################

$collection->create('api_reports_agent_hours_list', array(
	'path'        => '/reports/agent-hours/{date1}/{date2}',
	'controller'  => 'ApiBundle:ReportsAgentHours:list',
	'defaults'    => array('date1' => '', 'date2' => ''),
	'methods'     => array('GET'),
));

########################################################################################################################
# Report Ticket Satisfaction
########################################################################################################################

$collection->create('api_reports_ticket_satisfaction_list', array(
	'path'        => '/reports/ticket-satisfaction/{page}',
	'controller'  => 'ApiBundle:ReportsTicketSatisfaction:list',
	'defaults'    => array('page' => '0'),
	'methods'     => array('GET'),
));

$collection->create('api_reports_ticket_satisfaction_summary', array(
	'path'        => '/reports/ticket-satisfaction/summary/{date}',
	'controller'  => 'ApiBundle:ReportsTicketSatisfaction:summary',
	'defaults'    => array('date' => ''),
	'methods'     => array('GET'),
));

########################################################################################################################
# Report Billing
########################################################################################################################

$collection->create('api_reports_billing_get', array(
	'path'         => '/reports/billing/{id}',
	'controller'   => 'ApiBundle:ReportsBilling:get',
	'methods'      => array('GET'),
));

########################################################################################################################
# Plugins
########################################################################################################################

$collection->create('api_plugins_package_list', array(
	'path'        => '/plugins/packages',
	'controller'  => 'ApiBundle:Plugins:listPackages',
	'methods'     => array('GET'),
));

$collection->create('api_plugins_package_getinstaller', array(
	'path'         => '/plugins/packages/{name}/installer',
	'controller'   => 'ApiBundle:Plugins:getPackageInstaller',
	'requirements' => array('name' => '[a-z0-9\._]+'),
	'methods'      => array('GET'),
));

########################################################################################################################
# Blobs
########################################################################################################################

$collection->create('api_blobs_upload', array(
	'path'         => '/blobs',
	'controller'   => 'ApiBundle:Blobs:upload',
	'methods'      => array('PUT', 'POST'),
));

$collection->create('api_blobs_get', array(
	'path'         => '/blobs/{id}/{auth}',
	'controller'   => 'ApiBundle:Blobs:getInfo',
	'methods'      => array('GET'),
));

########################################################################################################################
# My
########################################################################################################################

$collection->create('api_my_session_renewtoken', array(
	'path'         => '/my/session/renew-request-token',
	'controller'   => 'ApiBundle:MySession:renewRequestToken',
	'methods'      => array('GET'),
));

########################################################################################################################
# Apps
########################################################################################################################

$collection->create('api_apps', array(
	'path'        => '/apps',
	'controller'  => 'ApiBundle:Apps:list',
	'methods'     => array('GET'),
));

$collection->create('api_apps_resync_packages', array(
	'path'        => '/apps/resync-packages',
	'controller'  => 'ApiBundle:Apps:resyncPackages',
	'methods'     => array('POST'),
));

$collection->create('api_apps_upload_package', array(
	'path'        => '/apps/upload-package',
	'controller'  => 'ApiBundle:Apps:uploadPackage',
	'methods'     => array('POST'),
));

$collection->create('api_apps_custom_new', array(
	'path'        => '/apps/custom',
	'controller'  => 'ApiBundle:Apps:createCustomApp',
	'methods'     => array('PUT'),
));

$collection->create('api_apps_custom_getassets', array(
	'path'         => '/apps/custom/{id}/assets',
	'controller'   => 'ApiBundle:Apps:getCustomAssets',
	'methods'      => array('GET'),
	'requirements' => array('id' => '\d+')
));

$collection->create('api_apps_package', array(
	'path'         => '/apps/packages/{name}',
	'controller'   => 'ApiBundle:Apps:getPackage',
	'methods'      => array('GET'),
	'requirements' => array('name' => '[a-zA-Z0-9_\-\.]+')
));

$collection->create('api_apps_package_delete', array(
	'path'         => '/apps/packages/{name}',
	'controller'   => 'ApiBundle:Apps:deletePackage',
	'methods'      => array('DELETE'),
	'requirements' => array('name' => '[a-zA-Z0-9_\-\.]+')
));

$collection->create('api_apps_instance', array(
	'path'         => '/apps/instances/{id}',
	'controller'   => 'ApiBundle:Apps:getInstance',
	'methods'      => array('GET'),
	'requirements' => array('id' => '\d+')
));

$collection->create('api_apps_instance_update', array(
	'path'         => '/apps/instances/{id}',
	'controller'   => 'ApiBundle:Apps:updateInstance',
	'methods'      => array('POST'),
	'requirements' => array('id' => '\d+')
));

$collection->create('api_apps_instance_uninstall', array(
	'path'         => '/apps/instances/{id}',
	'controller'   => 'ApiBundle:Apps:uninstallInstance',
	'methods'      => array('DELETE'),
	'requirements' => array('id' => '\d+')
));

$collection->create('api_apps_install', array(
	'path'        => '/apps/packages/{name}',
	'controller'  => 'ApiBundle:Apps:installPackage',
	'methods'     => array('PUT'),
));

$collection->create('api_apps_package_exec', array(
	'path'         => '/apps/packages/{name}/{action}',
	'controller'   => 'ApiBundle:Apps:execPackage',
	'defaults'     => array('action' => 'default'),
	'methods'      => array('GET', 'POST', 'PUT', 'DELETE'),
	'requirements' => array('name' => '[a-zA-Z0-9_\-\.]+')
));

$collection->create('api_apps_instance_exec', array(
	'path'         => '/apps/instances/{id}/{action}',
	'controller'   => 'ApiBundle:Apps:execInstance',
	'defaults'     => array('action' => 'default'),
	'methods'      => array('GET', 'POST', 'PUT', 'DELETE'),
	'requirements' => array('id' => '\d+')
));

return $collection;
