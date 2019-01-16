<?php

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create(
    'api',
    [
        'path'       => '/',
        'controller' => 'LegacyApiBundle:Docs:about',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_docs_home',
    [
        'path'       => '/api.html',
        'controller' => 'LegacyApiBundle:Docs:api',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_getagentsforkey',
    [
        'path'       => '/get-agents-for-key.json',
        'controller' => 'LegacyApiBundle:Docs:getAgentsForKey',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_discover',
    [
        'path'       => '/discover',
        'controller' => 'LegacyApiBundle:Test:discover',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_test',
    [
        'path'       => '/test',
        'controller' => 'LegacyApiBundle:Test:test',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_test_post',
    [
        'path'       => '/test',
        'controller' => 'LegacyApiBundle:Test:postTest',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_deskpro_time',
    [
        'path'       => '/deskpro/time',
        'controller' => 'LegacyApiBundle:Deskpro:time',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_deskpro_info',
    [
        'path'       => '/deskpro/info',
        'controller' => 'LegacyApiBundle:Misc:helpdeskInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create('check_url', [
    'path'       => '/check_url',
    'controller' => 'LegacyApiBundle:Misc:checkUrl',
]);

$collection->create(
    'api_deskpro_dpspecial',
    [
        'path'       => '/deskpro/dp_special/{action}',
        'controller' => 'LegacyApiBundle:Misc:dpSpecial',
        'methods'    => ['GET', 'POST'],
    ]
);

$collection->create(
    'api_me_lastlogin',
    [
        'path'       => '/me/last-login',
        'controller' => 'LegacyApiBundle:Misc:getLastLogin',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_token_exchange',
    [
        'path'       => '/token-exchange',
        'controller' => 'LegacyApiBundle:Misc:tokenExchange',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_token_renew',
    [
        'path'       => '/renew-token',
        'controller' => 'LegacyApiBundle:Misc:renewToken',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_profile_inhelpstate',
    [
        'path'       => '/profile/inhelp/{id}/{state}',
        'controller' => 'LegacyApiBundle:Profile:saveInhelpState',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_docs',
    [
        'path'       => '/docs',
        'controller' => 'LegacyApiBundle:Docs:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_docs_get',
    [
        'path'       => '/docs/{id}',
        'controller' => 'LegacyApiBundle:Docs:get',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// General
//#######################################################################################################################

$collection->create(
    'api_labels_definitions',
    [
        'path'       => '/labels/definitions/{type}',
        'controller' => 'LegacyApiBundle:Labels:listDefinitions',
        'methods'    => ['GET'],
        'defaults'   => ['type' => null],
    ]
);

$collection->create(
    'api_labels_definitions_create',
    [
        'path'       => '/labels/definitions',
        'controller' => 'LegacyApiBundle:Labels:createDefinition',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_labels_definitions_update',
    [
        'path'       => '/labels/definitions',
        'controller' => 'LegacyApiBundle:Labels:updateDefinition',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_labels_definitions_delete',
    [
        'path'       => '/labels/definitions',
        'controller' => 'LegacyApiBundle:Labels:deleteDefinition',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_misc_upload',
    [
        'path'       => '/misc/upload',
        'controller' => 'LegacyApiBundle:Misc:upload',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_misc_session_person',
    [
        'path'       => '/misc/session-person/{session_code}',
        'controller' => 'LegacyApiBundle:Misc:getSessionPerson',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_misc_rate_limit',
    [
        'path'       => '/misc/rate-limit',
        'controller' => 'LegacyApiBundle:Misc:getRateLimit',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_new',
    [
        'path'       => '/tickets',
        'controller' => 'LegacyApiBundle:Ticket:newTicket',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket',
    [
        'path'         => '/tickets/{ticket_id}',
        'controller'   => 'LegacyApiBundle:Ticket:getTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_post',
    [
        'path'         => '/tickets/{ticket_id}',
        'controller'   => 'LegacyApiBundle:Ticket:postTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_delete',
    [
        'path'         => '/tickets/{ticket_id}',
        'controller'   => 'LegacyApiBundle:Ticket:deleteTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tickets_ticket_logs',
    [
        'path'         => '/tickets/{ticket_id}/logs',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketLogs',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_messages',
    [
        'path'         => '/tickets/{ticket_id}/messages',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketMessages',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_messages_post',
    [
        'path'         => '/tickets/{ticket_id}/messages',
        'controller'   => 'LegacyApiBundle:Ticket:replyTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_message',
    [
        'path'         => '/tickets/{ticket_id}/messages/{message_id}',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketMessage',
        'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_message_details',
    [
        'path'         => '/tickets/{ticket_id}/messages/{message_id}/details',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketMessageDetails',
        'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_undelete',
    [
        'path'         => '/tickets/{ticket_id}/undelete',
        'controller'   => 'LegacyApiBundle:Ticket:undeleteTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_split',
    [
        'path'         => '/tickets/{ticket_id}/split',
        'controller'   => 'LegacyApiBundle:Ticket:splitTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_claim',
    [
        'path'         => '/tickets/{ticket_id}/claim',
        'controller'   => 'LegacyApiBundle:Ticket:claimTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_merge',
    [
        'path'         => '/tickets/{ticket_id}/merge/{merge_ticket_id}',
        'controller'   => 'LegacyApiBundle:Ticket:mergeTicket',
        'requirements' => ['ticket_id' => '\\d+', 'merge_ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_link',
    [
        'path'         => '/tickets/{ticket_id}/link/{link_ticket_id}',
        'controller'   => 'LegacyApiBundle:Ticket:linkTicket',
        'requirements' => ['ticket_id' => '\\d+', 'link_ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_spam',
    [
        'path'         => '/tickets/{ticket_id}/spam',
        'controller'   => 'LegacyApiBundle:Ticket:spamTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_unspam',
    [
        'path'         => '/tickets/{ticket_id}/unspam',
        'controller'   => 'LegacyApiBundle:Ticket:unspamTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_lock',
    [
        'path'         => '/tickets/{ticket_id}/lock',
        'controller'   => 'LegacyApiBundle:Ticket:lockTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_unlock',
    [
        'path'         => '/tickets/{ticket_id}/unlock',
        'controller'   => 'LegacyApiBundle:Ticket:unlockTicket',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_tasks',
    [
        'path'         => '/tickets/{ticket_id}/tasks',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketTasks',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_tasks_post',
    [
        'path'         => '/tickets/{ticket_id}/tasks',
        'controller'   => 'LegacyApiBundle:Ticket:postTicketTasks',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_billing_charges',
    [
        'path'         => '/tickets/{ticket_id}/billing-charges',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketBillingCharges',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_billing_charges_post',
    [
        'path'         => '/tickets/{ticket_id}/billing-charges',
        'controller'   => 'LegacyApiBundle:Ticket:postTicketBillingCharges',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_billing_charge',
    [
        'path'         => '/tickets/{ticket_id}/billing-charges/{charge_id}',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketBillingCharge',
        'requirements' => ['ticket_id' => '\\d+', 'charge_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_billing_charge_delete',
    [
        'path'         => '/tickets/{ticket_id}/billing-charges/{charge_id}',
        'controller'   => 'LegacyApiBundle:Ticket:deleteTicketBillingCharge',
        'requirements' => ['ticket_id' => '\\d+', 'charge_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tickets_ticket_slas',
    [
        'path'         => '/tickets/{ticket_id}/slas',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketSlas',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_slas_post',
    [
        'path'         => '/tickets/{ticket_id}/slas',
        'controller'   => 'LegacyApiBundle:Ticket:postTicketSlas',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_sla',
    [
        'path'         => '/tickets/{ticket_id}/slas/{ticket_sla_id}',
        'controller'   => 'LegacyApiBundle:Ticket:getTicketSla',
        'requirements' => ['ticket_id' => '\\d+', 'ticket_sla_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_sla_delete',
    [
        'path'         => '/tickets/{ticket_id}/slas/{ticket_sla_id}',
        'controller'   => 'LegacyApiBundle:Ticket:deleteTicketSla',
        'requirements' => ['ticket_id' => '\\d+', 'ticket_sla_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tickets_ticket_participants',
    [
        'path'         => '/tickets/{ticket_id}/participants',
        'controller'   => 'LegacyApiBundle:Ticket:getParticipants',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_participants_post',
    [
        'path'         => '/tickets/{ticket_id}/participants',
        'controller'   => 'LegacyApiBundle:Ticket:postParticipants',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_participant',
    [
        'path'         => '/tickets/{ticket_id}/participants/{person_id}',
        'controller'   => 'LegacyApiBundle:Ticket:getParticipant',
        'requirements' => ['ticket_id' => '\\d+', 'person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_participant_delete',
    [
        'path'         => '/tickets/{ticket_id}/participants/{person_id}',
        'controller'   => 'LegacyApiBundle:Ticket:deleteParticipant',
        'requirements' => ['ticket_id' => '\\d+', 'person_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tickets_ticket_labels',
    [
        'path'         => '/tickets/{ticket_id}/labels',
        'controller'   => 'LegacyApiBundle:Ticket:getLabels',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_labels_post',
    [
        'path'         => '/tickets/{ticket_id}/labels',
        'controller'   => 'LegacyApiBundle:Ticket:postLabels',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets_ticket_label',
    [
        'path'         => '/tickets/{ticket_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Ticket:getLabel',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tickets_ticket_label_delete',
    [
        'path'         => '/tickets/{ticket_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Ticket:deleteLabel',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tickets_update_dates',
    [
        'path'         => '/tickets/{ticket_id}/update_dates',
        'controller'   => 'LegacyApiBundle:Ticket:updateTicketDates',
        'requirements' => ['ticket_id' => '\\d+'],
        'methods'      => ['PUT'],
    ]
);

$collection->create(
    'api_tickets_fields',
    [
        'path'       => '/tickets/fields',
        'controller' => 'LegacyApiBundle:Ticket:getFields',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_departments',
    [
        'path'       => '/tickets/departments',
        'controller' => 'LegacyApiBundle:Ticket:getDepartments',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_products',
    [
        'path'       => '/tickets/products',
        'controller' => 'LegacyApiBundle:Ticket:getProducts',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_categories',
    [
        'path'       => '/tickets/categories',
        'controller' => 'LegacyApiBundle:Ticket:getCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_priorities',
    [
        'path'       => '/tickets/priorities',
        'controller' => 'LegacyApiBundle:Ticket:getPriorities',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_workflows',
    [
        'path'       => '/tickets/workflows',
        'controller' => 'LegacyApiBundle:Ticket:getWorkflows',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_slas',
    [
        'path'       => '/tickets/slas',
        'controller' => 'LegacyApiBundle:Ticket:getSlas',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_sla',
    [
        'path'         => '/tickets/slas/{sla_id}',
        'controller'   => 'LegacyApiBundle:Ticket:getSla',
        'requirements' => ['sla_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_textsnippets_list',
    [
        'path'       => '/text-snippets/{typename}',
        'controller' => 'LegacyApiBundle:TextSnippets:filterSnippets',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_textsnippets_new',
    [
        'path'       => '/text-snippets/{typename}',
        'controller' => 'LegacyApiBundle:TextSnippets:saveSnippet',
        'defaults'   => ['id' => '0'],
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_textsnippets_edit',
    [
        'path'         => '/text-snippets/{typename}/{id}',
        'controller'   => 'LegacyApiBundle:TextSnippets:saveSnippet',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_textsnippets_del',
    [
        'path'         => '/text-snippets/{typename}/{id}',
        'controller'   => 'LegacyApiBundle:TextSnippets:deleteSnippet',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_textsnippets_get',
    [
        'path'         => '/text-snippets/{typename}/{id}',
        'controller'   => 'LegacyApiBundle:TextSnippets:getSnippet',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_textsnippets_cats_list',
    [
        'path'       => '/text-snippets/{typename}/categories',
        'controller' => 'LegacyApiBundle:TextSnippets:listCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_textsnippets_cats_new',
    [
        'path'       => '/text-snippets/{typename}/categories',
        'controller' => 'LegacyApiBundle:TextSnippets:saveCategory',
        'defaults'   => ['id' => '0'],
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_textsnippets_cats_edit',
    [
        'path'         => '/text-snippets/{typename}/categories/{id}',
        'controller'   => 'LegacyApiBundle:TextSnippets:saveCategory',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_textsnippets_cats_get',
    [
        'path'         => '/text-snippets/{typename}/categories/{id}',
        'controller'   => 'LegacyApiBundle:TextSnippets:getCategory',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_textsnippets_cats_del',
    [
        'path'         => '/text-snippets/{typename}/categories/{id}',
        'controller'   => 'LegacyApiBundle:TextSnippets:deleteCategory',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_open_tickets_newticketmessage',
    [
        'path'         => '/open/tickets/new-ticket-message',
        'controller'   => 'LegacyApiBundle:OpenTicket:newTicketMessage',
        'requirements' => ['sla_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tickets',
    [
        'path'       => '/tickets',
        'controller' => 'LegacyApiBundle:TicketSearch:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_quickstats',
    [
        'path'       => '/tickets/quick-stats',
        'controller' => 'LegacyApiBundle:TicketSearch:getQuickStats',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_filters',
    [
        'path'       => '/tickets/filters',
        'controller' => 'LegacyApiBundle:TicketSearch:getFilters',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_filter_counts',
    [
        'path'       => '/tickets/filters/counts',
        'controller' => 'LegacyApiBundle:TicketSearch:getFilterCounts',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tickets_filter',
    [
        'path'         => '/tickets/filters/{filter_id}',
        'controller'   => 'LegacyApiBundle:TicketSearch:getFilter',
        'requirements' => ['filter_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people',
    [
        'path'       => '/people',
        'controller' => 'LegacyApiBundle:Person:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_people_quick_search',
    [
        'path'       => '/people/quick_search',
        'controller' => 'LegacyApiBundle:Person:quickSearch',
        'methods'    => ['GET'],
    ]
);

$collection->create('api_people_quick_search_email', [
    'path'       => '/people/quick_search_email',
    'controller' => 'LegacyApiBundle:Person:quickSearchEmail',
    'methods'    => ['GET'],
]);

$collection->create(
    'api_people_post',
    [
        'path'       => '/people',
        'controller' => 'LegacyApiBundle:Person:newPerson',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_people_person',
    [
        'path'         => '/people/{person_id}',
        'controller'   => 'LegacyApiBundle:Person:getPerson',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_quick_search_person',
    [
        'path'         => '/people/quick_search/{person_id}',
        'controller'   => 'LegacyApiBundle:Person:getPerson',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_post',
    [
        'path'         => '/people/{person_id}',
        'controller'   => 'LegacyApiBundle:Person:postPerson',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_delete',
    [
        'path'         => '/people/{person_id}',
        'controller'   => 'LegacyApiBundle:Person:deletePerson',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_people_person_merge',
    [
        'path'         => '/people/{person_id}/merge/{other_person_id}',
        'controller'   => 'LegacyApiBundle:Person:mergePerson',
        'requirements' => ['person_id' => '\\d+', 'other_person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_logintoken',
    [
        'path'         => '/people/{person_id}/login-token',
        'controller'   => 'LegacyApiBundle:Person:getLoginToken',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_picture',
    [
        'path'         => '/people/{person_id}/picture',
        'controller'   => 'LegacyApiBundle:Person:getPersonPicture',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_picture_post',
    [
        'path'         => '/people/{person_id}/picture',
        'controller'   => 'LegacyApiBundle:Person:postPersonPicture',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_picture_delete',
    [
        'path'         => '/people/{person_id}/picture',
        'controller'   => 'LegacyApiBundle:Person:deletePersonPicture',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_people_person_emails',
    [
        'path'         => '/people/{person_id}/emails',
        'controller'   => 'LegacyApiBundle:Person:getPersonEmails',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_emails_post',
    [
        'path'         => '/people/{person_id}/emails',
        'controller'   => 'LegacyApiBundle:Person:postPersonEmails',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_email',
    [
        'path'         => '/people/{person_id}/emails/{email_id}',
        'controller'   => 'LegacyApiBundle:Person:getPersonEmail',
        'requirements' => ['person_id' => '\\d+', 'email_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_email_post',
    [
        'path'         => '/people/{person_id}/emails/{email_id}',
        'controller'   => 'LegacyApiBundle:Person:postPersonEmail',
        'requirements' => ['person_id' => '\\d+', 'email_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_email_delete',
    [
        'path'         => '/people/{person_id}/emails/{email_id}',
        'controller'   => 'LegacyApiBundle:Person:deletePersonEmail',
        'requirements' => ['person_id' => '\\d+', 'email_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create('api_people_person_phone_numbers_get', [
    'path'         => '/people/{person_id}/phone_numbers',
    'controller'   => 'LegacyApiBundle:Person:getPersonPhoneNumbers',
    'requirements' => ['person_id' => '\\d+'],
    'methods'      => ['GET'],
]);

$collection->create('api_people_person_phone_numbers_update', [
    'path'         => '/people/{person_id}/phone_numbers',
    'controller'   => 'LegacyApiBundle:Person:postPersonPhoneNumbers',
    'requirements' => ['person_id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('api_people_person_phone_numbers', [
    'path'         => '/people/{person_id}/phone_numbers/{number_id}',
    'controller'   => 'LegacyApiBundle:Person:getPersonPhoneNumber',
    'requirements' => ['person_id' => '\\d+', 'number_id' => '\\d+'],
    'methods'      => ['GET'],
]);

$collection->create('api_people_person_phone_numbers_post', [
    'path'         => '/people/{person_id}/phone_numbers/{number_id}',
    'controller'   => 'LegacyApiBundle:Person:postPersonPhoneNumber',
    'requirements' => ['person_id' => '\\d+', 'number_id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('api_people_person_phone_numbers_delete', [
    'path'         => '/people/{person_id}/phone_numbers/{number_id}',
    'controller'   => 'LegacyApiBundle:Person:deletePersonPhoneNumber',
    'requirements' => ['person_id' => '\\d+', 'number_id' => '\\d+'],
    'methods'      => ['DELETE'],
]);

$collection->create(
    'api_people_person_vcard',
    [
        'path'         => '/people/{person_id}/vcard',
        'controller'   => 'LegacyApiBundle:Person:getPersonVcard',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_tickets',
    [
        'path'         => '/people/{person_id}/tickets',
        'controller'   => 'LegacyApiBundle:Person:getPersonTickets',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_chats',
    [
        'path'         => '/people/{person_id}/chats',
        'controller'   => 'LegacyApiBundle:Person:getPersonChats',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_activity_stream',
    [
        'path'         => '/people/{person_id}/activity-stream',
        'controller'   => 'LegacyApiBundle:Person:getPersonActivityStream',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_reset_password',
    [
        'path'         => '/people/{person_id}/reset-password',
        'controller'   => 'LegacyApiBundle:Person:resetPassword',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_clear_session',
    [
        'path'         => '/people/{person_id}/clear-session',
        'controller'   => 'LegacyApiBundle:Person:clearSession',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_notes',
    [
        'path'         => '/people/{person_id}/notes',
        'controller'   => 'LegacyApiBundle:Person:getPersonNotes',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_notes_post',
    [
        'path'         => '/people/{person_id}/notes',
        'controller'   => 'LegacyApiBundle:Person:postPersonNotes',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_billing_charges',
    [
        'path'         => '/people/{person_id}/billing-charges',
        'controller'   => 'LegacyApiBundle:Person:getPersonBillingCharges',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_contact_details',
    [
        'path'         => '/people/{person_id}/contact-details',
        'controller'   => 'LegacyApiBundle:Person:getPersonContactDetails',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_contact_details_post',
    [
        'path'         => '/people/{person_id}/contact-details',
        'controller'   => 'LegacyApiBundle:Person:postPersonContactDetails',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_contact_detail',
    [
        'path'         => '/people/{person_id}/contact-details/{contact_id}',
        'controller'   => 'LegacyApiBundle:Person:getPersonContactDetail',
        'requirements' => ['person_id' => '\\d+', 'contact_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_contact_detail_delete',
    [
        'path'         => '/people/{person_id}/contact-details/{contact_id}',
        'controller'   => 'LegacyApiBundle:Person:deletePersonContactDetail',
        'requirements' => ['person_id' => '\\d+', 'contact_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_people_person_groups',
    [
        'path'         => '/people/{person_id}/groups',
        'controller'   => 'LegacyApiBundle:Person:getPersonGroups',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_groups_post',
    [
        'path'         => '/people/{person_id}/groups',
        'controller'   => 'LegacyApiBundle:Person:postPersonGroups',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_group',
    [
        'path'         => '/people/{person_id}/groups/{usergroup_id}',
        'controller'   => 'LegacyApiBundle:Person:getPersonGroup',
        'requirements' => ['person_id' => '\\d+', 'usergroup_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_group_delete',
    [
        'path'         => '/people/{person_id}/groups/{usergroup_id}',
        'controller'   => 'LegacyApiBundle:Person:deletePersonGroup',
        'requirements' => ['person_id' => '\\d+', 'usergroup_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_people_person_brands',
    [
        'path'         => '/people/{person_id}/brands',
        'controller'   => 'LegacyApiBundle:Person:getPersonBrands',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_brands_post',
    [
        'path'         => '/people/{person_id}/brands',
        'controller'   => 'LegacyApiBundle:Person:postPersonBrands',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_brand',
    [
        'path'         => '/people/{person_id}/brands/{brand_id}',
        'controller'   => 'LegacyApiBundle:Person:getPersonBrand',
        'requirements' => ['person_id' => '\\d+', 'brand_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_group_delete',
    [
        'path'         => '/people/{person_id}/brands/{brand_id}',
        'controller'   => 'LegacyApiBundle:Person:deletePersonBrand',
        'requirements' => ['person_id' => '\\d+', 'brand_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_people_person_labels',
    [
        'path'         => '/people/{person_id}/labels',
        'controller'   => 'LegacyApiBundle:Person:getPersonLabels',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_labels_post',
    [
        'path'         => '/people/{person_id}/labels',
        'controller'   => 'LegacyApiBundle:Person:postPersonLabels',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_people_person_label',
    [
        'path'         => '/people/{person_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Person:getPersonLabel',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_people_person_label_delete',
    [
        'path'         => '/people/{person_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Person:deletePersonLabel',
        'requirements' => ['person_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_people_fields',
    [
        'path'       => '/people/fields',
        'controller' => 'LegacyApiBundle:Person:getFields',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_people_groups',
    [
        'path'       => '/people/groups',
        'controller' => 'LegacyApiBundle:Person:getGroups',
        'methods'    => ['GET'],
    ]
);

$collection->create('api_people_authlogin', [
    'path'       => '/people/auth-login',
    'controller' => 'LegacyApiBundle:Person:authLogin',
    'methods'    => ['POST'],
]);

$collection->create(
    'api_agents_list',
    [
        'path'       => '/agents',
        'controller' => 'LegacyApiBundle:Agents:listAgents',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agents_list_deleted',
    [
        'path'       => '/agents/deleted',
        'controller' => 'LegacyApiBundle:Agents:listDeletedAgents',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agents_get_deleted',
    [
        'path'       => '/agents/deleted/{id}',
        'controller' => 'LegacyApiBundle:Agents:getDeletedAgent',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agents_undelete',
    [
        'path'       => '/agents/deleted/{id}/undelete',
        'controller' => 'LegacyApiBundle:Agents:undeleteAgent',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agents_get',
    [
        'path'       => '/agents/{id}',
        'controller' => 'LegacyApiBundle:Agents:getAgent',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agents_delete',
    [
        'path'       => '/agents/{id}/delete',
        'controller' => 'LegacyApiBundle:Agents:deleteAgent',
        'defaults'   => ['mode' => 'delete'],
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_agents_deletetouse',
    [
        'path'       => '/agents/{id}/delete/to-user',
        'controller' => 'LegacyApiBundle:Agents:deleteAgent',
        'defaults'   => ['mode' => 'user'],
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_agents_save',
    [
        'path'       => '/agents/{id}',
        'controller' => 'LegacyApiBundle:Agents:saveAgent',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agents_save_profile',
    [
        'path'       => '/agents/{id}/profile',
        'controller' => 'LegacyApiBundle:Agents:saveAgentProfile',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agents_resetpassword',
    [
        'path'       => '/agents/{id}/reset-password',
        'controller' => 'LegacyApiBundle:Agents:resetPassword',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agents_getlogintoken',
    [
        'path'       => '/agents/{id}/login-token',
        'controller' => 'LegacyApiBundle:Agents:generateLoginToken',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agents_create',
    [
        'path'       => '/agents',
        'controller' => 'LegacyApiBundle:Agents:saveAgent',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_agents_create_bulk',
    [
        'path'       => '/agents_bulk',
        'controller' => 'LegacyApiBundle:Agents:bulkCreateAgents',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agents_create_bulk_check',
    [
        'path'       => '/agents_bulk/check',
        'controller' => 'LegacyApiBundle:Agents:bulkLicenseCheck',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agents_notifyprefs_gettables',
    [
        'path'       => '/agents/{id}/notify-prefs/get-tables',
        'controller' => 'LegacyApiBundle:Agents:getNotifyPrefs',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agent_teams_list',
    [
        'path'       => '/agent_teams',
        'controller' => 'LegacyApiBundle:AgentTeams:listTeams',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agent_teams_get',
    [
        'path'       => '/agent_teams/{id}',
        'controller' => 'LegacyApiBundle:AgentTeams:getTeam',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agent_teams_update',
    [
        'path'       => '/agent_teams/{id}',
        'controller' => 'LegacyApiBundle:AgentTeams:saveTeam',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agent_teams_create',
    [
        'path'       => '/agent_teams',
        'controller' => 'LegacyApiBundle:AgentTeams:saveTeam',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_agent_teams_delete',
    [
        'path'       => '/agent_teams/{id}',
        'controller' => 'LegacyApiBundle:AgentTeams:deleteTeam',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_agentgroups_list',
    [
        'path'       => '/agent_groups',
        'controller' => 'LegacyApiBundle:AgentGroups:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agentgroups_get',
    [
        'path'       => '/agent_groups/{id}',
        'controller' => 'LegacyApiBundle:AgentGroups:getGroup',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_agentgroups_save',
    [
        'path'       => '/agent_groups/{id}',
        'controller' => 'LegacyApiBundle:AgentGroups:saveGroup',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agentgroups_create',
    [
        'path'       => '/agent_groups',
        'controller' => 'LegacyApiBundle:AgentGroups:saveGroup',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_agentgroups_del',
    [
        'path'       => '/agent_groups/{id}',
        'controller' => 'LegacyApiBundle:AgentGroups:deleteGroup',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_agentgroups_enable',
    [
        'path'       => '/agent_groups/{id}/enable',
        'controller' => 'LegacyApiBundle:AgentGroups:toggleGroup',
        'defaults'   => ['is_enabled' => true],
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agentgroups_disable',
    [
        'path'       => '/agent_groups/{id}/disable',
        'controller' => 'LegacyApiBundle:AgentGroups:toggleGroup',
        'defaults'   => ['is_enabled' => false],
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_agentgroups_getperms',
    [
        'path'       => '/agent_groups/all/permissions',
        'controller' => 'LegacyApiBundle:AgentGroups:getAllPerms',
        'defaults'   => [],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_combiner',
    [
        'path'       => '/api_caller',
        'controller' => 'LegacyApiBundle:ApiCombiner:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_organizations',
    [
        'path'       => '/organizations',
        'controller' => 'LegacyApiBundle:Organization:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_organizations_quick_search',
    [
        'path'       => '/organizations/quick_search',
        'controller' => 'LegacyApiBundle:Organization:quickSearch',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_organizations_post',
    [
        'path'       => '/organizations',
        'controller' => 'LegacyApiBundle:Organization:newOrganization',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization',
    [
        'path'         => '/organizations/{organization_id}',
        'controller'   => 'LegacyApiBundle:Organization:getOrganization',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_post',
    [
        'path'         => '/organizations/{organization_id}',
        'controller'   => 'LegacyApiBundle:Organization:postOrganization',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_delete',
    [
        'path'         => '/organizations/{organization_id}',
        'controller'   => 'LegacyApiBundle:Organization:deleteOrganization',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_organizations_organization_picture',
    [
        'path'         => '/organizations/{organization_id}/picture',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationPicture',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_picture_post',
    [
        'path'         => '/organizations/{organization_id}/picture',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationPicture',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_picture_delete',
    [
        'path'         => '/organizations/{organization_id}/picture',
        'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationPicture',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_organizations_organization_activity_stream',
    [
        'path'         => '/organizations/{organization_id}/activity-stream',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationActivityStream',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_members',
    [
        'path'         => '/organizations/{organization_id}/members',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationMembers',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_tickets',
    [
        'path'         => '/organizations/{organization_id}/tickets',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationTickets',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_chats',
    [
        'path'         => '/organizations/{organization_id}/chats',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationChats',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_billing_charges',
    [
        'path'         => '/organizations/{organization_id}/billing-charges',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationBillingCharges',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_email_domains',
    [
        'path'         => '/organizations/{organization_id}/email-domains',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationEmailDomains',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_email_domains_post',
    [
        'path'         => '/organizations/{organization_id}/email-domains',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationEmailDomains',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_email_domain',
    [
        'path'         => '/organizations/{organization_id}/email-domains/{domain}',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationEmailDomain',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_email_domain_move_users',
    [
        'path'         => '/organizations/{organization_id}/email-domains/{domain}/move-users',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationEmailDomainMoveUsers',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_email_domain_move_taken_users',
    [
        'path'         => '/organizations/{organization_id}/email-domains/{domain}/move-taken-users',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationEmailDomainMoveTakenUsers',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_email_domain_delete',
    [
        'path'         => '/organizations/{organization_id}/email-domains/{domain}',
        'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationEmailDomain',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_organizations_organization_contact_details',
    [
        'path'         => '/organizations/{organization_id}/contact-details',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationContactDetails',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_contact_details_post',
    [
        'path'         => '/organizations/{organization_id}/contact-details',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationContactDetails',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_contact_detail',
    [
        'path'         => '/organizations/{organization_id}/contact-details/{contact_id}',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationContactDetail',
        'requirements' => ['organization_id' => '\\d+', 'contact_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_contact_detail_delete',
    [
        'path'         => '/organizations/{organization_id}/contact-details/{contact_id}',
        'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationContactDetail',
        'requirements' => ['organization_id' => '\\d+', 'contact_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_organizations_organization_groups',
    [
        'path'         => '/organizations/{organization_id}/groups',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationGroups',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_groups_post',
    [
        'path'         => '/organizations/{organization_id}/groups',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationGroups',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_group',
    [
        'path'         => '/organizations/{organization_id}/groups/{usergroup_id}',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationGroup',
        'requirements' => ['organization_id' => '\\d+', 'usergroup_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_group_delete',
    [
        'path'         => '/organizations/{organization_id}/groups/{usergroup_id}',
        'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationGroup',
        'requirements' => ['organization_id' => '\\d+', 'usergroup_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_organizations_organization_labels',
    [
        'path'         => '/organizations/{organization_id}/labels',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationLabels',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_labels_post',
    [
        'path'         => '/organizations/{organization_id}/labels',
        'controller'   => 'LegacyApiBundle:Organization:postOrganizationLabels',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_organizations_organization_label',
    [
        'path'         => '/organizations/{organization_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Organization:getOrganizationLabel',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_organizations_organization_label_delete',
    [
        'path'         => '/organizations/{organization_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Organization:deleteOrganizationLabel',
        'requirements' => ['organization_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_organizations_fields',
    [
        'path'       => '/organizations/fields',
        'controller' => 'LegacyApiBundle:Organization:getFields',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_organizations_groups',
    [
        'path'       => '/organizations/groups',
        'controller' => 'LegacyApiBundle:Organization:getGroups',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chats',
    [
        'path'       => '/chats',
        'controller' => 'LegacyApiBundle:Chat:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat',
    [
        'path'         => '/chats/{chat_id}',
        'controller'   => 'LegacyApiBundle:Chat:getChat',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat_post',
    [
        'path'         => '/chats/{chat_id}',
        'controller'   => 'LegacyApiBundle:Chat:postChat',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chats_chat_leave',
    [
        'path'         => '/chats/{chat_id}/leave',
        'controller'   => 'LegacyApiBundle:Chat:leaveChat',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chats_chat_end',
    [
        'path'         => '/chats/{chat_id}/end',
        'controller'   => 'LegacyApiBundle:Chat:endChat',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chats_chat_messages',
    [
        'path'         => '/chats/{chat_id}/messages',
        'controller'   => 'LegacyApiBundle:Chat:getMessages',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat_messages_post',
    [
        'path'         => '/chats/{chat_id}/messages',
        'controller'   => 'LegacyApiBundle:Chat:newMessage',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chats_chat_participants',
    [
        'path'         => '/chats/{chat_id}/participants',
        'controller'   => 'LegacyApiBundle:Chat:getParticipants',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat_participants_post',
    [
        'path'         => '/chats/{chat_id}/participants',
        'controller'   => 'LegacyApiBundle:Chat:postParticipants',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chats_chat_participant',
    [
        'path'         => '/chats/{chat_id}/participants/{person_id}',
        'controller'   => 'LegacyApiBundle:Chat:getParticipant',
        'requirements' => ['chat_id' => '\\d+', 'person_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat_participant_delete',
    [
        'path'         => '/chats/{chat_id}/participants/{person_id}',
        'controller'   => 'LegacyApiBundle:Chat:deleteParticipant',
        'requirements' => ['chat_id' => '\\d+', 'person_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_chats_chat_labels',
    [
        'path'         => '/chats/{chat_id}/labels',
        'controller'   => 'LegacyApiBundle:Chat:getChatLabels',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat_labels_post',
    [
        'path'         => '/chats/{chat_id}/labels',
        'controller'   => 'LegacyApiBundle:Chat:postChatLabels',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chats_chat_label',
    [
        'path'         => '/chats/{chat_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Chat:getChatLabel',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chats_chat_label_delete',
    [
        'path'         => '/chats/{chat_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Chat:deleteChatLabel',
        'requirements' => ['chat_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_downloads',
    [
        'path'       => '/downloads',
        'controller' => 'LegacyApiBundle:Download:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_downloads_post',
    [
        'path'       => '/downloads',
        'controller' => 'LegacyApiBundle:Download:newDownload',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_downloads_download',
    [
        'path'         => '/downloads/{download_id}',
        'controller'   => 'LegacyApiBundle:Download:getDownload',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_download_post',
    [
        'path'         => '/downloads/{download_id}',
        'controller'   => 'LegacyApiBundle:Download:postDownload',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_downloads_download_delete',
    [
        'path'         => '/downloads/{download_id}',
        'controller'   => 'LegacyApiBundle:Download:deleteDownload',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_downloads_download_comments',
    [
        'path'         => '/downloads/{download_id}/comments',
        'controller'   => 'LegacyApiBundle:Download:getDownloadComments',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_download_comments_new',
    [
        'path'         => '/downloads/{download_id}/comments',
        'controller'   => 'LegacyApiBundle:Download:newDownloadComment',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_downloads_download_comments_comment',
    [
        'path'         => '/downloads/{download_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Download:getDownloadComment',
        'requirements' => ['download_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_download_comments_comment_post',
    [
        'path'         => '/downloads/{download_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Download:postDownloadComment',
        'requirements' => ['download_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_downloads_download_comments_comment_delete',
    [
        'path'         => '/downloads/{download_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Download:deleteDownloadComment',
        'requirements' => ['download_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_downloads_download_labels',
    [
        'path'         => '/downloads/{download_id}/labels',
        'controller'   => 'LegacyApiBundle:Download:getDownloadLabels',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_download_labels_post',
    [
        'path'         => '/downloads/{download_id}/labels',
        'controller'   => 'LegacyApiBundle:Download:postDownloadLabels',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_downloads_download_label',
    [
        'path'         => '/downloads/{download_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Download:getDownloadLabel',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_download_label_delete',
    [
        'path'         => '/downloads/{download_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Download:deleteDownloadLabel',
        'requirements' => ['download_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_downloads_validating_comments',
    [
        'path'       => '/downloads/validating-comments',
        'controller' => 'LegacyApiBundle:Download:getValidatingComments',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_downloads_categories',
    [
        'path'       => '/downloads/categories',
        'controller' => 'LegacyApiBundle:Download:getCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_downloads_categories_post',
    [
        'path'       => '/downloads/categories',
        'controller' => 'LegacyApiBundle:Download:postCategories',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_downloads_category',
    [
        'path'         => '/downloads/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:Download:getCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_category_post',
    [
        'path'         => '/downloads/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:Download:postCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_downloads_category_delete',
    [
        'path'         => '/downloads/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:Download:deleteCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_downloads_category_downloads',
    [
        'path'         => '/downloads/categories/{category_id}/downloads',
        'controller'   => 'LegacyApiBundle:Download:getCategoryDownloads',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_category_groups',
    [
        'path'         => '/downloads/categories/{category_id}/groups',
        'controller'   => 'LegacyApiBundle:Download:getCategoryGroups',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_category_groups_post',
    [
        'path'         => '/downloads/categories/{category_id}/groups',
        'controller'   => 'LegacyApiBundle:Download:postCategoryGroups',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_downloads_category_group',
    [
        'path'         => '/downloads/categories/{category_id}/groups/{group_id}',
        'controller'   => 'LegacyApiBundle:Download:getCategoryGroup',
        'requirements' => ['category_id' => '\\d+', 'group_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_downloads_category_group_delete',
    [
        'path'         => '/downloads/categories/{category_id}/groups/{group_id}',
        'controller'   => 'LegacyApiBundle:Download:deleteCategoryGroup',
        'requirements' => ['category_id' => '\\d+', 'group_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_news',
    [
        'path'       => '/news',
        'controller' => 'LegacyApiBundle:News:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_news_post',
    [
        'path'       => '/news',
        'controller' => 'LegacyApiBundle:News:newNews',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_news_news',
    [
        'path'         => '/news/{news_id}',
        'controller'   => 'LegacyApiBundle:News:getNews',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_news_post',
    [
        'path'         => '/news/{news_id}',
        'controller'   => 'LegacyApiBundle:News:postNews',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_news_news_delete',
    [
        'path'         => '/news/{news_id}',
        'controller'   => 'LegacyApiBundle:News:deleteNews',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_news_news_comments',
    [
        'path'         => '/news/{news_id}/comments',
        'controller'   => 'LegacyApiBundle:News:getNewsComments',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_news_comments_new',
    [
        'path'         => '/news/{news_id}/comments',
        'controller'   => 'LegacyApiBundle:News:newNewsComment',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_news_news_comments_comment',
    [
        'path'         => '/news/{news_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:News:getNewsComment',
        'requirements' => ['news_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_news_comments_comment_post',
    [
        'path'         => '/news/{news_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:News:postNewsComment',
        'requirements' => ['news_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_news_news_comments_comment_delete',
    [
        'path'         => '/news/{news_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:News:deleteNewsComment',
        'requirements' => ['news_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_news_news_labels',
    [
        'path'         => '/news/{news_id}/labels',
        'controller'   => 'LegacyApiBundle:News:getNewsLabels',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_news_labels_post',
    [
        'path'         => '/news/{news_id}/labels',
        'controller'   => 'LegacyApiBundle:News:postNewsLabels',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_news_news_label',
    [
        'path'         => '/news/{news_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:News:getNewsLabel',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_news_label_delete',
    [
        'path'         => '/news/{news_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:News:deleteNewsLabel',
        'requirements' => ['news_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_news_validating_comments',
    [
        'path'       => '/news/validating-comments',
        'controller' => 'LegacyApiBundle:News:getValidatingComments',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_news_categories',
    [
        'path'       => '/news/categories',
        'controller' => 'LegacyApiBundle:News:getCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_news_categories_post',
    [
        'path'       => '/news/categories',
        'controller' => 'LegacyApiBundle:News:postCategories',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_news_category',
    [
        'path'         => '/news/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:News:getCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_category_post',
    [
        'path'         => '/news/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:News:postCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_news_category_delete',
    [
        'path'         => '/news/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:News:deleteCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_news_category_news',
    [
        'path'         => '/news/categories/{category_id}/news',
        'controller'   => 'LegacyApiBundle:News:getCategoryNews',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_category_groups',
    [
        'path'         => '/news/categories/{category_id}/groups',
        'controller'   => 'LegacyApiBundle:News:getCategoryGroups',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_category_groups_post',
    [
        'path'         => '/news/categories/{category_id}/groups',
        'controller'   => 'LegacyApiBundle:News:postCategoryGroups',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_news_category_group',
    [
        'path'         => '/news/categories/{category_id}/groups/{group_id}',
        'controller'   => 'LegacyApiBundle:News:getCategoryGroup',
        'requirements' => ['category_id' => '\\d+', 'group_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_news_category_group_delete',
    [
        'path'         => '/news/categories/{category_id}/groups/{group_id}',
        'controller'   => 'LegacyApiBundle:News:deleteCategoryGroup',
        'requirements' => ['category_id' => '\\d+', 'group_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb',
    [
        'path'       => '/kb',
        'controller' => 'LegacyApiBundle:Kb:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_kb_post',
    [
        'path'       => '/kb',
        'controller' => 'LegacyApiBundle:Kb:newArticle',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_kb_article',
    [
        'path'         => '/kb/{article_id}',
        'controller'   => 'LegacyApiBundle:Kb:getArticle',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_post',
    [
        'path'         => '/kb/{article_id}',
        'controller'   => 'LegacyApiBundle:Kb:postArticle',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_article_delete',
    [
        'path'         => '/kb/{article_id}',
        'controller'   => 'LegacyApiBundle:Kb:deleteArticle',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_article_votes',
    [
        'path'         => '/kb/{article_id}/votes',
        'controller'   => 'LegacyApiBundle:Kb:getArticleVotes',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_comments',
    [
        'path'         => '/kb/{article_id}/comments',
        'controller'   => 'LegacyApiBundle:Kb:getArticleComments',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_comments_new',
    [
        'path'         => '/kb/{article_id}/comments',
        'controller'   => 'LegacyApiBundle:Kb:newArticleComment',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_article_comments_comment',
    [
        'path'         => '/kb/{article_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Kb:getArticleComment',
        'requirements' => ['article_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_comments_comment_post',
    [
        'path'         => '/kb/{article_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Kb:postArticleComment',
        'requirements' => ['article_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_article_comments_comment_delete',
    [
        'path'         => '/kb/{article_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Kb:deleteArticleComment',
        'requirements' => ['article_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_article_attachments',
    [
        'path'         => '/kb/{article_id}/attachments',
        'controller'   => 'LegacyApiBundle:Kb:getArticleAttachments',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_attachments_post',
    [
        'path'         => '/kb/{article_id}/attachments',
        'controller'   => 'LegacyApiBundle:Kb:newArticleAttachment',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_article_attachment',
    [
        'path'         => '/kb/{article_id}/attachments/{attachment_id}',
        'controller'   => 'LegacyApiBundle:Kb:getArticleAttachment',
        'requirements' => ['article_id' => '\\d+', 'attachment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_attachment_delete',
    [
        'path'         => '/kb/{article_id}/attachments/{attachment_id}',
        'controller'   => 'LegacyApiBundle:Kb:deleteArticleAttachment',
        'requirements' => ['article_id' => '\\d+', 'attachment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_article_labels',
    [
        'path'         => '/kb/{article_id}/labels',
        'controller'   => 'LegacyApiBundle:Kb:getArticleLabels',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_labels_post',
    [
        'path'         => '/kb/{article_id}/labels',
        'controller'   => 'LegacyApiBundle:Kb:postArticleLabels',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_article_label',
    [
        'path'         => '/kb/{article_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Kb:getArticleLabel',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_article_label_delete',
    [
        'path'         => '/kb/{article_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Kb:deleteArticleLabel',
        'requirements' => ['article_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_validating_comments',
    [
        'path'       => '/kb/validating-comments',
        'controller' => 'LegacyApiBundle:Kb:getValidatingComments',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_kb_categories',
    [
        'path'       => '/kb/categories',
        'controller' => 'LegacyApiBundle:Kb:getCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_kb_categories_post',
    [
        'path'       => '/kb/categories',
        'controller' => 'LegacyApiBundle:Kb:postCategories',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_kb_category',
    [
        'path'         => '/kb/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:Kb:getCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_category_post',
    [
        'path'         => '/kb/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:Kb:postCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_category_delete',
    [
        'path'         => '/kb/categories/{category_id}',
        'controller'   => 'LegacyApiBundle:Kb:deleteCategory',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_category_articles',
    [
        'path'         => '/kb/categories/{category_id}/articles',
        'controller'   => 'LegacyApiBundle:Kb:getCategoryArticles',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_category_groups',
    [
        'path'         => '/kb/categories/{category_id}/groups',
        'controller'   => 'LegacyApiBundle:Kb:getCategoryGroups',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_category_groups_post',
    [
        'path'         => '/kb/categories/{category_id}/groups',
        'controller'   => 'LegacyApiBundle:Kb:postCategoryGroups',
        'requirements' => ['category_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_category_group',
    [
        'path'         => '/kb/categories/{category_id}/groups/{group_id}',
        'controller'   => 'LegacyApiBundle:Kb:getCategoryGroup',
        'requirements' => ['category_id' => '\\d+', 'group_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_category_group_delete',
    [
        'path'         => '/kb/categories/{category_id}/groups/{group_id}',
        'controller'   => 'LegacyApiBundle:Kb:deleteCategoryGroup',
        'requirements' => ['category_id' => '\\d+', 'group_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_fields_old',
    [
        'path'       => '/kb/fields',
        'controller' => 'LegacyApiBundle:Kb:getFields',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_kb_products',
    [
        'path'       => '/kb/products',
        'controller' => 'LegacyApiBundle:Kb:getProducts',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback',
    [
        'path'       => '/feedback',
        'controller' => 'LegacyApiBundle:Feedback:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_post',
    [
        'path'       => '/feedback',
        'controller' => 'LegacyApiBundle:Feedback:newFeedback',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback',
    [
        'path'         => '/feedback/{feedback_id}',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedback',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_post',
    [
        'path'         => '/feedback/{feedback_id}',
        'controller'   => 'LegacyApiBundle:Feedback:postFeedback',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback_delete',
    [
        'path'         => '/feedback/{feedback_id}',
        'controller'   => 'LegacyApiBundle:Feedback:deleteFeedback',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_feedback_feedback_votes',
    [
        'path'         => '/feedback/{feedback_id}/votes',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackVotes',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_comments',
    [
        'path'         => '/feedback/{feedback_id}/comments',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackComments',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_comments_new',
    [
        'path'         => '/feedback/{feedback_id}/comments',
        'controller'   => 'LegacyApiBundle:Feedback:newFeedbackComment',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback_comments_comment',
    [
        'path'         => '/feedback/{feedback_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackComment',
        'requirements' => ['feedback_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_comments_comment_post',
    [
        'path'         => '/feedback/{feedback_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Feedback:postFeedbackComment',
        'requirements' => ['feedback_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback_comments_comment_delete',
    [
        'path'         => '/feedback/{feedback_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Feedback:deleteFeedbackComment',
        'requirements' => ['feedback_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_feedback_feedback_merge',
    [
        'path'         => '/feedback/{feedback_id}/merge/{other_feedback_id}',
        'controller'   => 'LegacyApiBundle:Feedback:mergeFeedback',
        'requirements' => ['feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback_attachments',
    [
        'path'         => '/feedback/{feedback_id}/attachments',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackAttachments',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_attachments_post',
    [
        'path'         => '/feedback/{feedback_id}/attachments',
        'controller'   => 'LegacyApiBundle:Feedback:newFeedbackAttachment',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback_attachment',
    [
        'path'         => '/feedback/{feedback_id}/attachments/{attachment_id}',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackAttachment',
        'requirements' => ['feedback_id' => '\\d+', 'attachment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_attachment_delete',
    [
        'path'         => '/feedback/{feedback_id}/attachments/{attachment_id}',
        'controller'   => 'LegacyApiBundle:Feedback:deleteFeedbackAttachment',
        'requirements' => ['feedback_id' => '\\d+', 'attachment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_feedback_feedback_labels',
    [
        'path'         => '/feedback/{feedback_id}/labels',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackLabels',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_labels_post',
    [
        'path'         => '/feedback/{feedback_id}/labels',
        'controller'   => 'LegacyApiBundle:Feedback:postFeedbackLabels',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_feedback_feedback_label',
    [
        'path'         => '/feedback/{feedback_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Feedback:getFeedbackLabel',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_feedback_feedback_label_delete',
    [
        'path'         => '/feedback/{feedback_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Feedback:deleteFeedbackLabel',
        'requirements' => ['feedback_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_feedback_validating_comments',
    [
        'path'       => '/feedback/validating-comments',
        'controller' => 'LegacyApiBundle:Feedback:getValidatingComments',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_categories',
    [
        'path'       => '/feedback/categories',
        'controller' => 'LegacyApiBundle:Feedback:getCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_status_categories',
    [
        'path'       => '/feedback/status-categories',
        'controller' => 'LegacyApiBundle:Feedback:getStatusCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_user_categories',
    [
        'path'       => '/feedback/user-categories',
        'controller' => 'LegacyApiBundle:Feedback:getUserCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tasks',
    [
        'path'       => '/tasks',
        'controller' => 'LegacyApiBundle:Task:search',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tasks_post',
    [
        'path'       => '/tasks',
        'controller' => 'LegacyApiBundle:Task:newTask',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_tasks_task',
    [
        'path'         => '/tasks/{task_id}',
        'controller'   => 'LegacyApiBundle:Task:getTask',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_post',
    [
        'path'         => '/tasks/{task_id}',
        'controller'   => 'LegacyApiBundle:Task:postTask',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tasks_task_delete',
    [
        'path'         => '/tasks/{task_id}',
        'controller'   => 'LegacyApiBundle:Task:deleteTask',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tasks_task_associations',
    [
        'path'         => '/tasks/{task_id}/associations',
        'controller'   => 'LegacyApiBundle:Task:getTaskAssociations',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_associations_post',
    [
        'path'         => '/tasks/{task_id}/associations',
        'controller'   => 'LegacyApiBundle:Task:postTaskAssociations',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tasks_task_associated_item',
    [
        'path'         => '/tasks/{task_id}/associations/{assoc_id}',
        'controller'   => 'LegacyApiBundle:Task:getTaskAssociation',
        'requirements' => ['task_id' => '\\d+', 'assoc_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_comments',
    [
        'path'         => '/tasks/{task_id}/comments',
        'controller'   => 'LegacyApiBundle:Task:getTaskComments',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_comments_post',
    [
        'path'         => '/tasks/{task_id}/comments',
        'controller'   => 'LegacyApiBundle:Task:postTaskComments',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tasks_task_comment',
    [
        'path'         => '/tasks/{task_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Task:getTaskComment',
        'requirements' => ['task_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_associated_item_delete',
    [
        'path'         => '/tasks/{task_id}/comments/{comment_id}',
        'controller'   => 'LegacyApiBundle:Task:deleteTaskComment',
        'requirements' => ['task_id' => '\\d+', 'comment_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_tasks_task_labels',
    [
        'path'         => '/tasks/{task_id}/labels',
        'controller'   => 'LegacyApiBundle:Task:getTaskLabels',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_labels_post',
    [
        'path'         => '/tasks/{task_id}/labels',
        'controller'   => 'LegacyApiBundle:Task:postTaskLabels',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_tasks_task_label',
    [
        'path'         => '/tasks/{task_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Task:getTaskLabel',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_tasks_task_label_delete',
    [
        'path'         => '/tasks/{task_id}/labels/{label}',
        'controller'   => 'LegacyApiBundle:Task:deleteTaskLabel',
        'requirements' => ['task_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_glossary',
    [
        'path'       => '/glossary',
        'controller' => 'LegacyApiBundle:Glossary:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_glossary_lookup',
    [
        'path'       => '/glossary/lookup',
        'controller' => 'LegacyApiBundle:Glossary:lookup',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_glossary_post',
    [
        'path'       => '/glossary',
        'controller' => 'LegacyApiBundle:Glossary:newWord',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_glossary_word',
    [
        'path'         => '/glossary/{word_id}',
        'controller'   => 'LegacyApiBundle:Glossary:getWord',
        'requirements' => ['word_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_glossary_word_delete',
    [
        'path'         => '/glossary/{word_id}',
        'controller'   => 'LegacyApiBundle:Glossary:deleteWord',
        'requirements' => ['word_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_glossary_definition',
    [
        'path'         => '/glossary/definitions/{definition_id}',
        'controller'   => 'LegacyApiBundle:Glossary:getDefinition',
        'requirements' => ['definition_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_glossary_definition_post',
    [
        'path'         => '/glossary/definitions/{definition_id}',
        'controller'   => 'LegacyApiBundle:Glossary:postDefinition',
        'requirements' => ['definition_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_glossary_definition_delete',
    [
        'path'         => '/glossary/definitions/{definition_id}',
        'controller'   => 'LegacyApiBundle:Glossary:deleteDefinition',
        'requirements' => ['definition_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_dismiss_activity',
    [
        'path'       => '/activity/dismiss',
        'controller' => 'LegacyApiBundle:Activity:dismiss',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_get_activity',
    [
        'path'         => '/activity/{since}',
        'controller'   => 'LegacyApiBundle:Activity:getActivity',
        'requirements' => ['since' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

//#######################################################################################################################
// Label Management
//#######################################################################################################################

$label_types = [
    ['route' => 'ticket', 'route_url' => 'ticket', 'controller' => 'TicketLabels'],
    ['route' => 'person', 'route_url' => 'person', 'controller' => 'PersonLabels'],
    ['route' => 'org', 'route_url' => 'org', 'controller' => 'OrgLabels'],
    ['route' => 'feedback', 'route_url' => 'feedback', 'controller' => 'FeedbackLabels'],
    ['route' => 'chat', 'route_url' => 'chat', 'controller' => 'ChatLabels'],
    ['route' => 'kb', 'route_url' => 'kb', 'controller' => 'KbLabels'],
    ['route' => 'news', 'route_url' => 'news', 'controller' => 'NewsLabels'],
    ['route' => 'downloads', 'route_url' => 'downloads', 'controller' => 'DownloadsLabels'],
];

foreach ($label_types as $info) {
    $collection->create(
        "api_{$info['route']}_labels",
        [
            'path'       => "/{$info['route_url']}_labels",
            'controller' => "LegacyApiBundle:{$info['controller']}:list",
            'methods'    => ['GET'],
        ]
    );

    $collection->create(
        "api_{$info['route']}_labels_get",
        [
            'path'       => "/{$info['route_url']}_labels/get",
            'controller' => "LegacyApiBundle:{$info['controller']}:get",
            'methods'    => ['GET'],
        ]
    );

    $collection->create(
        "api_{$info['route']}_labels_save",
        [
            'path'       => "/{$info['route_url']}_labels/save",
            'controller' => "LegacyApiBundle:{$info['controller']}:save",
            'methods'    => ['POST'],
        ]
    );

    $collection->create(
        "api_{$info['route']}_labels_add",
        [
            'path'       => "/{$info['route_url']}_labels",
            'controller' => "LegacyApiBundle:{$info['controller']}:add",
            'methods'    => ['POST'],
        ]
    );

    $collection->create(
        "api_{$info['route']}_labels_remove",
        [
            'path'       => "/{$info['route_url']}_labels",
            'controller' => "LegacyApiBundle:{$info['controller']}:remove",
            'methods'    => ['DELETE'],
        ]
    );
}

//#######################################################################################################################
// Round Robin
//#######################################################################################################################

$collection->create(
    'api_roundrobins_list',
    [
        'path'       => '/round_robin',
        'controller' => 'LegacyApiBundle:RoundRobin:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_roundrobins_settings',
    [
        'path'       => '/round_robin/settings',
        'controller' => 'LegacyApiBundle:RoundRobin:settings',
        'methods'    => ['GET', 'PUT'],
    ]
);

$collection->create(
    'api_roundrobins_triggers',
    [
        'path'       => '/round_robin/triggers/{id}',
        'controller' => 'LegacyApiBundle:RoundRobin:checkTriggers',
        'defaults'   => ['id' => null],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_roundrobins_get',
    [
        'path'       => '/round_robin/{id}',
        'controller' => 'LegacyApiBundle:RoundRobin:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_roundrobins_set',
    [
        'path'       => '/round_robin/{id}',
        'controller' => 'LegacyApiBundle:RoundRobin:set',
        'methods'    => ['POST', 'PUT'],
        'defaults'   => ['id' => 0],
    ]
);

$collection->create(
    'api_roundrobins_delete',
    [
        'path'       => '/round_robin/{id}',
        'controller' => 'LegacyApiBundle:RoundRobin:delete',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_roundrobins_logs',
    [
        'path'       => '/round_robin/{id}/logs',
        'controller' => 'LegacyApiBundle:RoundRobin:logs',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Chat Round Robin
//#######################################################################################################################

$collection->create(
    'api_chat_roundrobins_list',
    [
        'path'       => '/chat_round_robin',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chat_roundrobins_settings',
    [
        'path'       => '/chat_round_robin/settings',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:settings',
        'methods'    => ['GET', 'PUT'],
    ]
);

$collection->create(
    'api_chat_roundrobins_triggers',
    [
        'path'       => '/chat_round_robin/triggers/{id}',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:checkTriggers',
        'defaults'   => ['id' => null],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chat_roundrobins_get',
    [
        'path'       => '/chat_round_robin/{id}',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chat_roundrobins_set',
    [
        'path'       => '/chat_round_robin/{id}',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:set',
        'methods'    => ['POST', 'PUT'],
        'defaults'   => ['id' => 0],
    ]
);

$collection->create(
    'api_chat_roundrobins_delete',
    [
        'path'       => '/chat_round_robin/{id}',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:delete',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_chat_roundrobins_logs',
    [
        'path'       => '/chat_round_robin/{id}/logs',
        'controller' => 'LegacyApiBundle:ChatRoundRobin:logs',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Start Settings
//#######################################################################################################################

$collection->create(
    'api_startsettings_set',
    [
        'path'       => '/start-settings',
        'controller' => 'LegacyApiBundle:Settings:setStartSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_startsettings_setinitial',
    [
        'path'       => '/start-settings/set-initial',
        'controller' => 'LegacyApiBundle:Settings:setDoneInitial',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Settings
//#######################################################################################################################

$collection->create(
    'api_settings_values_get',
    [
        'path'       => '/settings/values/{name}',
        'controller' => 'LegacyApiBundle:Settings:getValue',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_settings_values_set',
    [
        'path'       => '/settings/values/{name}',
        'controller' => 'LegacyApiBundle:Settings:setValue',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Portal Settings
//#######################################################################################################################

$collection->create(
    'api_settings_portal_general',
    [
        'path'       => '/settings/portal/general',
        'controller' => 'LegacyApiBundle:Settings:portalSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_settings_portal_general_save',
    [
        'path'       => '/settings/portal/general',
        'controller' => 'LegacyApiBundle:Settings:savePortalSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Portal App Settings
//#######################################################################################################################

$collection->create('api_settings_portalapps', [
    'path'       => '/settings/portal/{app}',
    'controller' => 'LegacyApiBundle:Settings:portalAppSettings',
    'methods'    => ['GET'],
]);

$collection->create('api_settings_portalapps_save', [
    'path'       => '/settings/portal/{app}',
    'controller' => 'LegacyApiBundle:Settings:savePortalAppSettings',
    'methods'    => ['POST'],
]);

//#######################################################################################################################
// Server Settings
//#######################################################################################################################

$collection->create(
    'api_server_settings',
    [
        'path'       => '/server_settings',
        'controller' => 'LegacyApiBundle:Settings:serverSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_settings_save',
    [
        'path'       => '/server_settings',
        'controller' => 'LegacyApiBundle:Settings:saveServerSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// General Settings
//#######################################################################################################################

$collection->create(
    'api_general_settings_get_logo_blob',
    [
        'path'       => '/general_settings/blob',
        'controller' => 'LegacyApiBundle:Settings:getLogoBlob',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_general_settings_set_logo_blob',
    [
        'path'       => '/general_settings/blob',
        'controller' => 'LegacyApiBundle:Settings:setLogoBlob',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_general_settings',
    [
        'path'       => '/general_settings',
        'controller' => 'LegacyApiBundle:Settings:generalSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_general_settings_save',
    [
        'path'       => '/general_settings',
        'controller' => 'LegacyApiBundle:Settings:saveGeneralSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Usersources
//#######################################################################################################################

$collection->create(
    'api_usersources_start_sync',
    [
        'path'       => '/usersources/start-sync',
        'controller' => 'LegacyApiBundle:Usersources:startUsersourceSync',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_usersources_list',
    [
        'path'       => '/usersources/{type}',
        'controller' => 'LegacyApiBundle:Usersources:listByType',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersources_iframe',
    [
        'path'       => '/usersources/iframe/code/{interface}/{app_id}',
        'controller' => 'LegacyApiBundle:Usersources:getIframe',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersources_display_order',
    [
        'path'       => '/usersources/display-order',
        'controller' => 'LegacyApiBundle:Usersources:updateDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_usersources_sync_status',
    [
        'path'       => '/usersources/sync/status',
        'controller' => 'LegacyApiBundle:Usersources:syncStatus',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersources_sync_start',
    [
        'path'       => '/usersources/sync/start',
        'controller' => 'LegacyApiBundle:Usersources:syncStart',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_usersources_sync_stop',
    [
        'path'       => '/usersources/sync/stop',
        'controller' => 'LegacyApiBundle:Usersources:syncStop',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_usersources_sync_info',
    [
        'path'       => '/usersources/sync/info/{app_id}',
        'controller' => 'LegacyApiBundle:Usersources:getSyncInformation',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersources_available_apps',
    [
        'path'       => '/usersources/available/app-packages/{interface}',
        'controller' => 'LegacyApiBundle:Usersources:availableAppPackages',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersources_get',
    [
        'path'       => '/usersources/{type}/{id}',
        'controller' => 'LegacyApiBundle:Usersources:getUsersource',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersources_post',
    [
        'path'       => '/usersources/{type}/{id}',
        'controller' => 'LegacyApiBundle:Usersources:postUsersource',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_usersources_extra_details',
    [
        'path'       => '/usersources/{type}/app-{app_id}/extra-details',
        'controller' => 'LegacyApiBundle:Usersources:getUsersourceExtra',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usersource_refresh_person',
    [
        'path'       => '/usersources/{usersource_id}/person-refresh/{identity_or_email}',
        'controller' => 'LegacyApiBundle:Usersources:personRefresh',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Registration Settings

$collection->create(
    'api_reg_settings',
    [
        'path'       => '/registration_settings',
        'controller' => 'LegacyApiBundle:Settings:registrationSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reg_settings_save',
    [
        'path'       => '/registration_settings',
        'controller' => 'LegacyApiBundle:Settings:saveRegistrationSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_pass_settings',
    [
        'path'       => '/password_settings',
        'controller' => 'LegacyApiBundle:Settings:passwordSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_pass_settings_save',
    [
        'path'       => '/password_settings',
        'controller' => 'LegacyApiBundle:Settings:savePasswordSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Portal Settings
//#######################################################################################################################

$collection->create(
    'api_portal_settings',
    [
        'path'       => '/portal_settings',
        'controller' => 'LegacyApiBundle:Settings:portalSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_portal_settings_save',
    [
        'path'       => '/portal_settings',
        'controller' => 'LegacyApiBundle:Settings:savePortalSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_portal_settings_setfavicon',
    [
        'path'         => '/portal_settings/favicon/{blob_id}/{blob_auth}',
        'controller'   => 'LegacyApiBundle:Settings:saveCustomFavicon',
        'methods'      => ['POST'],
        'requirements' => ['blob_id' => '\\d+', 'blob_auth' => '[a-zA-Z0-9]+'],
    ]
);

//#######################################################################################################################
// Advanced Settings
//#######################################################################################################################

$collection->create(
    'api_all_settings_raw',
    [
        'path'       => '/all_settings_raw',
        'controller' => 'LegacyApiBundle:Settings:allSettingsRaw',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_all_settings_raw_save',
    [
        'path'       => '/all_settings_raw',
        'controller' => 'LegacyApiBundle:Settings:saveAllSettingsRaw',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Elastic Search
//#######################################################################################################################

$collection->create(
    'api_elastic_settings',
    [
        'path'       => '/elastic-search/settings',
        'controller' => 'LegacyApiBundle:ElasticSearch:getSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_elastic_index_status',
    [
        'path'       => '/elastic-search/index-status',
        'controller' => 'LegacyApiBundle:ElasticSearch:indexStatus',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_elastic_settings_save',
    [
        'path'       => '/elastic-search/settings',
        'controller' => 'LegacyApiBundle:ElasticSearch:saveSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_elastic_settings_test',
    [
        'path'       => '/elastic-search/settings/test',
        'controller' => 'LegacyApiBundle:ElasticSearch:testSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// License
//#######################################################################################################################

$collection->create(
    'api_dp_license',
    [
        'path'       => '/dp_license',
        'controller' => 'LegacyApiBundle:License:getLicense',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_dp_license_save',
    [
        'path'       => '/dp_license',
        'controller' => 'LegacyApiBundle:License:setLicense',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_dp_keyfile',
    [
        'path'         => '/dp_license/keyfile.{_format}',
        'controller'   => 'LegacyApiBundle:License:downloadKeyfile',
        'methods'      => ['GET'],
        'requirements' => ['_format' => 'txt|json'],
    ]
);

$collection->create(
    'api_dp_license_supportrequest',
    [
        'path'       => '/dp_license/support-request',
        'controller' => 'LegacyApiBundle:License:sendSupportRequest',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_dp_license_versioninfo',
    [
        'path'       => '/dp_license/version-info',
        'controller' => 'LegacyApiBundle:License:getVersionInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_dp_license_latestversion',
    [
        'path'       => '/dp_license/latest-version-info',
        'controller' => 'LegacyApiBundle:License:getLatestVersion',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_dp_license_news',
    [
        'path'       => '/dp_license/news',
        'controller' => 'LegacyApiBundle:License:getNews',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Ticket Settings
//#######################################################################################################################

$collection->create(
    'api_ticket_settings',
    [
        'path'       => '/ticket_settings',
        'controller' => 'LegacyApiBundle:Settings:ticketSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_settings_save',
    [
        'path'       => '/ticket_settings',
        'controller' => 'LegacyApiBundle:Settings:saveTicketSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_fwd_settings',
    [
        'path'       => '/ticket_settings/fwd',
        'controller' => 'LegacyApiBundle:Settings:ticketFwdSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_fwd_settings_save',
    [
        'path'       => '/ticket_settings/fwd',
        'controller' => 'LegacyApiBundle:Settings:saveTicketFwdSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Reg Settings
//#######################################################################################################################

$collection->create(
    'api_registration_settings',
    [
        'path'       => '/registraton_settings',
        'controller' => 'LegacyApiBundle:Settings:registrationSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_registration_settings_save',
    [
        'path'       => '/registraton_settings',
        'controller' => 'LegacyApiBundle:Settings:saveRegistrationSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Products
//#######################################################################################################################

$collection->create(
    'api_products',
    [
        'path'       => '/products',
        'controller' => 'LegacyApiBundle:TicketFields:listPriorities',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Ticket Departments
//#######################################################################################################################

$collection->create(
    'api_ticket_deps',
    [
        'path'       => '/ticket_deps',
        'controller' => 'LegacyApiBundle:TicketDeps:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_deps_create',
    [
        'path'       => '/ticket_deps',
        'controller' => 'LegacyApiBundle:TicketDeps:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_deps_order',
    [
        'path'       => '/ticket_deps/display_order',
        'controller' => 'LegacyApiBundle:TicketDeps:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_deps_settings',
    [
        'path'       => '/ticket_deps/settings',
        'controller' => 'LegacyApiBundle:TicketDeps:getSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_deps_settingssave',
    [
        'path'       => '/ticket_deps/settings',
        'controller' => 'LegacyApiBundle:TicketDeps:saveSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_deps_get',
    [
        'path'       => '/ticket_deps/{id}',
        'controller' => 'LegacyApiBundle:TicketDeps:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_deps_save',
    [
        'path'       => '/ticket_deps/{id}',
        'controller' => 'LegacyApiBundle:TicketDeps:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_deps_remove',
    [
        'path'       => '/ticket_deps/{id}',
        'controller' => 'LegacyApiBundle:TicketDeps:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Ticket Layouts
//#######################################################################################################################

$collection->create(
    'api_ticket_layout_get',
    [
        'path'         => '/ticket_layouts/{dep_id}',
        'controller'   => 'LegacyApiBundle:TicketLayouts:get',
        'requirements' => ['dep_id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_ticket_layout_getdefault',
    [
        'path'       => '/ticket_layouts/default',
        'controller' => 'LegacyApiBundle:TicketLayouts:get',
        'defaults'   => ['dep_id' => '0'],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_layout_save',
    [
        'path'         => '/ticket_layouts/{dep_id}',
        'controller'   => 'LegacyApiBundle:TicketLayouts:save',
        'requirements' => ['dep_id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_ticket_layout_delete',
    [
        'path'         => '/ticket_layouts/{dep_id}',
        'controller'   => 'LegacyApiBundle:TicketLayouts:delete',
        'requirements' => ['dep_id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_ticket_layout_savedefault',
    [
        'path'       => '/ticket_layouts/default',
        'controller' => 'LegacyApiBundle:TicketLayouts:save',
        'defaults'   => ['dep_id' => '0'],
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_layout_stats',
    [
        'path'       => '/ticket_layouts/stats',
        'controller' => 'LegacyApiBundle:TicketLayouts:getLayoutStats',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_layout_field_status',
    [
        'path'       => '/ticket_layouts/fields/{field_id}',
        'controller' => 'LegacyApiBundle:TicketLayouts:getFieldStatus',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_layout_field_status_save',
    [
        'path'       => '/ticket_layouts/fields/{field_id}',
        'controller' => 'LegacyApiBundle:TicketLayouts:saveFieldStatus',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Products
//#######################################################################################################################

$collection->create(
    'api_products',
    [
        'path'       => '/ticket_prods',
        'controller' => 'LegacyApiBundle:TicketFields:listProducts',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_products_save',
    [
        'path'       => '/ticket_prods',
        'controller' => 'LegacyApiBundle:TicketFields:saveProducts',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket Categoriesw
//#######################################################################################################################

$collection->create(
    'api_ticket_cats',
    [
        'path'       => '/ticket_cats',
        'controller' => 'LegacyApiBundle:TicketFields:listCategories',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_cats_save',
    [
        'path'       => '/ticket_cats',
        'controller' => 'LegacyApiBundle:TicketFields:saveCategories',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket Workflows
//#######################################################################################################################

$collection->create(
    'api_ticket_works',
    [
        'path'       => '/ticket_works',
        'controller' => 'LegacyApiBundle:TicketFields:listWorkflows',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_works_save',
    [
        'path'       => '/ticket_works',
        'controller' => 'LegacyApiBundle:TicketFields:saveWorkflows',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket Priorities
//#######################################################################################################################

$collection->create(
    'api_ticket_pris',
    [
        'path'       => '/ticket_pris',
        'controller' => 'LegacyApiBundle:TicketFields:listPriorities',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_pris_save',
    [
        'path'       => '/ticket_pris',
        'controller' => 'LegacyApiBundle:TicketFields:savePriorities',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket Statuses
//#######################################################################################################################

$collection->create(
    'api_ticket_statuses_stats',
    [
        'path'       => '/ticket_statuses/stats',
        'controller' => 'LegacyApiBundle:TicketStatuses:getStats',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_statuses_archived',
    [
        'path'       => '/ticket_statuses/archived',
        'controller' => 'LegacyApiBundle:TicketStatuses:getArchivedInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_statuses_archived_savesettings',
    [
        'path'       => '/ticket_statuses/archived/settings',
        'controller' => 'LegacyApiBundle:TicketStatuses:saveArchivedSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_statuses_archived_resetsearch',
    [
        'path'       => '/ticket_statuses/archived/reset-search-tables',
        'controller' => 'LegacyApiBundle:TicketStatuses:resetSearchTables',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_statuses_deleted',
    [
        'path'       => '/ticket_statuses/deleted',
        'controller' => 'LegacyApiBundle:TicketStatuses:getDeletedInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_statuses_deleted_purge',
    [
        'path'       => '/ticket_statuses/deleted/purge',
        'controller' => 'LegacyApiBundle:TicketStatuses:purgeDeleted',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_ticket_statuses_deleted_savesettings',
    [
        'path'       => '/ticket_statuses/deleted/settings',
        'controller' => 'LegacyApiBundle:TicketStatuses:saveDeletedSettings',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_statuses_spam',
    [
        'path'       => '/ticket_statuses/spam',
        'controller' => 'LegacyApiBundle:TicketStatuses:getSpamInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_statuses_spam_purge',
    [
        'path'       => '/ticket_statuses/spam/purge',
        'controller' => 'LegacyApiBundle:TicketStatuses:purgeSpam',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_ticket_statuses_spam_savesettings',
    [
        'path'       => '/ticket_statuses/spam/settings',
        'controller' => 'LegacyApiBundle:TicketStatuses:saveSpamSettings',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket SLAs
//#######################################################################################################################

$collection->create(
    'api_ticket_slas',
    [
        'path'       => '/ticket_slas',
        'controller' => 'LegacyApiBundle:TicketSlas:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_slascreate',
    [
        'path'       => '/ticket_slas',
        'controller' => 'LegacyApiBundle:TicketSlas:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_slasget',
    [
        'path'       => '/ticket_slas/{id}',
        'controller' => 'LegacyApiBundle:TicketSlas:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_slasupdate',
    [
        'path'       => '/ticket_slas/{id}',
        'controller' => 'LegacyApiBundle:TicketSlas:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_slasdelete',
    [
        'path'       => '/ticket_slas/{id}',
        'controller' => 'LegacyApiBundle:TicketSlas:delete',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Ticket Urgencies
//#######################################################################################################################

$collection->create(
    'api_ticket_urgencies',
    [
        'path'       => '/ticket_urgencies',
        'controller' => 'LegacyApiBundle:TicketUrgencies:list',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Ticket Fields
//#######################################################################################################################

$collection->create(
    'api_ticket_fields',
    [
        'path'       => '/ticket_fields',
        'controller' => 'LegacyApiBundle:TicketFields:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_fields_get',
    [
        'path'         => '/ticket_fields/{id}',
        'controller'   => 'LegacyApiBundle:TicketFields:getCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_ticket_fields_create',
    [
        'path'       => '/ticket_fields',
        'controller' => 'LegacyApiBundle:TicketFields:saveCustomField',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_fields_save',
    [
        'path'         => '/ticket_fields/{id}',
        'controller'   => 'LegacyApiBundle:TicketFields:saveCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_ticket_fields_delete',
    [
        'path'         => '/ticket_fields/{id}',
        'controller'   => 'LegacyApiBundle:TicketFields:deleteCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_ticket_fields_setenabled',
    [
        'path'       => '/ticket_fields/set-enabled/{field_id}/{is_enabled}',
        'controller' => 'LegacyApiBundle:TicketFields:toggleField',
        'methods'    => ['POST'],
    ]
);

$collection->create('api_ticket_fields_convert', [
    'path'         => '/ticket_fields/convert/{type}',
    'controller'   => 'LegacyApiBundle:TicketFields:convert',
    'methods'      => ['POST'],
    'requirements' => ['type' => 'categories|workflows|priorities|products'],
]);

//#######################################################################################################################
// SMS Channel
//#######################################################################################################################

$collection->create(
    'api_channel_sms_accounts',
    [
        'path'       => '/channel/sms/accounts',
        'controller' => 'LegacyApiBundle:ChannelSms:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_channel_sms_account_get',
    [
        'path'         => '/channel/sms/account/{id}',
        'requirements' => ['id' => '\d+'],
        'controller'   => 'LegacyApiBundle:ChannelSms:get',
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_channel_sms_account_delete',
    [
        'path'         => '/channel/sms/account/{id}',
        'requirements' => ['id' => '\d+'],
        'controller'   => 'LegacyApiBundle:ChannelSms:delete',
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_channel_sms_account_save',
    [
        'path'       => '/channel/sms/account/{id}',
        'controller' => 'LegacyApiBundle:ChannelSms:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_channel_sms_account_create',
    [
        'path'       => '/channel/sms/account',
        'controller' => 'LegacyApiBundle:ChannelSms:save',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_channel_sms_connect_provider',
    [
        'path'       => '/channel/sms/connect_provider',
        'controller' => 'LegacyApiBundle:ChannelSms:connectProvider',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_channel_sms_setup_and_test_twilio',
    [
        'path'       => '/channel/sms/setup-and-test/twilio',
        'controller' => 'LegacyApiBundle:ChannelSms:setupAndTestTwilio',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Facebook Channel
//#######################################################################################################################

$collection->create(
    'api_channel_facebook_pages',
    [
        'path'       => '/channel/facebook/pages',
        'controller' => 'LegacyApiBundle:ChannelFacebook:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_channel_facebook_pages_post',
    [
        'path'       => '/channel/facebook/pages',
        'controller' => 'LegacyApiBundle:ChannelFacebook:create',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_channel_facebook_page_get',
    [
        'path'         => '/channel/facebook/page/{id}',
        'requirements' => ['id' => '\d+'],
        'controller'   => 'LegacyApiBundle:ChannelFacebook:get',
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_channel_facebook_page_delete',
    [
        'path'         => '/channel/facebook/page/{id}',
        'requirements' => ['id' => '\d+'],
        'controller'   => 'LegacyApiBundle:ChannelFacebook:delete',
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_channel_facebook_page_save',
    [
        'path'       => '/channel/facebook/page/{id}',
        'controller' => 'LegacyApiBundle:ChannelFacebook:save',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Email Accounts
//#######################################################################################################################

$collection->create(
    'api_emailaccounts',
    [
        'path'       => '/email_accounts',
        'controller' => 'LegacyApiBundle:EmailAccounts:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_emailaccounts_settings_get',
    [
        'path'       => '/email_accounts/settings',
        'controller' => 'LegacyApiBundle:EmailAccounts:getSettings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_emailaccounts_settings_set',
    [
        'path'       => '/email_accounts/settings',
        'controller' => 'LegacyApiBundle:EmailAccounts:setSettings',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_emailaccounts_create',
    [
        'path'       => '/email_accounts',
        'controller' => 'LegacyApiBundle:EmailAccounts:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT', 'POST'],
    ]
);

$collection->create(
    'api_emailaccounts_test',
    [
        'path'       => '/email_accounts/test-account',
        'controller' => 'LegacyApiBundle:EmailAccounts:testAccount',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_emailaccounts_testoutgoing',
    [
        'path'       => '/email_accounts/test-outgoing-account',
        'controller' => 'LegacyApiBundle:EmailAccounts:testOutgoingAccount',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_emailaccounts_get',
    [
        'path'       => '/email_accounts/{id}',
        'controller' => 'LegacyApiBundle:EmailAccounts:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_emailaccounts_remove',
    [
        'path'       => '/email_accounts/{id}',
        'controller' => 'LegacyApiBundle:EmailAccounts:remove',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_emailaccounts_save',
    [
        'path'       => '/email_accounts/{id}',
        'controller' => 'LegacyApiBundle:EmailAccounts:save',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Email Status
//#######################################################################################################################

$collection->create(
    'api_emailstatus_sourcelist',
    [
        'path'       => '/email_status/sources',
        'controller' => 'LegacyApiBundle:EmailStatus:listSources',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_sourcestats',
    [
        'path'       => '/email_status/stats',
        'controller' => 'LegacyApiBundle:EmailStatus:sourcesStats',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_source_get',
    [
        'path'         => '/email_status/sources/{id}',
        'controller'   => 'LegacyApiBundle:EmailStatus:getSourceInfo',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_source_get_summary',
    [
        'path'         => '/email_status/sources/{id}/summary',
        'controller'   => 'LegacyApiBundle:EmailStatus:getSourceSummary',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_source_get_rendered',
    [
        'path'         => '/email_status/sources/{id}/rendered',
        'controller'   => 'LegacyApiBundle:EmailStatus:getSourceRendered',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_source_reprocess',
    [
        'path'         => '/email_status/sources/{id}/reprocess',
        'controller'   => 'LegacyApiBundle:EmailStatus:reprocessEmailSource',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_emailstatus_source_massactions',
    [
        'path'         => '/email_status/sources/mass-actions/{action}',
        'controller'   => 'LegacyApiBundle:EmailStatus:emailSourceMassActions',
        'requirements' => ['action' => '[a-z]+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_emailstatus_source_delete',
    [
        'path'         => '/email_status/sources/{id}',
        'controller'   => 'LegacyApiBundle:EmailStatus:deleteEmailSource',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_emailstatus_sendmaillist',
    [
        'path'       => '/email_status/sendmail',
        'controller' => 'LegacyApiBundle:EmailStatus:listSendmail',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_sendmail_massactions',
    [
        'path'         => '/email_status/sendmail/mass-actions/{action}',
        'controller'   => 'LegacyApiBundle:EmailStatus:sendmailMassActions',
        'requirements' => ['action' => '[a-z]+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_emailstatus_sendmail_delete',
    [
        'path'         => '/email_status/sendmail/{id}',
        'controller'   => 'LegacyApiBundle:EmailStatus:deleteSendmail',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_emailstatus_sendmail_get_summary',
    [
        'path'         => '/email_status/sendmail/{id}/summary',
        'controller'   => 'LegacyApiBundle:EmailStatus:getSendmailSummary',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_sendmail_get_rendered',
    [
        'path'         => '/email_status/sendmail/{id}/rendered',
        'controller'   => 'LegacyApiBundle:EmailStatus:getSendmailRendered',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_emailstatus_sendmail_resend',
    [
        'path'         => '/email_status/sendmail/{id}/resend',
        'controller'   => 'LegacyApiBundle:EmailStatus:resendSendmail',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_emailstatus_sendmail_get',
    [
        'path'         => '/email_status/sendmail/{id}',
        'controller'   => 'LegacyApiBundle:EmailStatus:getSendmailInfo',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

//#######################################################################################################################
// Ticket Triggers
//#######################################################################################################################

$collection->create(
    'api_ticket_triggers_getappevents',
    [
        'path'       => '/ticket_triggers/app-events/{type}',
        'controller' => 'LegacyApiBundle:TicketTriggers:getAppEvents',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_triggers_getcustomactions',
    [
        'path'       => '/ticket_triggers/get-custom-actions',
        'controller' => 'LegacyApiBundle:TicketTriggers:getCustomActions',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_triggers_getspecial',
    [
        'path'         => '/ticket_triggers/{special_type}/{id}',
        'controller'   => 'LegacyApiBundle:TicketTriggers:get',
        'requirements' => [
            'special_type' => '(departments|departments_changed|email_accounts|satisfaction)',
            'id'           => '\d+',
        ],
        'methods' => ['GET'],
    ]
);

$collection->create(
    'api_ticket_triggers_updatespecial',
    [
        'path'         => '/ticket_triggers/{special_type}/{id}',
        'controller'   => 'LegacyApiBundle:TicketTriggers:save',
        'requirements' => [
            'special_type' => '(departments|departments_changed|email_accounts|satisfaction)',
            'id'           => '\d+',
        ],
        'methods' => ['POST'],
    ]
);

$collection->create(
    'api_ticket_triggers',
    [
        'path'         => '/ticket_triggers/{type}',
        'controller'   => 'LegacyApiBundle:TicketTriggers:list',
        'requirements' => ['type' => '(all|newticket|newreply|update)'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_ticket_triggers_updateorder',
    [
        'path'       => '/ticket_triggers/run_order',
        'controller' => 'LegacyApiBundle:TicketTriggers:saveRunOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_triggers_create',
    [
        'path'       => '/ticket_triggers',
        'controller' => 'LegacyApiBundle:TicketTriggers:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_triggers_get',
    [
        'path'         => '/ticket_triggers/{id}',
        'controller'   => 'LegacyApiBundle:TicketTriggers:get',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_ticket_triggers_update',
    [
        'path'         => '/ticket_triggers/{id}',
        'controller'   => 'LegacyApiBundle:TicketTriggers:save',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_ticket_triggers_delete',
    [
        'path'         => '/ticket_triggers/{id}',
        'controller'   => 'LegacyApiBundle:TicketTriggers:delete',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_ticket_triggers_enabletriggergroup',
    [
        'path'         => '/ticket_triggers/{special_type}/enable',
        'defaults'     => ['is_enabled' => true],
        'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTriggerGroup',
        'requirements' => [
            'special_type' => '(departments|departments_changed|email_accounts|satisfaction)',
            'id'           => '\d+',
        ],
        'methods' => ['POST'],
    ]
);

$collection->create(
    'api_ticket_triggers_disabletriggergroup',
    [
        'path'         => '/ticket_triggers/{special_type}/disable',
        'defaults'     => ['is_enabled' => false],
        'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTriggerGroup',
        'requirements' => [
            'special_type' => '(departments|departments_changed|email_accounts|satisfaction)',
            'id'           => '\d+',
        ],
        'methods' => ['POST'],
    ]
);

$collection->create(
    'api_ticket_triggers_enabletrigger',
    [
        'path'         => '/ticket_triggers/{id}/enable',
        'defaults'     => ['is_enabled' => true],
        'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTrigger',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_ticket_triggers_disabletrigger',
    [
        'path'         => '/ticket_triggers/{id}/disable',
        'defaults'     => ['is_enabled' => false],
        'controller'   => 'LegacyApiBundle:TicketTriggers:toggleTrigger',
        'requirements' => ['id' => '\d+'],
        'methods'      => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket Escalations
//#######################################################################################################################

$collection->create(
    'api_ticket_escalations',
    [
        'path'       => '/ticket_escalations',
        'controller' => 'LegacyApiBundle:TicketEscalations:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_escalations_updateorder',
    [
        'path'       => '/ticket_escalations/run_order',
        'controller' => 'LegacyApiBundle:TicketEscalations:saveRunOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_escalations_create',
    [
        'path'       => '/ticket_escalations',
        'controller' => 'LegacyApiBundle:TicketEscalations:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_escalations_get',
    [
        'path'       => '/ticket_escalations/{id}',
        'controller' => 'LegacyApiBundle:TicketEscalations:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_escalations_update',
    [
        'path'       => '/ticket_escalations/{id}',
        'controller' => 'LegacyApiBundle:TicketEscalations:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_escalations_getspecial',
    [
        'path'         => '/ticket_escalations/{special_type}/{id}',
        'controller'   => 'LegacyApiBundle:TicketEscalations:get',
        'requirements' => ['special_type' => '(satisfaction|statuses)', 'id' => '\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_ticket_escalations_updatespecial',
    [
        'path'         => '/ticket_escalations/{special_type}/{id}',
        'controller'   => 'LegacyApiBundle:TicketEscalations:save',
        'requirements' => ['special_type' => '(satisfaction|statuses)', 'id' => '\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_ticket_escalations_delete',
    [
        'path'       => '/ticket_escalations/{id}',
        'controller' => 'LegacyApiBundle:TicketEscalations:delete',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_ticket_escalations_enable',
    [
        'path'       => '/ticket_escalations/{id}/enable',
        'defaults'   => ['is_enabled' => true],
        'controller' => 'LegacyApiBundle:TicketEscalations:toggleEscalation',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_escalations_disable',
    [
        'path'       => '/ticket_escalations/{id}/disable',
        'defaults'   => ['is_enabled' => false],
        'controller' => 'LegacyApiBundle:TicketEscalations:toggleEscalation',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Ticket Filters
//#######################################################################################################################

$collection->create(
    'api_ticket_filters',
    [
        'path'       => '/ticket_filters',
        'controller' => 'LegacyApiBundle:TicketFilters:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_filters_create',
    [
        'path'       => '/ticket_filters',
        'defaults'   => ['id' => '0'],
        'controller' => 'LegacyApiBundle:TicketFilters:save',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_filters_savedisplayorder',
    [
        'path'       => '/ticket_filters/display_order',
        'controller' => 'LegacyApiBundle:TicketFilters:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_filters_get',
    [
        'path'         => '/ticket_filters/{id}',
        'requirements' => ['id' => '\\d+'],
        'controller'   => 'LegacyApiBundle:TicketFilters:get',
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_ticket_filters_save',
    [
        'path'         => '/ticket_filters/{id}',
        'requirements' => ['id' => '\\d+'],
        'controller'   => 'LegacyApiBundle:TicketFilters:save',
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_ticket_filters_delete',
    [
        'path'         => '/ticket_filters/{id}',
        'requirements' => ['id' => '\\d+'],
        'controller'   => 'LegacyApiBundle:TicketFilters:remove',
        'methods'      => ['DELETE'],
    ]
);

//#######################################################################################################################
// Ticket Macros
//#######################################################################################################################

$collection->create(
    'api_ticket_macros',
    [
        'path'       => '/ticket_macros',
        'controller' => 'LegacyApiBundle:TicketMacros:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_macros_create',
    [
        'path'       => '/ticket_macros',
        'defaults'   => ['id' => '0'],
        'controller' => 'LegacyApiBundle:TicketMacros:save',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_ticket_macros_get',
    [
        'path'       => '/ticket_macros/{id}',
        'controller' => 'LegacyApiBundle:TicketMacros:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_ticket_macros_save',
    [
        'path'       => '/ticket_macros/{id}',
        'controller' => 'LegacyApiBundle:TicketMacros:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_ticket_macros_delete',
    [
        'path'       => '/ticket_macros/{id}',
        'controller' => 'LegacyApiBundle:TicketMacros:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Feedback Statuses
//#######################################################################################################################

$collection->create(
    'api_feedback_statuses',
    [
        'path'       => '/feedback_statuses',
        'controller' => 'LegacyApiBundle:FeedbackStatuses:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_statuses_order',
    [
        'path'       => '/feedback_statuses/display_order',
        'controller' => 'LegacyApiBundle:FeedbackStatuses:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_statuses_get',
    [
        'path'       => '/feedback_statuses/{id}',
        'controller' => 'LegacyApiBundle:FeedbackStatuses:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_statuses_create',
    [
        'path'       => '/feedback_statuses',
        'controller' => 'LegacyApiBundle:FeedbackStatuses:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_feedback_statuses_save',
    [
        'path'       => '/feedback_statuses/{id}',
        'controller' => 'LegacyApiBundle:FeedbackStatuses:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_statuses_delete',
    [
        'path'       => '/feedback_statuses/{id}',
        'controller' => 'LegacyApiBundle:FeedbackStatuses:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Feedback Types
//#######################################################################################################################

$collection->create(
    'api_feedback_types',
    [
        'path'       => '/feedback_types',
        'controller' => 'LegacyApiBundle:FeedbackTypes:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_types_order',
    [
        'path'       => '/feedback_types/display_order',
        'controller' => 'LegacyApiBundle:FeedbackTypes:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_types_get',
    [
        'path'       => '/feedback_types/{id}',
        'controller' => 'LegacyApiBundle:FeedbackTypes:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_types_create',
    [
        'path'       => '/feedback_types',
        'controller' => 'LegacyApiBundle:FeedbackTypes:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_feedback_types_save',
    [
        'path'       => '/feedback_types/{id}',
        'controller' => 'LegacyApiBundle:FeedbackTypes:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_types_delete',
    [
        'path'       => '/feedback_types/{id}',
        'controller' => 'LegacyApiBundle:FeedbackTypes:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Feedback Categories
//#######################################################################################################################

$collection->create(
    'api_feedback_categories',
    [
        'path'       => '/feedback_categories',
        'controller' => 'LegacyApiBundle:FeedbackCategories:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_categories_order',
    [
        'path'       => '/feedback_categories/display_order',
        'controller' => 'LegacyApiBundle:FeedbackCategories:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_categories_get',
    [
        'path'       => '/feedback_categories/{id}',
        'controller' => 'LegacyApiBundle:FeedbackCategories:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_feedback_categories_create',
    [
        'path'       => '/feedback_categories',
        'controller' => 'LegacyApiBundle:FeedbackCategories:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_feedback_categories_save',
    [
        'path'       => '/feedback_categories/{id}',
        'controller' => 'LegacyApiBundle:FeedbackCategories:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_feedback_categories_delete',
    [
        'path'       => '/feedback_categories/{id}',
        'controller' => 'LegacyApiBundle:FeedbackCategories:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Twitter Setup
//#######################################################################################################################

$collection->create(
    'api_twitter_setup',
    [
        'path'       => '/twitter_setup',
        'controller' => 'LegacyApiBundle:TwitterSetup:twitterSetup',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_twitter_setup_save',
    [
        'path'       => '/twitter_setup',
        'controller' => 'LegacyApiBundle:TwitterSetup:save',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Twitter Accounts
//#######################################################################################################################

$collection->create(
    'api_twitter_accounts',
    [
        'path'       => '/twitter_accounts',
        'controller' => 'LegacyApiBundle:TwitterAccounts:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_twitter_accounts_get',
    [
        'path'       => '/twitter_accounts/{id}',
        'controller' => 'LegacyApiBundle:TwitterAccounts:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_twitter_accounts_create',
    [
        'path'       => '/twitter_accounts',
        'controller' => 'LegacyApiBundle:TwitterAccounts:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_twitter_accounts_save',
    [
        'path'       => '/twitter_accounts/{id}',
        'controller' => 'LegacyApiBundle:TwitterAccounts:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_twitter_accounts_delete',
    [
        'path'       => '/twitter_accounts/{id}',
        'controller' => 'LegacyApiBundle:TwitterAccounts:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Server Requirements
//#######################################################################################################################

$collection->create(
    'api_server_reqs',
    [
        'path'       => '/server_reqs',
        'controller' => 'LegacyApiBundle:Server:getServerReqs',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server PHP Info
//#######################################################################################################################

$collection->create(
    'api_server_php_info',
    [
        'path'       => '/server_php_info',
        'controller' => 'LegacyApiBundle:Server:getPhpInfo',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server Mysql Info
//#######################################################################################################################

$collection->create(
    'api_server_mysql_info',
    [
        'path'       => '/server_mysql_info',
        'controller' => 'LegacyApiBundle:Server:getMysqlInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_mysql_info_schemadiff',
    [
        'path'       => '/server_mysql_info/schema-diff',
        'controller' => 'LegacyApiBundle:Server:getMysqlSchemaDiff',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server Mysql Status
//#######################################################################################################################

$collection->create(
    'api_server_mysql_status',
    [
        'path'       => '/server_mysql_status',
        'controller' => 'LegacyApiBundle:Server:getMysqlStatus',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server Mysql Sort Order
//#######################################################################################################################

$collection->create(
    'api_server_mysql_sort_order',
    [
        'path'       => '/server_mysql_sort_order',
        'controller' => 'LegacyApiBundle:Server:getMysqlSortOrder',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_mysql_sort_order_save',
    [
        'path'       => '/server_mysql_sort_order',
        'controller' => 'LegacyApiBundle:Server:saveMysqlSortOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_server_mysql_sort_order_status',
    [
        'path'       => '/server_mysql_sort_order_status',
        'controller' => 'LegacyApiBundle:Server:getMysqlSortOrderStatus',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server
//#######################################################################################################################

$collection->create(
    'api_server_cron_status',
    [
        'path'       => '/server/cron-status',
        'controller' => 'LegacyApiBundle:Server:cronStatus',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_error_status',
    [
        'path'       => '/server/error-status',
        'controller' => 'LegacyApiBundle:Server:errorStatus',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_delete_error_log',
    [
        'path'       => '/server/error-log',
        'controller' => 'LegacyApiBundle:Server:deleteErrorLog',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_server_apc_status',
    [
        'path'       => '/server/apc-status',
        'controller' => 'LegacyApiBundle:Server:apcStatus',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_autoupdate_begin',
    [
        'path'       => '/server/updates/auto',
        'controller' => 'LegacyApiBundle:Server:beginAutomaticUpdate',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_server_autoupdate_abort',
    [
        'path'       => '/server/updates/auto',
        'controller' => 'LegacyApiBundle:Server:abortAutomaticUpdate',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_server_autoupdate_status',
    [
        'path'       => '/server/updates/auto',
        'controller' => 'LegacyApiBundle:Server:getAutomaticUpdateStatus',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server Error Logs
//#######################################################################################################################

$collection->create(
    'api_server_error_logs',
    [
        'path'       => '/server_error_logs',
        'controller' => 'LegacyApiBundle:Server:listErrorLogs',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_error_logs_get',
    [
        'path'       => '/server_error_logs/{id}',
        'controller' => 'LegacyApiBundle:Server:getErrorLogs',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_error_logs_delete',
    [
        'path'       => '/server_error_logs',
        'controller' => 'LegacyApiBundle:Server:removeErrorLogs',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Server Task Queue
//#######################################################################################################################

$collection->create(
    'api_server_task_queue',
    [
        'path'       => '/server_task_queue',
        'controller' => 'LegacyApiBundle:Server:getTaskQueue',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server Cron
//#######################################################################################################################

$collection->create(
    'api_server_cron',
    [
        'path'       => '/server_cron',
        'controller' => 'LegacyApiBundle:Server:listCron',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_cron_logs',
    [
        'path'       => '/server_cron/logs',
        'controller' => 'LegacyApiBundle:Server:logsCron',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_cron_logs_delete',
    [
        'path'       => '/server_cron/logs',
        'controller' => 'LegacyApiBundle:Server:removeCron',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Server File Uploads
//#######################################################################################################################

$collection->create(
    'api_server_file_uploads',
    [
        'path'       => '/server_file_uploads',
        'controller' => 'LegacyApiBundle:Server:getFileUploads',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_test_file_uploads',
    [
        'path'       => '/server_file_uploads',
        'controller' => 'LegacyApiBundle:Server:testFileUpload',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_server_switch_file_uploads_storage',
    [
        'path'       => '/server_file_uploads/switch',
        'controller' => 'LegacyApiBundle:Server:switchFileStorage',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_server_switch_file_uploads_storage_status',
    [
        'path'       => '/server_file_uploads/switch_status',
        'controller' => 'LegacyApiBundle:Server:switchFileStorageStatus',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server File Integrity
//#######################################################################################################################

$collection->create(
    'api_server_file_check',
    [
        'path'       => '/server_file_check',
        'controller' => 'LegacyApiBundle:Server:listFileCheck',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_file_check_get',
    [
        'path'       => '/server_file_check/{id}',
        'controller' => 'LegacyApiBundle:Server:getFileCheck',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Server Report File
//#######################################################################################################################

$collection->create(
    'api_server_report_file_get',
    [
        'path'       => '/server_report_file',
        'controller' => 'LegacyApiBundle:Server:getReportFile',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_server_report_file_check_save',
    [
        'path'       => '/server_report_file/file_check_results',
        'controller' => 'LegacyApiBundle:Server:saveFileCheckResults',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Chat Fields
//#######################################################################################################################

$collection->create(
    'api_chat_fields_get',
    [
        'path'         => '/chat_fields/{id}',
        'controller'   => 'LegacyApiBundle:ChatFields:getCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_chat_fields_create',
    [
        'path'       => '/chat_fields',
        'controller' => 'LegacyApiBundle:ChatFields:saveCustomField',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_chat_fields_delete',
    [
        'path'         => '/chat_fields/{id}',
        'controller'   => 'LegacyApiBundle:ChatFields:deleteCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_chat_fields_save',
    [
        'path'         => '/chat_fields/{id}',
        'controller'   => 'LegacyApiBundle:ChatFields:saveCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_chat_fields_save_batch',
    [
        'path'       => '/chat_fields/batch',
        'controller' => 'LegacyApiBundle:ChatFields:saveBatchCustomField',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_chat_fields',
    [
        'path'       => '/chat_fields',
        'controller' => 'LegacyApiBundle:ChatFields:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chat_fields_setenabled',
    [
        'path'       => '/chat_fields/set-enabled/{field_id}/{is_enabled}',
        'controller' => 'LegacyApiBundle:ChatFields:toggleField',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_chat_fields_update_order',
    [
        'path'       => '/chat_fields/display-order',
        'controller' => 'LegacyApiBundle:ChatFields:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Kb Fields
//#######################################################################################################################

$collection->create(
    'api_kb_fields_get',
    [
        'path'         => '/kb_fields/{id}',
        'controller'   => 'LegacyApiBundle:ArticleFields:getCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_kb_fields_create',
    [
        'path'       => '/kb_fields',
        'controller' => 'LegacyApiBundle:ArticleFields:saveCustomField',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_kb_fields_delete',
    [
        'path'         => '/kb_fields/{id}',
        'controller'   => 'LegacyApiBundle:ArticleFields:deleteCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_kb_fields_save',
    [
        'path'         => '/kb_fields/{id}',
        'controller'   => 'LegacyApiBundle:ArticleFields:saveCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_kb_fields_save_batch',
    [
        'path'       => '/kb_fields/batch',
        'controller' => 'LegacyApiBundle:ArticleFields:saveBatchCustomField',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_kb_fields',
    [
        'path'       => '/kb_fields',
        'controller' => 'LegacyApiBundle:ArticleFields:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_kb_fields_setenabled',
    [
        'path'       => '/kb_fields/set-enabled/{field_id}/{is_enabled}',
        'controller' => 'LegacyApiBundle:ArticleFields:toggleField',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_kb_fields_update_order',
    [
        'path'       => '/kb_fields/display-order',
        'controller' => 'LegacyApiBundle:ArticleFields:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Chat Departments
//#######################################################################################################################

$collection->create(
    'api_chat_deps',
    [
        'path'       => '/chat_deps',
        'controller' => 'LegacyApiBundle:ChatDeps:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chat_deps_create',
    [
        'path'       => '/chat_deps',
        'controller' => 'LegacyApiBundle:ChatDeps:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_chat_deps_order',
    [
        'path'       => '/chat_deps/display_order',
        'controller' => 'LegacyApiBundle:ChatDeps:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_chat_deps_get',
    [
        'path'       => '/chat_deps/{id}',
        'controller' => 'LegacyApiBundle:ChatDeps:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_chat_deps_save',
    [
        'path'       => '/chat_deps/{id}',
        'controller' => 'LegacyApiBundle:ChatDeps:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_chat_deps_remove',
    [
        'path'       => '/chat_deps/{id}',
        'controller' => 'LegacyApiBundle:ChatDeps:remove',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Api Keys
//#######################################################################################################################

$collection->create(
    'api_api_keys',
    [
        'path'       => '/api_keys',
        'controller' => 'LegacyApiBundle:ApiKeys:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_api_keys_create',
    [
        'path'       => '/api_keys',
        'controller' => 'LegacyApiBundle:ApiKeys:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_api_keys_get',
    [
        'path'       => '/api_keys/{id}',
        'controller' => 'LegacyApiBundle:ApiKeys:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_api_keys_save',
    [
        'path'       => '/api_keys/{id}',
        'controller' => 'LegacyApiBundle:ApiKeys:save',
        'methods'    => ['POST', 'PUT'],
    ]
);

$collection->create(
    'api_api_keys_delete',
    [
        'path'       => '/api_keys/{id}',
        'controller' => 'LegacyApiBundle:ApiKeys:remove',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_api_keys_logs',
    [
        'path'       => '/api_keys/{id}/logs',
        'controller' => 'LegacyApiBundle:ApiKeys:getLogs',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_api_keys_regenerate',
    [
        'path'       => '/api_keys/regenerate/{id}',
        'controller' => 'LegacyApiBundle:ApiKeys:regenerate',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_api_keys_replay_log_entry',
    [
        'path'       => '/api_keys/replay/{logEntryId}',
        'controller' => 'LegacyApiBundle:ApiKeys:replayLogEntry',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_api_keys_settings',
    [
        'path'       => '/api_keys_settings',
        'controller' => 'LegacyApiBundle:ApiKeys:getDefaultSettings',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Tasks
//#######################################################################################################################

$collection->create(
    'api_tasks_settings_get',
    [
        'path'       => '/tasks/settings',
        'controller' => 'LegacyApiBundle:Tasks:settings',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_tasks_settings_set',
    [
        'path'       => '/tasks/settings',
        'controller' => 'LegacyApiBundle:Tasks:updateSettings',
        'methods'    => ['PUT'],
    ]
);

//#######################################################################################################################
// Problems
//#######################################################################################################################

$collection->create('api_problems_settings_get', [
    'path'       => '/problems/settings',
    'controller' => 'LegacyApiBundle:Problems:settings',
    'methods'    => ['GET'],
]);

$collection->create('api_problems_settings_set', [
    'path'       => '/problems/settings',
    'controller' => 'LegacyApiBundle:Problems:updateSettings',
    'methods'    => ['PUT'],
]);

//#######################################################################################################################
// CRM User Fields
//#######################################################################################################################

$collection->create(
    'api_user_fields_get',
    [
        'path'         => '/user_fields/{id}',
        'controller'   => 'LegacyApiBundle:UserFields:getCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_user_fields_create',
    [
        'path'       => '/user_fields',
        'controller' => 'LegacyApiBundle:UserFields:saveCustomField',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_user_fields_save',
    [
        'path'         => '/user_fields/{id}',
        'controller'   => 'LegacyApiBundle:UserFields:saveCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_user_fields_delete',
    [
        'path'         => '/user_fields/{id}',
        'controller'   => 'LegacyApiBundle:UserFields:deleteCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_user_fields',
    [
        'path'       => '/user_fields',
        'controller' => 'LegacyApiBundle:UserFields:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_user_fields_setenabled',
    [
        'path'       => '/user_fields/set-enabled/{field_id}/{is_enabled}',
        'controller' => 'LegacyApiBundle:UserFields:toggleField',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_user_fields_update_order',
    [
        'path'       => '/user_fields/display-order',
        'controller' => 'LegacyApiBundle:UserFields:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// CRM New Custom Fields
//#######################################################################################################################

$collection->create(
    'api_custom_fields',
    [
        'path'       => '/custom_fields',
        'controller' => 'LegacyApiBundle:CustomFields:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_common_custom_fields_get',
    [
        'path'         => '/custom_fields/{objectType}/{objectId}',
        'controller'   => 'LegacyApiBundle:CustomFields:getCommonFields',
        'requirements' => [
            'objectType' => implode(
                '|',
                array_keys(\Application\LegacyApiBundle\Controller\CustomFieldsController::$allowed_common)
            ),
            'id' => '\\d+',
        ],
        'methods' => ['GET'],
    ]
);

$collection->create(
    'api_common_custom_fields_set',
    [
        'path'         => '/custom_fields/{objectType}/{objectId}',
        'controller'   => 'LegacyApiBundle:CustomFields:setCommonField',
        'requirements' => ['objectType' => '\\w+', 'objectId' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_custom_fields_children',
    [
        'path'         => '/custom_fields/{id}/children',
        'controller'   => 'LegacyApiBundle:CustomFields:children',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_custom_fields_children',
    [
        'path'         => '/custom_fields/{id}/children',
        'controller'   => 'LegacyApiBundle:CustomFields:addChild',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_custom_fields_get',
    [
        'path'         => '/custom_fields/{id}',
        'controller'   => 'LegacyApiBundle:CustomFields:get',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_custom_fields_save',
    [
        'path'         => '/custom_fields/{id}',
        'controller'   => 'LegacyApiBundle:CustomFields:save',
        'requirements' => ['id' => '\\d+'],
        'defaults'     => ['id' => 0],
        'methods'      => ['PUT', 'POST'],
    ]
);

$collection->create(
    'api_custom_fields_delete',
    [
        'path'         => '/custom_fields/{id}',
        'controller'   => 'LegacyApiBundle:CustomFields:delete',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_custom_fields_update_order',
    [
        'path'       => '/custom_fields/display-order',
        'controller' => 'LegacyApiBundle:CustomFields:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_custom_fields_delete_option',
    [
        'path'       => '/custom_fields/option',
        'controller' => 'LegacyApiBundle:CustomFields:deleteOption',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// CRM Organization Fields
//#######################################################################################################################

$collection->create(
    'api_org_fields_get',
    [
        'path'         => '/org_fields/{id}',
        'controller'   => 'LegacyApiBundle:OrgFields:getCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_org_fields_create',
    [
        'path'       => '/org_fields',
        'controller' => 'LegacyApiBundle:OrgFields:saveCustomField',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_org_fields_save',
    [
        'path'         => '/org_fields/{id}',
        'controller'   => 'LegacyApiBundle:OrgFields:saveCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_org_fields_delete',
    [
        'path'         => '/org_fields/{id}',
        'controller'   => 'LegacyApiBundle:OrgFields:deleteCustomField',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_org_fields',
    [
        'path'       => '/org_fields',
        'controller' => 'LegacyApiBundle:OrgFields:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_org_fields_setenabled',
    [
        'path'       => '/org_fields/set-enabled/{field_id}/{is_enabled}',
        'controller' => 'LegacyApiBundle:OrgFields:toggleField',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_org_fields_update_order',
    [
        'path'       => '/org_fields/display-order',
        'controller' => 'LegacyApiBundle:OrgFields:saveDisplayOrder',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// CRM Banning
//#######################################################################################################################

$collection->create(
    'api_banning',
    [
        'path'       => '/banning',
        'controller' => 'LegacyApiBundle:Banning:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_banning_email_export',
    [
        'path'       => '/banning/export_emails',
        'controller' => 'LegacyApiBundle:Banning:exportEmails',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_banning_email_import',
    [
        'path'       => '/banning/import_emails',
        'controller' => 'LegacyApiBundle:Banning:importEmails',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_banning_ip_create',
    [
        'path'       => '/banning_ip',
        'controller' => 'LegacyApiBundle:Banning:saveIp',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_banning_email_create',
    [
        'path'       => '/banning_email',
        'controller' => 'LegacyApiBundle:Banning:saveEmail',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_banning_ip_get',
    [
        'path'         => '/banning_ip/{id}',
        'controller'   => 'LegacyApiBundle:Banning:getIp',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_banning_email_get',
    [
        'path'       => '/banning_email/{id}',
        'controller' => 'LegacyApiBundle:Banning:getEmail',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_banning_ip_save',
    [
        'path'       => '/banning_ip/{id}',
        'controller' => 'LegacyApiBundle:Banning:saveIp',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_banning_email_save',
    [
        'path'       => '/banning_email/{id}',
        'controller' => 'LegacyApiBundle:Banning:saveEmail',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_banning_ip_remove_all',
    [
        'path'       => '/banning_ip',
        'controller' => 'LegacyApiBundle:Banning:removeIp',
        'methods'    => ['DELETE'],
        'defaults'   => ['id' => null],
    ]
);

$collection->create(
    'api_banning_email_remove_all',
    [
        'path'       => '/banning_email',
        'controller' => 'LegacyApiBundle:Banning:removeEmail',
        'methods'    => ['DELETE'],
        'defaults'   => ['id' => null],
    ]
);

$collection->create(
    'api_banning_ip_remove',
    [
        'path'         => '/banning_ip/{id}',
        'controller'   => 'LegacyApiBundle:Banning:removeIp',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_banning_email_remove',
    [
        'path'       => '/banning_email/{id}',
        'controller' => 'LegacyApiBundle:Banning:removeEmail',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// CRM User Groups
//#######################################################################################################################

$collection->create(
    'api_user_groups_list',
    [
        'path'       => '/user_groups',
        'controller' => 'LegacyApiBundle:Usergroups:list',
        'defaults'   => ['type' => 'user'],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_usergroups_non_sys_list',
    [
        'path'       => '/non_sys_usergroups',
        'controller' => 'LegacyApiBundle:Usergroups:list',
        'defaults'   => ['type' => 'non_sys_user'],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_user_groups_get',
    [
        'path'         => '/user_groups/{id}',
        'controller'   => 'LegacyApiBundle:Usergroups:get',
        'requirements' => ['id' => '(\\d+|[a-z0-9_\.\-]+)'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_user_groups_delete',
    [
        'path'         => '/user_groups/{id}',
        'controller'   => 'LegacyApiBundle:Usergroups:delete',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_user_groups_create',
    [
        'path'       => '/user_groups',
        'controller' => 'LegacyApiBundle:Usergroups:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_user_groups_save',
    [
        'path'         => '/user_groups/{id}',
        'controller'   => 'LegacyApiBundle:Usergroups:save',
        'requirements' => ['id' => '(\\d+|[a-z0-9_\.\-]+)'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_user_groups_save_permissions',
    [
        'path'         => '/user_groups/permissions/{type}',
        'controller'   => 'LegacyApiBundle:Usergroups:savePermissions',
        'requirements' => ['type' => '([-\._a-z0-9]+)'],
        'methods'      => ['PUT'],
    ]
);

//#######################################################################################################################
// CRM Import CSV
//#######################################################################################################################

$collection->create(
    'api_import_csv_upload',
    [
        'path'       => '/import_csv_upload',
        'controller' => 'LegacyApiBundle:CsvUpload:upload',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_import_csv_import',
    [
        'path'       => '/import_csv_import',
        'controller' => 'LegacyApiBundle:CsvUpload:import',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_import_csv_status',
    [
        'path'       => '/import_csv_status',
        'controller' => 'LegacyApiBundle:CsvUpload:status',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_import_csv_logs',
    [
        'path'       => '/import_csv_logs',
        'controller' => 'LegacyApiBundle:CsvUpload:logs',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_import_csv_clean',
    [
        'path'       => '/import_csv_clean',
        'controller' => 'LegacyApiBundle:CsvUpload:clean',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// CRM Export CSV
//#######################################################################################################################

$collection->create(
    'api_export_csv_start',
    [
        'path'       => '/export/start',
        'controller' => 'LegacyApiBundle:CsvExport:start',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_export_csv_stop',
    [
        'path'       => '/export/stop',
        'controller' => 'LegacyApiBundle:CsvExport:stop',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_export_csv_status',
    [
        'path'       => '/export/status',
        'controller' => 'LegacyApiBundle:CsvExport:status',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_export_list_files',
    [
        'path'       => '/export/list',
        'controller' => 'LegacyApiBundle:CsvExport:list',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// CRM User Rules
//#######################################################################################################################

$collection->create(
    'api_user_rules',
    [
        'path'       => '/user_rules',
        'controller' => 'LegacyApiBundle:UserRules:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_user_rules_create',
    [
        'path'       => '/user_rules',
        'controller' => 'LegacyApiBundle:UserRules:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_user_rules_get',
    [
        'path'       => '/user_rules/{id}',
        'controller' => 'LegacyApiBundle:UserRules:get',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_user_rules_save',
    [
        'path'       => '/user_rules/{id}',
        'controller' => 'LegacyApiBundle:UserRules:save',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_user_rules_delete',
    [
        'path'       => '/user_rules/{id}',
        'controller' => 'LegacyApiBundle:UserRules:remove',
        'methods'    => ['DELETE'],
    ]
);

$collection->create(
    'api_user_rules_apply',
    [
        'path'       => '/user_rules_apply/{id}/page_{page_id}',
        'controller' => 'LegacyApiBundle:UserRules:apply',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Login Logs
//#######################################################################################################################

$collection->create(
    'api_login_logs',
    [
        'path'         => '/login_logs/{agent_id}',
        'controller'   => 'LegacyApiBundle:LoginLogs:list',
        'requirements' => ['agent_id' => '\\d+'],
        'defaults'     => ['agent_id' => '0'],
        'methods'      => ['GET'],
    ]
);

//#######################################################################################################################
// Languages
//#######################################################################################################################

$collection->create(
    'api_langs',
    [
        'path'       => '/langs',
        'controller' => 'LegacyApiBundle:Languages:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_langs_masstickets',
    [
        'path'       => '/langs/tools/mass-update-tickets',
        'controller' => 'LegacyApiBundle:Languages:massUpdateTickets',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_langs_massusers',
    [
        'path'       => '/langs/tools/mass-update-users',
        'controller' => 'LegacyApiBundle:Languages:massUpdateUsers',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_langs_setdefault',
    [
        'path'       => '/langs/{id}/set-default',
        'controller' => 'LegacyApiBundle:Languages:setDefaultLang',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_langs_install',
    [
        'path'         => '/langs/{id}/install',
        'controller'   => 'LegacyApiBundle:Languages:installLang',
        'methods'      => ['POST'],
        'requirements' => ['id' => '[a-z_]+'],
    ]
);

$collection->create(
    'api_langs_delete',
    [
        'path'         => '/langs/{id}/uninstall',
        'controller'   => 'LegacyApiBundle:Languages:uninstallLang',
        'methods'      => ['POST'],
        'requirements' => ['id' => '\d+|[a-z_]+'],
    ]
);

$collection->create(
    'api_langs_getinfo',
    [
        'path'         => '/langs/{id}',
        'controller'   => 'LegacyApiBundle:Languages:getLang',
        'methods'      => ['GET'],
        'requirements' => ['id' => '\d+|[a-z_]+'],
    ]
);

$collection->create(
    'api_langs_saveinfo',
    [
        'path'         => '/langs/{id}',
        'controller'   => 'LegacyApiBundle:Languages:saveLang',
        'methods'      => ['POST'],
        'requirements' => ['id' => '\d+|[a-z_]+'],
    ]
);

$collection->create(
    'api_langs_savephrases',
    [
        'path'         => '/langs/{id}/phrases',
        'controller'   => 'LegacyApiBundle:Languages:savePhraseSet',
        'methods'      => ['POST'],
        'requirements' => ['id' => '\d+|[a-z_]+'],
    ]
);

$collection->create(
    'api_langs_syncphrases',
    [
        'path'         => '/langs/{id}/phrases/sync',
        'controller'   => 'LegacyApiBundle:Languages:syncPhraseSet',
        'methods'      => ['POST'],
        'requirements' => ['id' => '\d+|[a-z_]+'],
    ]
);

$collection->create(
    'api_langs_reset_managed',
    [
        'path'       => '/langs/phrases/reset-managed',
        'controller' => 'LegacyApiBundle:Languages:resetManagedPhrases',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_langs_getphrasegroups',
    [
        'path'       => '/langs/phrases-groups',
        'controller' => 'LegacyApiBundle:Languages:getPhraseGroups',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_langs_getphrase_all',
    [
        'path'         => '/langs/phrases/{phrase_id}',
        'controller'   => 'LegacyApiBundle:Languages:getPhrase',
        'requirements' => ['phrase_id' => '[a-zA-Z0-9\-_\.]+'],
        'defaults'     => ['for_lang' => '-1'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_langs_getphrase',
    [
        'path'         => '/langs/phrases/{phrase_id}/{for_lang}',
        'controller'   => 'LegacyApiBundle:Languages:getPhrase',
        'defaults'     => ['for_lang' => '-1'],
        'requirements' => ['phrase_id' => '[a-zA-Z0-9\-_\.]+', 'for_lang' => '\d+|[a-z]+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_langs_savephrase',
    [
        'path'       => '/langs/phrases/{phrase_id}',
        'controller' => 'LegacyApiBundle:Languages:savePhrase',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_langs_getphrases',
    [
        'path'         => '/langs/{id}/{group_id}',
        'controller'   => 'LegacyApiBundle:Languages:getPhrases',
        'methods'      => ['GET'],
        'requirements' => ['id' => '\d+|[a-z_]+', 'group_id' => '[a-zA-Z0-9\-_\.]+'],
    ]
);

//#######################################################################################################################
// Templates
//#######################################################################################################################

$collection->create(
    'api_templates_getinfo',
    [
        'path'       => '/templates-info',
        'controller' => 'LegacyApiBundle:Templates:getTemplateInfo',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_templates_get',
    [
        'path'       => '/templates/{name}',
        'controller' => 'LegacyApiBundle:Templates:getTemplate',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_templates_update',
    [
        'path'       => '/templates/{name}',
        'controller' => 'LegacyApiBundle:Templates:setTemplate',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_templates_delete',
    [
        'path'       => '/templates/{name}',
        'controller' => 'LegacyApiBundle:Templates:deleteTemplate',
        'methods'    => ['DELETE'],
    ]
);

//#######################################################################################################################
// Email Templates
//#######################################################################################################################

$collection->create(
    'api_templates_email_getinfo',
    [
        'path'       => '/email-templates-info',
        'controller' => 'LegacyApiBundle:Templates:getEmailTemplateInfo',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Save Log
//#######################################################################################################################

$collection->create(
    'api_savelog_logjserror',
    [
        'path'       => '/log-js-error',
        'controller' => 'LegacyApiBundle:SaveLog:logJsError',
        'methods'    => ['POST'],
    ]
);

//#######################################################################################################################
// Reports Overview
//#######################################################################################################################

$collection->create(
    'api_reports_overview_get_data',
    [
        'path'       => '/reports/overview/data/{type}',
        'controller' => 'LegacyApiBundle:ReportsOverview:getData',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reports_overview_update_stats',
    [
        'path'       => '/reports/overview/get-stats/{type}',
        'controller' => 'LegacyApiBundle:ReportsOverview:getStats',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Report Builder
//#######################################################################################################################

$collection->create(
    'api_reports_builder_list',
    [
        'path'       => '/reports/builder',
        'controller' => 'LegacyApiBundle:ReportsBuilder:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reports_builder_list_custom',
    [
        'path'       => '/reports/builder/custom',
        'controller' => 'LegacyApiBundle:ReportsBuilder:listCustom',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reports_builder_list_builtIn',
    [
        'path'       => '/reports/builder/builtIn',
        'controller' => 'LegacyApiBundle:ReportsBuilder:listBuiltIn',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reports_builder_get_group_params',
    [
        'path'       => '/reports/builder/group-params',
        'controller' => 'LegacyApiBundle:ReportsBuilder:getGroupParams',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reports_builder_get',
    [
        'path'         => '/reports/builder/{id}',
        'controller'   => 'LegacyApiBundle:ReportsBuilder:get',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['GET'],
    ]
);

$collection->create(
    'api_reports_builder_delete',
    [
        'path'         => '/reports/builder/{id}',
        'controller'   => 'LegacyApiBundle:ReportsBuilder:delete',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['DELETE'],
    ]
);

$collection->create(
    'api_reports_builder_create',
    [
        'path'       => '/reports/builder',
        'controller' => 'LegacyApiBundle:ReportsBuilder:save',
        'defaults'   => ['id' => '0'],
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_reports_builder_save',
    [
        'path'         => '/reports/builder/{id}',
        'controller'   => 'LegacyApiBundle:ReportsBuilder:save',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_reports_builder_clone',
    [
        'path'         => '/reports/builder/clone/{id}',
        'controller'   => 'LegacyApiBundle:ReportsBuilder:clone',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_reports_builder_test',
    [
        'path'         => '/reports/builder/test/{id}',
        'controller'   => 'LegacyApiBundle:ReportsBuilder:test',
        'requirements' => ['id' => '\\d+'],
        'methods'      => ['POST'],
    ]
);

$collection->create(
    'api_reports_builder_parse',
    [
        'path'       => '/reports/builder/parse',
        'controller' => 'LegacyApiBundle:ReportsBuilder:parse',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_reports_builder_download',
    [
        'path'       => '/reports/builder/download/{id}/{type}',
        'controller' => 'LegacyApiBundle:ReportsBuilder:download',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Report Agent Activity
//#######################################################################################################################

$collection->create(
    'api_reports_agent_activity_list',
    [
        'path'       => '/reports/agent-activity/{agent_or_team_id}/{date}',
        'controller' => 'LegacyApiBundle:ReportsAgentActivity:list',
        'defaults'   => ['agent_or_team_id' => 'all', 'date' => ''],
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Report Agent Hours
//#######################################################################################################################

$collection->create(
    'api_reports_agent_hours_list',
    [
        'path'       => '/reports/agent-hours/{date1}/{date2}',
        'controller' => 'LegacyApiBundle:ReportsAgentHours:list',
        'defaults'   => ['date1' => '', 'date2' => ''],
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Report Ticket Satisfaction
//#######################################################################################################################

$collection->create(
    'api_reports_ticket_satisfaction_list',
    [
        'path'       => '/reports/ticket-satisfaction/{page}',
        'controller' => 'LegacyApiBundle:ReportsTicketSatisfaction:list',
        'defaults'   => ['page' => '0'],
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_reports_ticket_satisfaction_summary',
    [
        'path'       => '/reports/ticket-satisfaction/summary/{date}',
        'controller' => 'LegacyApiBundle:ReportsTicketSatisfaction:summary',
        'defaults'   => ['date' => ''],
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Report Billing
//#######################################################################################################################

$collection->create(
    'api_reports_billing_get',
    [
        'path'       => '/reports/billing/{id}',
        'controller' => 'LegacyApiBundle:ReportsBilling:get',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Plugins
//#######################################################################################################################

$collection->create(
    'api_plugins_package_list',
    [
        'path'       => '/plugins/packages',
        'controller' => 'LegacyApiBundle:Plugins:listPackages',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_plugins_package_getinstaller',
    [
        'path'         => '/plugins/packages/{name}/installer',
        'controller'   => 'LegacyApiBundle:Plugins:getPackageInstaller',
        'requirements' => ['name' => '[a-z0-9\._]+'],
        'methods'      => ['GET'],
    ]
);

//#######################################################################################################################
// Blobs
//#######################################################################################################################

$collection->create(
    'api_blobs_upload',
    [
        'path'       => '/blobs',
        'controller' => 'LegacyApiBundle:Blobs:upload',
        'methods'    => ['PUT', 'POST'],
    ]
);

$collection->create(
    'api_blobs_get',
    [
        'path'       => '/blobs/{id}/{auth}',
        'controller' => 'LegacyApiBundle:Blobs:getInfo',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// My
//#######################################################################################################################

$collection->create(
    'api_my_session_renewtoken',
    [
        'path'       => '/my/session/renew-request-token',
        'controller' => 'LegacyApiBundle:MySession:renewRequestToken',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Apps
//#######################################################################################################################

$collection->create(
    'api_apps',
    [
        'path'       => '/apps',
        'controller' => 'LegacyApiBundle:Apps:list',
        'methods'    => ['GET'],
    ]
);

$collection->create(
    'api_apps_resync_packages',
    [
        'path'       => '/apps/resync-packages',
        'controller' => 'LegacyApiBundle:Apps:resyncPackages',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_apps_upload_package',
    [
        'path'       => '/apps/upload-package',
        'controller' => 'LegacyApiBundle:Apps:uploadPackage',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_apps_custom_new',
    [
        'path'       => '/apps/custom',
        'controller' => 'LegacyApiBundle:Apps:createCustomApp',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_apps_custom_getassets',
    [
        'path'         => '/apps/custom/{id}/assets',
        'controller'   => 'LegacyApiBundle:Apps:getCustomAssets',
        'methods'      => ['GET'],
        'requirements' => ['id' => '\d+'],
    ]
);

$collection->create(
    'api_apps_package',
    [
        'path'         => '/apps/packages/{name}',
        'controller'   => 'LegacyApiBundle:Apps:getPackage',
        'methods'      => ['GET'],
        'requirements' => ['name' => '[a-zA-Z0-9_\-\.]+'],
    ]
);

$collection->create(
    'api_apps_package_delete',
    [
        'path'         => '/apps/packages/{name}',
        'controller'   => 'LegacyApiBundle:Apps:deletePackage',
        'methods'      => ['DELETE'],
        'requirements' => ['name' => '[a-zA-Z0-9_\-\.]+'],
    ]
);

$collection->create(
    'api_apps_instance',
    [
        'path'         => '/apps/instances/{id}',
        'controller'   => 'LegacyApiBundle:Apps:getInstance',
        'methods'      => ['GET'],
        'requirements' => ['id' => '\d+'],
    ]
);

$collection->create(
    'api_apps_instance_update',
    [
        'path'         => '/apps/instances/{id}',
        'controller'   => 'LegacyApiBundle:Apps:updateInstance',
        'methods'      => ['POST'],
        'requirements' => ['id' => '\d+'],
    ]
);

$collection->create(
    'api_apps_instance_uninstall',
    [
        'path'         => '/apps/instances/{id}',
        'controller'   => 'LegacyApiBundle:Apps:uninstallInstance',
        'methods'      => ['DELETE'],
        'requirements' => ['id' => '\d+'],
    ]
);

$collection->create(
    'api_apps_install',
    [
        'path'       => '/apps/packages/{name}',
        'controller' => 'LegacyApiBundle:Apps:installPackage',
        'methods'    => ['PUT'],
    ]
);

$collection->create(
    'api_apps_package_exec',
    [
        'path'         => '/apps/packages/{name}/{action}',
        'controller'   => 'LegacyApiBundle:Apps:execPackage',
        'defaults'     => ['action' => 'default'],
        'methods'      => ['GET', 'POST', 'PUT', 'DELETE'],
        'requirements' => ['name' => '[a-zA-Z0-9_\-\.]+'],
    ]
);

$collection->create(
    'api_apps_instance_exec',
    [
        'path'         => '/apps/instances/{id}/{action}',
        'controller'   => 'LegacyApiBundle:Apps:execInstance',
        'defaults'     => ['action' => 'default'],
        'methods'      => ['GET', 'POST', 'PUT', 'DELETE'],
        'requirements' => ['id' => '\d+'],
    ]
);

$collection->create(
    'api_apps_jira',
    [
        'path'       => '/apps/jira',
        'controller' => 'LegacyApiBundle:Apps:jiraSettings',
        'methods'    => ['GET'],
    ]
);

//#######################################################################################################################
// Reset Demo
//#######################################################################################################################

$collection->create(
    'api_reset_demo_run',
    [
        'path'       => '/reset-helpdesk',
        'controller' => 'LegacyApiBundle:ResetHelpdesk:run',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_reset_demo_status',
    [
        'path'       => '/reset-helpdesk/status',
        'controller' => 'LegacyApiBundle:ResetHelpdesk:status',
        'methods'    => ['GET'],
    ]
);

//#############################################################################################
// Billing Fields
//#############################################################################################

$collection->create('api_billing_fields_get', [
    'path'         => '/billing_fields/{id}',
    'controller'   => 'LegacyApiBundle:BillingFields:getCustomField',
    'requirements' => ['id' => '\\d+'],
    'methods'      => ['GET'],
]);

$collection->create('api_billing_fields_create', [
    'path'       => '/billing_fields',
    'controller' => 'LegacyApiBundle:BillingFields:saveCustomField',
    'defaults'   => ['id' => '0'],
    'methods'    => ['PUT'],
]);

$collection->create('api_billing_fields_save', [
    'path'         => '/billing_fields/{id}',
    'controller'   => 'LegacyApiBundle:BillingFields:saveCustomField',
    'requirements' => ['id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('api_billing_fields_delete', [
    'path'         => '/billing_fields/{id}',
    'controller'   => 'LegacyApiBundle:BillingFields:deleteCustomField',
    'requirements' => ['id' => '\\d+'],
    'methods'      => ['DELETE'],
]);

$collection->create('api_billing_fields', [
    'path'       => '/billing_fields',
    'controller' => 'LegacyApiBundle:BillingFields:list',
    'methods'    => ['GET'],
]);

$collection->create('api_billing_fields_setenabled', [
    'path'       => '/billing_fields/set-enabled/{field_id}/{is_enabled}',
    'controller' => 'LegacyApiBundle:BillingFields:toggleField',
    'methods'    => ['POST'],
]);

$collection->create('api_billing_fields_update_order', [
    'path'       => '/billing_fields/display-order',
    'controller' => 'LegacyApiBundle:BillingFields:saveDisplayOrder',
    'methods'    => ['POST'],
]);

//#######################################################################################################################
// Brands
//#######################################################################################################################

$collection->create(
    'api_ticket_brands',
    [
        'path'       => '/ticket_brands',
        'controller' => 'LegacyApiBundle:TicketBrands:list',
        'methods'    => ['GET'],
    ]
);

return $collection;
