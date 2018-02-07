<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

$collection->create('agent', [
    'path'       => '/',
    'controller' => 'AgentBundle:Main:index',
]);

$collection->create('geoip', [
    'path'       => '/geoip',
    'controller' => 'AgentBundle:Misc:getGeoIp',
]);

$collection->create('agent_savedom', [
    'path'       => '/save-dom.json',
    'controller' => 'AgentBundle:Misc:saveDom',
]);

$collection->create('agent_requirejs_loader', [
    'path'       => '/requirejs-loader.js',
    'controller' => 'AgentBundle:Misc:getRequirejsLoader',
]);

$collection->create('agent_apps_config_js', [
    'path'       => '/apps-config.js',
    'controller' => 'AgentBundle:Misc:getAppsConfig',
]);

$collection->create('agent_my_info', [
    'path'       => '/me/info.js',
    'controller' => 'AgentBundle:Misc:getMyInfo',
]);

$collection->create('agent_combined_sectiondata', [
    'path'       => '/get-combined-section-data.json',
    'controller' => 'AgentBundle:Main:getCombinedSectionData',
]);

$collection->create('agent_load_recent_tabs', [
    'path'       => '/ui/load-recent-tabs.json',
    'controller' => 'AgentBundle:Main:loadRecentTabs',
]);

$collection->create('agent_content_preview_article', [
    'path'       => '/content-preview/article/{id}',
    'controller' => 'AgentBundle:ContentPreview:articlePreview',
]);

$collection->create('agent_accept_upload', [
    'path'       => '/misc/accept-upload',
    'controller' => 'AgentBundle:Misc:acceptTempUpload',
]);

$collection->create('agent_accept_redactor_image_upload', [
    'path'       => '/misc/accept-redactor-image-upload',
    'controller' => 'AgentBundle:Misc:acceptRedactorImageUpload',
]);

$collection->create('agent_accept_redactor_file_upload', [
    'path'       => '/misc/accept-redactor-file-upload',
    'controller' => 'AgentBundle:Misc:acceptRedactorFileUpload',
]);

$collection->create('agent_redactor_autosave', [
    'path'         => '/misc/redactor-autosave/{content_type}/{content_id}',
    'controller'   => 'AgentBundle:Misc:redactorAutosave',
    'requirements' => ['content_id' => '\\d+'],
]);

$collection->create('agent_submit_deskpro_feedback', [
    'path'       => '/misc/submit-deskpro-feedback.json',
    'controller' => 'AgentBundle:Misc:submitDeskproFeedback',
]);

$collection->create('agent_parse_vcard', [
    'path'       => '/misc/parse-vcard',
    'controller' => 'AgentBundle:Misc:parseVCard',
]);

$collection->create('agent_get_server_time', [
    'path'       => '/misc/get-server-time',
    'controller' => 'AgentBundle:Misc:getServerTime',
]);

$collection->create('agent_parse_vcard', [
    'path'         => '/misc/parse-vcard/{blob_id}',
    'controller'   => 'AgentBundle:Misc:parseVCard',
    'requirements' => ['blob_id' => '\\d+'],
]);

$collection->create('agent_ajax_save_prefs', [
    'path'       => '/misc/ajax-save-prefs',
    'controller' => 'AgentBundle:Misc:ajaxSavePrefs',
]);

$collection->create('agent_ajax_labels_autocomplete', [
    'path'         => '/misc/ajax-labels/{label_type}',
    'controller'   => 'AgentBundle:Misc:ajaxLabelsAutocomplete',
    'requirements' => ['label_type' => '[a-z]+'],
]);

$collection->create('agent_interface_data_js', [
    'path'       => '/misc/interface-data.js',
    'controller' => 'AgentBundle:Misc:getInterfaceData',
]);

$collection->create('agent_dismiss_help_message', [
    'path'       => '/misc/dismiss-help-message/{id}',
    'controller' => 'AgentBundle:Misc:dismissHelpMessage',
]);

$collection->create('agent_set_agent_status', [
    'path'       => '/misc/set-agent-status/{status}',
    'controller' => 'AgentBundle:Misc:setAgentStatus',
]);

$collection->create('agent_proxy', [
    'path'       => '/misc/proxy',
    'controller' => 'AgentBundle:Misc:proxy',
]);

$collection->create('agent_load_version_notice', [
    'path'       => '/misc/version-notices/{id}/log.html',
    'controller' => 'AgentBundle:Main:loadVersionNotice',
]);

$collection->create('agent_dismiss_version_notice', [
    'path'       => '/misc/version-notices/{id}/dismiss.json',
    'controller' => 'AgentBundle:Main:dismissVersionNotice',
]);

$collection->create('agent_dpnews_view', [
    'path'         => '/misc/view-dp-news/{id}',
    'controller'   => 'AgentBundle:Misc:viewDpNews',
    'requirements' => ['id' => '\d+'],
    'methods'      => ['GET'],
]);

$collection->create('agent_dpnews_dismiss', [
    'path'         => '/misc/view-dp-news/{id}/dismiss',
    'controller'   => 'AgentBundle:Misc:dismissDpNews',
    'requirements' => ['id' => '\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_redirect_out', [
    'path'         => '/redirect-out/{url}',
    'controller'   => 'AgentBundle:Misc:redirectExternal',
    'requirements' => ['url' => '.+'],
]);

$collection->create('agent_redirect_out_info', [
    'path'         => '/redirect-out-info/{url}',
    'controller'   => 'AgentBundle:Misc:redirectExternalInfo',
    'requirements' => ['url' => '.+'],
]);

$collection->create('agent_user_interface_frame', [
    'path'       => '/user-interface-frame',
    'controller' => 'AgentBundle:Misc:userInterfaceFrame',
]);

$collection->create('agent_password_confirm_code', [
    'path'       => '/password-confirm-code.json',
    'controller' => 'AgentBundle:Misc:getPasswordConfirmCode',
]);

$collection->create('agent_quicksearch', [
    'path'       => '/quick-search.json',
    'controller' => 'AgentBundle:Main:quickSearch',
]);

$collection->create('agent_quicksearch_getpersontickets', [
    'path'       => '/quick-search/get-person-tickets.json',
    'controller' => 'AgentBundle:Main:getPersonTickets',
]);

$collection->create('agent_quicksearch_getorgmembers', [
    'path'       => '/quick-search/get-org-members.json',
    'controller' => 'AgentBundle:Main:getOrgMembers',
]);

$collection->create('agent_recyclebin', [
    'path'       => '/recycle-bin',
    'controller' => 'AgentBundle:RecycleBin:list',
    'options'    => ['fragment_name' => 'recycle-bin', 'fragment_type' => 'list'],
]);

$collection->create('agent_recyclebin_more', [
    'path'       => '/recycle-bin/{type}/{page}',
    'controller' => 'AgentBundle:RecycleBin:listMore',
]);

$collection->create('agent_login_preload_sources', [
    'path'       => '/login/preload-sources',
    'controller' => 'AgentBundle:Login:preloadSources',
]);

$collection->create('agent_browser_requirements', [
    'path'       => '/browser-requirements',
    'controller' => 'AgentBundle:Login:browserRequirements',
]);

$collection->create('agent_min_ie_version', [
    'path'       => '/min-ie-version',
    'controller' => 'AgentBundle:Login:minIEVersion',
]);

$collection->create('agent_login', [
    'path'       => '/login',
    'controller' => 'AgentBundle:Login:index',
]);

$collection->create('agent_login_view_reset_password_form', [
    'path'       => '/login/reset-password',
    'controller' => 'AgentBundle:Login:viewResetPasswordForm',
]);

$collection->create('agent_login_authenticate_local', [
    'path'       => '/login/authenticate-password',
    'controller' => 'AgentBundle:Login:authenticateLocal',
    'defaults'   => ['usersource_id' => 0],
]);

$collection->create('agent_login_authenticate', [
    'path'         => '/login/authenticate/{usersource_id}',
    'controller'   => 'AgentBundle:Login:authenticate',
    'defaults'     => ['usersource_id' => 0],
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create('agent_login_callback', [
    'path'         => '/login/authenticate-callback/{usersource_id}',
    'controller'   => 'AgentBundle:Login:authenticateCallback',
    'requirements' => ['usersource_id' => '\\d+'],
]);

$collection->create(
    'agent_login_usersource_sso', [
        'path'         => '/login/usersource-sso/{usersource_id}',
        'controller'   => 'UserBundle:Login:usersourceSso',
        'requirements' => ['usersource_id' => '\\d+'],
    ]
);

$collection->create('agent_login_adminlogin', [
    'path'       => '/login/admin-login/{code}',
    'controller' => 'AgentBundle:Login:authAdminLogin',
]);

$collection->create('agent_send_lost', [
    'path'       => '/login/send-lost.json',
    'controller' => 'AgentBundle:Login:sendResetPassword',
    'defaults'   => ['_format' => 'json'],
]);

$collection->create('agent_whitelist_ip', [
    'path'       => '/whitelist-ip/{code}',
    'controller' => 'AgentBundle:Login:whitelistIp',
]);

$collection->create('agent_settings', [
    'path'       => '/settings',
    'controller' => 'AgentBundle:Settings:profile',
]);

$collection->create('agent_settings_profile_save', [
    'path'       => '/settings/profile/save.json',
    'controller' => 'AgentBundle:Settings:profileSave',
]);

$collection->create('agent_settings_profile_savewelcome', [
    'path'       => '/settings/profile/save-welcome.json',
    'controller' => 'AgentBundle:Settings:profileSaveWelcome',
]);

$collection->create('agent_settings_signature', [
    'path'       => '/settings/signature',
    'controller' => 'AgentBundle:Settings:signature',
]);

$collection->create('agent_settings_signature_save', [
    'path'       => '/settings/signature/save.json',
    'controller' => 'AgentBundle:Settings:signatureSave',
]);

$collection->create('agent_settings_profile_updatetimezone', [
    'path'       => '/settings/profile/update-timezone.json',
    'controller' => 'AgentBundle:Settings:updateTimezone',
]);

$collection->create('agent_settings_ticketnotif', [
    'path'       => '/settings/ticket-notifications',
    'controller' => 'AgentBundle:Settings:ticketNotifications',
]);

$collection->create('agent_settings_ticketnotif_save', [
    'path'       => '/settings/ticket-notifications/save.json',
    'controller' => 'AgentBundle:Settings:ticketNotificationsSave',
]);

$collection->create('agent_settings_othernotif', [
    'path'       => '/settings/other-notifications',
    'controller' => 'AgentBundle:Settings:otherNotifications',
]);

$collection->create('agent_settings_othernotif_save', [
    'path'       => '/settings/other-notifications/save.json',
    'controller' => 'AgentBundle:Settings:otherNotificationsSave',
]);

$collection->create('agent_settings_ticketmacros', [
    'path'       => '/settings/ticket-macros',
    'controller' => 'AgentBundle:Settings:ticketMacros',
]);

$collection->create('agent_settings_ticketmacros_edit', [
    'path'         => '/settings/ticket-macros/{macro_id}/edit',
    'controller'   => 'AgentBundle:Settings:ticketMacroEdit',
    'requirements' => ['macro_id' => '\\d+'],
]);

$collection->create('agent_settings_ticketmacros_edit_save', [
    'path'         => '/settings/ticket-macros/{macro_id}/save',
    'controller'   => 'AgentBundle:Settings:ticketMacroEditSave',
    'requirements' => ['macro_id' => '\\d+'],
]);

$collection->create('agent_settings_ticketmacros_new', [
    'path'       => '/settings/ticket-macros/new',
    'controller' => 'AgentBundle:Settings:ticketMacroEdit',
    'defaults'   => ['macro_id' => 0],
]);

$collection->create('agent_settings_ticketmacros_del', [
    'path'         => '/settings/ticket-macros/{macro_id}/delete',
    'controller'   => 'AgentBundle:Settings:ticketMacroDelete',
    'requirements' => ['macro_id' => '\\d+'],
]);

$collection->create('agent_settings_ticketfilters', [
    'path'       => '/settings/ticket-filters',
    'controller' => 'AgentBundle:Settings:ticketFilters',
]);

$collection->create('agent_settings_ticketfilters_edit', [
    'path'         => '/settings/ticket-filters/{filter_id}/edit',
    'controller'   => 'AgentBundle:Settings:ticketFilterEdit',
    'requirements' => ['filter_id' => '\\d+'],
]);

$collection->create('agent_settings_ticketfilters_edit_save', [
    'path'         => '/settings/ticket-filters/{filter_id}/edit/save',
    'controller'   => 'AgentBundle:Settings:ticketFilterEditSave',
    'requirements' => ['filter_id' => '\\d+'],
]);

$collection->create('agent_settings_ticketfilters_del', [
    'path'         => '/settings/ticket-filters/{filter_id}/delete',
    'controller'   => 'AgentBundle:Settings:ticketFilterDelete',
    'requirements' => ['filter_id' => '\\d+'],
]);

$collection->create('agent_settings_ticketfilters_new', [
    'path'       => '/settings/ticket-filters/new-filter',
    'controller' => 'AgentBundle:Settings:ticketFilterEdit',
    'defaults'   => ['filter_id' => 0],
]);

$collection->create('agent_settings_ticketslas', [
    'path'       => '/settings/ticket-slas',
    'controller' => 'AgentBundle:Settings:ticketSlas',
]);

$collection->create('agent_people_view', [
    'path'         => '/people/{person_id}',
    'controller'   => 'AgentBundle:Person:view',
    'requirements' => ['person_id' => '\\d+'],
    'options'      => ['fragment_name' => 'p'],
]);

$collection->create('agent_people_view_basicjson', [
    'path'         => '/people/{person_id}/basic.json',
    'controller'   => 'AgentBundle:Person:getBasicInfo',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_people_viewsession', [
    'path'         => '/people/session/{session_id}',
    'controller'   => 'AgentBundle:Person:viewSession',
    'requirements' => ['session_id' => '\\d+'],
]);

$collection->create('agent_people_new', [
    'path'       => '/people/new',
    'controller' => 'AgentBundle:Person:newPerson',
]);

$collection->create('agent_people_new_save', [
    'path'       => '/people/new/save',
    'controller' => 'AgentBundle:Person:newPersonSave',
]);

$collection->create('agent_people_ajaxsave', [
    'path'         => '/people/{person_id}/ajax-save',
    'controller'   => 'AgentBundle:Person:ajaxSave',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_people_savecontactdata', [
    'path'       => '/people/{person_id}/save-contact-data.json',
    'controller' => 'AgentBundle:Person:saveContactData',
]);

$collection->create('agent_people_unban_email', [
    'path'       => '/people/{person_id}/unban-email/{email_id}.json',
    'controller' => 'AgentBundle:Person:unbanEmail',
]);

$collection->create('agent_people_merge_overlay', [
    'path'         => '/people/{person_id}/merge-overlay/{other_person_id}',
    'controller'   => 'AgentBundle:Person:mergeOverlay',
    'requirements' => ['person_id' => '\\d+', 'other_person_id' => '\\d+'],
]);

$collection->create('agent_people_merge', [
    'path'         => '/people/{person_id}/merge/{other_person_id}',
    'controller'   => 'AgentBundle:Person:merge',
    'requirements' => ['person_id' => '\\d+', 'other_person_id' => '\\d+'],
]);

$collection->create('agent_people_delete', [
    'path'       => '/people/{person_id}/delete/{security_token}',
    'controller' => 'AgentBundle:Person:deletePerson',
]);

$collection->create('agent_people_login_as', [
    'path'       => '/people/{person_id}/login-as',
    'controller' => 'AgentBundle:Person:loginAs',
]);

$collection->create('agent_people_changepicoverlay', [
    'path'         => '/people/{person_id}/change-picture-overlay',
    'controller'   => 'AgentBundle:Person:changePictureOverlay',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_people_ajaxsave_note', [
    'path'         => '/people/{person_id}/ajax-save-note',
    'controller'   => 'AgentBundle:Person:ajaxSaveNote',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_people_notes_delete', [
    'path'         => '/people/notes/{note_id}',
    'controller'   => 'AgentBundle:Person:deleteNote',
    'requirements' => ['note_id' => '\\d+'],
    'methods'      => ['DELETE'],
]);

$collection->create('agent_people_ajaxsave_file', [
    'path'         => '/people/{person_id}/ajax-save-file',
    'controller'   => 'AgentBundle:Person:ajaxSaveFile',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_people_ajaxsave_organization', [
    'path'         => '/people/{person_id}/ajax-save-organization',
    'controller'   => 'AgentBundle:Person:ajaxSaveOrganization',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_person_list', [
    'path'       => '/person',
    'controller' => 'AgentBundle:Person:list',
    'condition'  => 'request.headers.get("X-Requested-With") == "XMLHttpRequest"',
]);

$collection->create('agent_team_list', [
    'path'       => '/agent_team',
    'controller' => 'AgentBundle:Person:listTeams',
    'condition'  => 'request.headers.get("X-Requested-With") == "XMLHttpRequest"',
]);

$collection->create('agent_person_get_tickets', [
    'path'         => '/person/{person_id}/tickets',
    'controller'   => 'AgentBundle:Person:getPersonTickets',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_person_get_chats', [
    'path'         => '/person/{person_id}/chats',
    'controller'   => 'AgentBundle:Person:getPersonChats',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_person_ajax_labels_save', [
    'path'         => '/person/{person_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Person:ajaxSaveLabels',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_person_ajaxsavecustomfields', [
    'path'         => '/person/{person_id}/ajax-save-custom-fields',
    'controller'   => 'AgentBundle:Person:ajaxSaveCustomFields',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_peoplesearch_usergroup', [
    'path'       => '/people-search/usergroup/{id}',
    'controller' => 'AgentBundle:PeopleSearch:showUsergroup',
    'options'    => ['fragment_name' => 'usergroup', 'fragment_type' => 'list'],
]);

$collection->create('agent_peoplesearch_organization', [
    'path'       => '/people-search/organization/{id}',
    'controller' => 'AgentBundle:PeopleSearch:showOrganizationMembers',
    'options'    => [
        'fragment_name' => 'organization-members',
        'fragment_type' => 'list',
    ],
]);

$collection->create('agent_peoplesearch_customfilter', [
    'path'       => '/people-search/search/{letter}',
    'controller' => 'AgentBundle:PeopleSearch:search',
    'defaults'   => ['letter' => '*'],
    'options'    => ['fragment_name' => 'people', 'fragment_type' => 'list'],
]);

$collection->create('agent_peoplesearch_getpage', [
    'path'       => '/people-search/get-page',
    'controller' => 'AgentBundle:PeopleSearch:getPeoplePage',
]);

$collection->create('agent_peoplesearch_performquick', [
    'path'       => '/people-search/search-quick',
    'controller' => 'AgentBundle:PeopleSearch:performQuickSearch',
]);

$collection->create('agent_peoplesearch_quickfind', [
    'path'       => '/people-search/quick-find',
    'controller' => 'AgentBundle:PeopleSearch:quickFind',
]);

$collection->create('agent_peoplesearch_quickfind_search', [
    'path'       => '/people-search/quick-find-search.json',
    'controller' => 'AgentBundle:PeopleSearch:quickFindSearch',
]);

$collection->create('agent_peoplesearch_getsectiondata', [
    'path'       => '/people/get-section-data.json',
    'controller' => 'AgentBundle:PeopleSearch:getSectionData',
]);

$collection->create('agent_peoplesearch_getsectiondata_reloadcounts', [
    'path'       => '/people/get-section-data/reload-counts.json',
    'controller' => 'AgentBundle:PeopleSearch:reloadCounts',
]);

$collection->create('agent_peoplesearch_reload_label_sectiondata', [
    'path'       => '/people/get-section-data/labels.json',
    'controller' => 'AgentBundle:PeopleSearch:reloadLabelData',
]);

$collection->create('agent_org_view', [
    'path'         => '/organizations/{organization_id}',
    'controller'   => 'AgentBundle:Organization:view',
    'requirements' => ['organization_id' => '\\d+'],
    'options'      => ['fragment_name' => 'o'],
]);

$collection->create('agent_org_child_add', [
    'path'         => '/organizations/{id}/child/add',
    'controller'   => 'AgentBundle:Organization:addChild',
    'requirements' => ['organization' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_org_child_remove', [
    'path'         => '/organizations/{id}/child/remove',
    'controller'   => 'AgentBundle:Organization:removeChild',
    'requirements' => ['organization' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_org_child_search', [
    'path'         => '/organizations/{id}/child/search',
    'controller'   => 'AgentBundle:OrganizationSearch:searchChild',
    'requirements' => ['organization' => '\\d+'],
    'methods'      => ['GET'],
]);

$collection->create('agent_org_new', [
    'path'       => '/organizations/new',
    'controller' => 'AgentBundle:Organization:newOrganization',
]);

$collection->create('agent_org_new_save', [
    'path'       => '/organizations/new/save',
    'controller' => 'AgentBundle:Organization:newOrganizationSave',
]);

$collection->create('agent_org_ajaxsave', [
    'path'         => '/organizations/{organization_id}/ajax-save',
    'controller'   => 'AgentBundle:Organization:ajaxSave',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_domain_assign', [
    'path'         => '/organizations/{organization_id}/assign-domain',
    'controller'   => 'AgentBundle:Organization:assignDomain',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_domain_unassign', [
    'path'         => '/organizations/{organization_id}/unassign-domain',
    'controller'   => 'AgentBundle:Organization:unassignDomain',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_domain_moveusers', [
    'path'         => '/organizations/{organization_id}/domain/move-users',
    'controller'   => 'AgentBundle:Organization:moveDomainUsers',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_domain_moveusers_exist', [
    'path'         => '/organizations/{organization_id}/domain/reassign-users',
    'controller'   => 'AgentBundle:Organization:moveTakenDomainUsers',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_save_member_pos', [
    'path'         => '/organizations/{organization_id}/save-member-pos/{person_id}',
    'controller'   => 'AgentBundle:Organization:savePosition',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_save_member_manager', [
    'path'         => '/organizations/{organization_id}/save-member-manager/{person_id}',
    'controller'   => 'AgentBundle:Organization:saveManager',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_savecontactdata', [
    'path'         => '/organizations/{organization_id}/save-contact-data.json',
    'controller'   => 'AgentBundle:Organization:saveContactData',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('agent_org_delete', [
    'path'       => '/organizations/{organization_id}/delete/{security_token}',
    'controller' => 'AgentBundle:Organization:deleteOrganization',
]);

$collection->create('agent_org_ajaxsave_note', [
    'path'         => '/organizations/{organization_id}/ajax-save-note',
    'controller'   => 'AgentBundle:Organization:ajaxSaveNote',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_notes_delete', [
    'path'         => '/organizations/notes/{note_id}',
    'controller'   => 'AgentBundle:Organization:deleteNote',
    'requirements' => ['note_id' => '\\d+'],
    'methods'      => ['DELETE'],
]);

$collection->create('agent_org_ajaxsave_file', [
    'path'         => '/organizations/{organization_id}/ajax-save-file',
    'controller'   => 'AgentBundle:Organization:ajaxSaveFile',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_ajax_labels_save', [
    'path'         => '/organizations/{organization_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Organization:ajaxSaveLabels',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_ajaxsavecustomfields', [
    'path'         => '/organizations/{organization_id}/ajax-save-custom-fields',
    'controller'   => 'AgentBundle:Organization:ajaxSaveCustomFields',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_org_changepicoverlay', [
    'path'         => '/organizations/{organization_id}/change-picture-overlay',
    'controller'   => 'AgentBundle:Organization:changePictureOverlay',
    'requirements' => ['organization_id' => '\\d+'],
]);

$collection->create('agent_orgsearch_getpage', [
    'path'       => '/organization-search/get-page',
    'controller' => 'AgentBundle:OrganizationSearch:getOrgPage',
]);

$collection->create('agent_orgsearch_customfilter', [
    'path'       => '/organization-search/search',
    'controller' => 'AgentBundle:OrganizationSearch:search',
    'options'    => ['fragment_name' => 'orgs', 'fragment_type' => 'list'],
]);

$collection->create('agent_orgsearch_quicknamesearch', [
    'path'       => '/organization-search/quick-name-search.json',
    'controller' => 'AgentBundle:OrganizationSearch:performQuickNameSearch',
]);

$collection->create('agent_orgsearch_namelookup', [
    'path'       => '/organization-search/name-lookup.json',
    'controller' => 'AgentBundle:OrganizationSearch:checkName',
]);

$collection->create('agent_ticketsearch_getsectiondata', [
    'path'       => '/ticket-search/get-section-data.json',
    'controller' => 'AgentBundle:TicketSearch:getSectionData',
]);

$collection->create('agent_ticketsearch_getsection_reloadarchive', [
    'path'       => '/ticket-search/get-section-data/reload-archive-section',
    'controller' => 'AgentBundle:TicketSearch:reloadArchiveSection',
]);

$collection->create('agent_ticketsearch_refreshsectiondata', [
    'path'       => '/ticket-search/refresh-section-data/{section}.json',
    'controller' => 'AgentBundle:TicketSearch:refreshSectionData',
]);

$collection->create('agent_ticketsearch_getlabelssection', [
    'path'       => '/ticket-search/get-section/labels',
    'controller' => 'AgentBundle:TicketSearch:getLabelsSection',
]);

$collection->create('agent_ticketsearch_getfiltercounts', [
    'path'       => '/ticket-search/get-filter-counts.json',
    'controller' => 'AgentBundle:TicketSearch:getFilterCounts',
]);

$collection->create('agent_ticketsearch_getslacounts', [
    'path'       => '/ticket-search/get-sla-counts.json',
    'controller' => 'AgentBundle:TicketSearch:getSlaCounts',
]);

$collection->create('agent_ticketsearch_grouptickets', [
    'path'       => '/ticket-search/group-tickets.json',
    'controller' => 'AgentBundle:TicketSearch:groupTickets',
]);

$collection->create('agent_ticketsearch_getpage', [
    'path'       => '/ticket-search/get-page',
    'controller' => 'AgentBundle:TicketSearch:getTicketPage',
]);

$collection->create('agent_ticketsearch_getflaggedsectiondata', [
    'path'       => '/tickets/get-flagged-section-data.json',
    'controller' => 'AgentBundle:TicketSearch:getFlaggedSectionData',
]);

$collection->create('agent_ticketsearch_runcustomfilter', [
    'path'       => '/ticket-search/custom-filter/run',
    'controller' => 'AgentBundle:TicketSearch:runCustomFilter',
]);

$collection->create('agent_ticketsearch_quicksearch', [
    'path'       => '/ticket-search/quick-search',
    'controller' => 'AgentBundle:TicketSearch:quickSearch',
]);

$collection->create('agent_ticketsearch_singleticketrow', [
    'path'         => '/ticket-search/single-ticket-row/{content_type}/{content_id}',
    'controller'   => 'AgentBundle:TicketSearch:getSingleTicketRow',
    'requirements' => ['content_id' => '\\d+'],
]);

$collection->create('agent_ticketsearch_getticketrows', [
    'path'       => '/ticket-search/ticket-rows.json',
    'controller' => 'AgentBundle:TicketSearch:getTicketRows',
]);

$collection->create('agent_ticketsearch_runfilter', [
    'path'         => '/ticket-search/filter/{filter_id}',
    'controller'   => 'AgentBundle:TicketSearch:runFilter',
    'requirements' => ['filter_id' => '\\d+'],
    'options'      => ['fragment_name' => 'filter', 'fragment_type' => 'list'],
]);

$collection->create('agent_ticketsearch_massactionoverlay', [
    'path'       => '/ticket-search/mass-action-overlay',
    'controller' => 'AgentBundle:TicketSearch:getTicketMassActionOverlay',
]);

$collection->create('agent_ticketsearch_getsubgroupcounts', [
    'path'       => '/ticket-search/subgroup-counts.json',
    'controller' => 'AgentBundle:TicketSearch:getSubgroupCounts',
]);

$collection->create('agent_ticketsearch_runnamedfilter', [
    'path'       => '/ticket-search/filter/{filter_name}',
    'controller' => 'AgentBundle:TicketSearch:runNamedFilter',
    'options'    => ['fragment_name' => 'inbox', 'fragment_type' => 'list'],
]);

$collection->create('agent_ticketsearch_runsla', [
    'path'         => '/ticket-search/sla/{sla_id}/{sla_status}',
    'controller'   => 'AgentBundle:TicketSearch:runSla',
    'defaults'     => ['sla_status' => ''],
    'requirements' => ['sla_id' => '\\d+'],
    'options'      => ['fragment_name' => 'sla', 'fragment_type' => 'list'],
]);

$collection->create('agent_ticketsearch_ajax_get_macro', [
    'path'       => '/ticket-search/ajax-get-macro',
    'controller' => 'AgentBundle:TicketSearch:ajaxGetMacro',
]);

$collection->create('agent_ticketsearch_ajax_get_macro_actions', [
    'path'       => '/ticket-search/ajax-get-macro-actions',
    'controller' => 'AgentBundle:TicketSearch:ajaxGetMacroActions',
]);

$collection->create('agent_ticketsearch_ajax_save_actions', [
    'path'       => '/ticket-search/ajax-save-actions',
    'controller' => 'AgentBundle:TicketSearch:ajaxSaveActions',
]);

$collection->create('agent_ticketsearch_ajax_delete_tickets', [
    'path'       => '/ticket-search/ajax-delete-tickets',
    'controller' => 'AgentBundle:TicketSearch:ajaxDeleteTickets',
]);

$collection->create('agent_ticketsearch_ajax_release_locks', [
    'path'       => '/ticket-search/ajax-release-locks',
    'controller' => 'AgentBundle:TicketSearch:ajaxReleaseLocks',
]);

$collection->create('agent_ticket_new', [
    'path'       => '/tickets/new',
    'controller' => 'AgentBundle:Ticket:new',
    'options'    => ['fragment_name' => 'nt'],
]);

$collection->create('agent_ticket_new_save', [
    'path'       => '/tickets/new/save',
    'controller' => 'AgentBundle:Ticket:newSave',
]);

$collection->create('agent_ticket_new_getpersonrow', [
    'path'       => '/tickets/new/get-person-row/{person_id}',
    'controller' => 'AgentBundle:Ticket:newticketGetPersonRow',
]);

$collection->create('agent_ticket_new_getcustomfieldsrow', [
    'path'       => '/tickets/new/get-custom-fields-row/{person_id}/{department_id}',
    'controller' => 'AgentBundle:Ticket:newTicketGetCustomFieldsRow',
]);

$collection->create('agent_ticket_update_drafts', [
    'path'       => '/tickets/update-drafts',
    'controller' => 'AgentBundle:Ticket:updateDrafts',
]);

$collection->create('agent_ticket_getmessagetext', [
    'path'       => '/tickets/messages/{message_id}/get-message-text.json',
    'controller' => 'AgentBundle:Ticket:ajaxGetMessageText',
]);

$collection->create('agent_ticket_getfullmessage', [
    'path'       => '/tickets/messages/{message_id}/get-full-message.json',
    'controller' => 'AgentBundle:Ticket:ajaxGetFullMessage',
]);

$collection->create('agent_ticket_savemessagetext', [
    'path'       => '/tickets/messages/{message_id}/save-message-text.json',
    'controller' => 'AgentBundle:Ticket:ajaxSaveMessageText',
]);

$collection->create('agent_ticket_setmessagenote', [
    'path'       => '/tickets/messages/{message_id}/set-message-note.json',
    'controller' => 'AgentBundle:Ticket:ajaxSetNote',
]);

$collection->create('agent_ticket_message_attachments', [
    'path'       => '/tickets/messages/{message_id}/attachments',
    'controller' => 'AgentBundle:Ticket:getMessageAttachments',
]);

$collection->create('agent_ticket_message_attachment_delete', [
    'path'       => '/tickets/messages/{message_id}/attachments/{attachment_id}/delete',
    'controller' => 'AgentBundle:Ticket:deleteMessageAttachment',
    'methods'    => ['POST'],
]);

$collection->create('agent_ticket_message_delete', [
    'path'       => '/tickets/messages/{message_id}/delete',
    'controller' => 'AgentBundle:Ticket:deleteMessage',
    'methods'    => ['POST'],
]);

$collection->create('agent_ticket_view', [
    'path'         => '/tickets/{ticket_id}',
    'controller'   => 'AgentBundle:Ticket:view',
    'requirements' => ['ticket_id' => '\\d+'],
    'options'      => ['fragment_name' => 't'],
]);

$collection->create('agent_ticket_loadlogs', [
    'path'         => '/tickets/{ticket_id}/load-logs',
    'controller'   => 'AgentBundle:Ticket:loadTicketLogs',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_loadattachlist', [
    'path'         => '/tickets/{ticket_id}/load-attach-list',
    'controller'   => 'AgentBundle:Ticket:loadAttachList',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_download_debug_report', [
    'path'         => '/tickets/{ticket_id}/download-debug-report',
    'controller'   => 'AgentBundle:Ticket:downloadTicketDebug',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_messagepage', [
    'path'         => '/tickets/{ticket_id}/message-page/{page}',
    'controller'   => 'AgentBundle:Ticket:getMessagePage',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_doupdate', [
    'path'         => '/tickets/{ticket_id}/update-views.json',
    'controller'   => 'AgentBundle:Ticket:updateViews',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_lock', [
    'path'         => '/tickets/{ticket_id}/lock-ticket.json',
    'controller'   => 'AgentBundle:Ticket:lockTicket',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_unlock', [
    'path'         => '/tickets/{ticket_id}/unlock-ticket.json',
    'controller'   => 'AgentBundle:Ticket:unlockTicket',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_release_lock', [
    'path'         => '/tickets/{ticket_id}/release-lock.json',
    'controller'   => 'AgentBundle:Ticket:releaseLock',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_split', [
    'path'         => '/tickets/{ticket_id}/split/{message_id}',
    'controller'   => 'AgentBundle:Ticket:split',
    'defaults'     => ['message_id' => 0],
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_split_save', [
    'path'         => '/tickets/{ticket_id}/split-save',
    'controller'   => 'AgentBundle:Ticket:splitSave',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_merge_overlay', [
    'path'         => '/tickets/{ticket_id}/merge-overlay/{other_ticket_id}',
    'controller'   => 'AgentBundle:Ticket:mergeOverlay',
    'requirements' => ['ticket_id' => '\\d+', 'other_ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_fwd_overlay', [
    'path'         => '/tickets/{ticket_id}/forward/{message_id}',
    'controller'   => 'AgentBundle:Ticket:forwardOverlay',
    'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
]);

$collection->create('agent_ticket_fwd_send_legacy', [
    'path'         => '/tickets/{ticket_id}/forward/{message_id}/send',
    'controller'   => 'AgentBundle:Ticket:forwardSendLegacy',
    'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_ticket_fwd_send', [
    'path'         => '/tickets/{ticket_id}/forward/send',
    'controller'   => 'AgentBundle:Ticket:forwardSend',
    'requirements' => ['ticket_id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_ticket_merge', [
    'path'         => '/tickets/{ticket_id}/merge/{other_ticket_id}',
    'controller'   => 'AgentBundle:Ticket:merge',
    'requirements' => ['ticket_id' => '\\d+', 'other_ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_changeuser_overlay', [
    'path'         => '/tickets/{ticket_id}/change-user-overlay',
    'controller'   => 'AgentBundle:Ticket:changeUserOverlay',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_changeuser_overlay_preview', [
    'path'         => '/tickets/{ticket_id}/change-user-overlay/preview/{new_person_id}',
    'controller'   => 'AgentBundle:Ticket:changeUserOverlayPreview',
    'requirements' => ['ticket_id' => '\\d+', 'new_person_id' => '\\d+'],
]);

$collection->create('agent_ticket_changeuser', [
    'path'         => '/tickets/{ticket_id}/change-user',
    'controller'   => 'AgentBundle:Ticket:changeUser',
    'requirements' => ['ticket_id' => '\\d+', 'new_person_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajaxsavereply', [
    'path'         => '/tickets/{ticket_id}/ajax-save-reply',
    'controller'   => 'AgentBundle:Ticket:ajaxSaveReply',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajaxsavesubject', [
    'path'         => '/tickets/{ticket_id}/ajax-save-subject.json',
    'controller'   => 'AgentBundle:Ticket:ajaxSaveSubject',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajaxchangeuseremail', [
    'path'         => '/tickets/{ticket_id}/ajax-change-email.json',
    'controller'   => 'AgentBundle:Ticket:ajaxChangeUserEmail',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajaxsaveoptions', [
    'path'         => '/tickets/{ticket_id}/ajax-save-options',
    'controller'   => 'AgentBundle:Ticket:ajaxSaveOptions',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajaxsaveflagged', [
    'path'         => '/tickets/{ticket_id}/ajax-save-flagged',
    'controller'   => 'AgentBundle:Ticket:ajaxSaveFlagged',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_addpart', [
    'path'       => '/tickets/{ticket_id}/add-part',
    'controller' => 'AgentBundle:Ticket:addParticipant',
]);

$collection->create('agent_ticket_set_agent_parts', [
    'path'       => '/tickets/{ticket_id}/set-agent-parts.json',
    'controller' => 'AgentBundle:Ticket:setAgentParticipants',
]);

$collection->create('agent_ticket_delpart', [
    'path'       => '/tickets/{ticket_id}/remove-part.json',
    'controller' => 'AgentBundle:Ticket:removeParticipant',
]);

$collection->create('agent_ticket_ajaxtab_releated_content', [
    'path'         => '/tickets/{ticket_id}/ajax-tab-related-content',
    'controller'   => 'AgentBundle:Ticket:ajaxTabRelatedContent',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajax_labels_save', [
    'path'         => '/tickets/{ticket_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Ticket:ajaxSaveLabels',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajax_get_macro', [
    'path'         => '/tickets/{ticket_id}/ajax-get-macro',
    'controller'   => 'AgentBundle:Ticket:ajaxGetMacro',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajax_apply_macro', [
    'path'         => '/tickets/{ticket_id}/{macro_id}/apply-macro.json',
    'controller'   => 'AgentBundle:Ticket:applyMacro',
    'requirements' => ['ticket_id' => '\\d+', 'macro_id' => '\\d+'],
]);

$collection->create('agent_ticket_ajax_save_actions', [
    'path'         => '/tickets/{ticket_id}/ajax-save-actions',
    'controller'   => 'AgentBundle:Ticket:ajaxSaveActions',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create(
    'agent_ticket_ajax_get_dataholders',
    [
        'path'         => '/tickets/{ticket_id}/dataholders',
        'controller'   => 'AgentBundle:Ticket:getDataHolders',
        'requirements' => ['ticket_id' => '\\d+'],
    ]
);

$collection->create('agent_ticket_message_raw', [
    'path'         => '/tickets/{ticket_id}/message-details/{message_id}/view-raw',
    'controller'   => 'AgentBundle:Ticket:viewRawMessage',
    'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
]);

$collection->create('agent_ticket_message_window', [
    'path'         => '/tickets/{ticket_id}/message-details/{message_id}/window/{type}',
    'controller'   => 'AgentBundle:Ticket:viewMessageWindow',
    'defaults'     => ['type' => 'normal'],
    'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
]);

$collection->create('agent_ticket_message_ajax_getquote', [
    'path'         => '/tickets/{ticket_id}/message-details/{message_id}/ajax-get-quote',
    'controller'   => 'AgentBundle:Ticket:ajaxGetMessageQuote',
    'requirements' => ['ticket_id' => '\\d+', 'message_id' => '\\d+'],
]);

$collection->create('agent_ticket_saveagentparts', [
    'path'         => '/ticket/{ticket_id}/save-agent-parts',
    'controller'   => 'AgentBundle:Ticket:saveAgentParts',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_addcharge', [
    'path'         => '/ticket/{ticket_id}/add-charge',
    'controller'   => 'AgentBundle:Ticket:addCharge',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_editcharge', [
    'path'         => '/ticket/{ticket_id}/edit-charge/{charge_id}',
    'controller'   => 'AgentBundle:Ticket:editCharge',
    'requirements' => ['ticket_id' => '\\d+', 'charge_id' => '\\d+'],
]);

$collection->create('agent_ticket_chargedelete', [
    'path'         => '/ticket/{ticket_id}/charge/{charge_id}/delete/{security_token}',
    'controller'   => 'AgentBundle:Ticket:deleteCharge',
    'requirements' => ['ticket_id' => '\\d+', 'charge_id' => '\\d+'],
]);

$collection->create(
    'agent_ticket_chargeform',
    [
        'path'         => '/ticket/{ticket_id}/charge/form',
        'controller'   => 'AgentBundle:Ticket:ticketChargeForm',
        'requirements' => ['ticket_id' => '\\d+'],
    ]
);

$collection->create('agent_ticket_addsla', [
    'path'         => '/ticket/{ticket_id}/add-sla',
    'controller'   => 'AgentBundle:Ticket:addSla',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_sladelete', [
    'path'         => '/ticket/{ticket_id}/sla/{sla_id}/delete/{security_token}',
    'controller'   => 'AgentBundle:Ticket:deleteSla',
    'requirements' => ['ticket_id' => '\\d+', 'sla_id' => '\\d+'],
]);

$collection->create('agent_ticket_delete', [
    'path'         => '/tickets/{ticket_id}/delete',
    'controller'   => 'AgentBundle:Ticket:delete',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_spam', [
    'path'         => '/tickets/{ticket_id}/spam',
    'controller'   => 'AgentBundle:Ticket:spam',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_close_problem', [
    'path'         => '/tickets/{ticket_id}/close_problem',
    'controller'   => 'AgentBundle:Ticket:closeProblem',
    'requirements' => ['ticket_id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_ticket_reopen_problem', [
    'path'         => '/tickets/{ticket_id}/reopen_problem',
    'controller'   => 'AgentBundle:Ticket:reopenProblem',
    'requirements' => ['ticket_id' => '\\d+'],
    'methods'      => ['POST'],
]);

$collection->create('agent_ticket_link_existing_overlay', [
    'path'         => '/tickets/{ticket_id}/link-overlay',
    'controller'   => 'AgentBundle:Ticket:linkExistingOverlay',
    'requirements' => ['ticket_id' => '\\d+'],
]);

$collection->create('agent_ticket_link_existing', [
    'path'       => '/tickets/{ticket_id}/link/{linked_ticket_id}',
    'controller' => 'AgentBundle:Ticket:linkExisting',
    'methods'    => ['POST'],
]);

$collection->create('agent_ticket_unlink', [
    'path'       => '/tickets/{ticket_id}/unlink-ticket',
    'controller' => 'AgentBundle:Ticket:unlinkTicket',
    'methods'    => ['POST'],
]);

$collection->create('agent_ticket_departments_by_brand', [
    'path'       => '/tickets/new/get-departments/{brandId}',
    'defaults'   => ['brandId' => null],
    'controller' => 'AgentBundle:Ticket:ajaxGetDepartments',
    'methods'    => ['GET'],
]);

$collection->create('agent_twitter_new', [
    'path'       => '/twitter/new',
    'controller' => 'AgentBundle:Twitter:newTweet',
]);

$collection->create('agent_twitter_new_save', [
    'path'       => '/twitter/new/save',
    'controller' => 'AgentBundle:Twitter:newTweetSave',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_mine_list', [
    'path'         => '/twitter/mine/{account_id}/{group}/{group_value}',
    'controller'   => 'AgentBundle:TwitterStatus:listMine',
    'defaults'     => ['group' => '', 'group_value' => ''],
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-own', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_team_list', [
    'path'         => '/twitter/team/{account_id}/{group}/{group_value}',
    'controller'   => 'AgentBundle:TwitterStatus:listTeam',
    'defaults'     => ['group' => '', 'group_value' => ''],
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-team', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_unassigned_list', [
    'path'         => '/twitter/unassigned/{account_id}/{group}/{group_value}',
    'controller'   => 'AgentBundle:TwitterStatus:listUnassigned',
    'defaults'     => ['group' => '', 'group_value' => ''],
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-unassigned', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_all_list', [
    'path'         => '/twitter/all/{account_id}/{group}/{group_value}',
    'controller'   => 'AgentBundle:TwitterStatus:listAll',
    'defaults'     => ['group' => '', 'group_value' => ''],
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-all', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_sent_list', [
    'path'         => '/twitter/sent/{account_id}/{group}/{group_value}',
    'controller'   => 'AgentBundle:TwitterStatus:listSent',
    'defaults'     => ['group' => '', 'group_value' => ''],
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-sent', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_timeline_list', [
    'path'         => '/twitter/timeline/{account_id}/{group}/{group_value}',
    'controller'   => 'AgentBundle:TwitterStatus:listTimeline',
    'defaults'     => ['group' => '', 'group_value' => ''],
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-timeline', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_followers_list', [
    'path'         => '/twitter/followers/{account_id}',
    'controller'   => 'AgentBundle:TwitterUser:listFollowers',
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-followers', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_followers_list_new', [
    'path'         => '/twitter/followers/{account_id}/new',
    'controller'   => 'AgentBundle:TwitterUser:listNewFollowers',
    'requirements' => ['account_id' => '\\d+'],
    'options'      => [
        'fragment_name' => 'tw-newfollowers',
        'fragment_type' => 'list',
    ],
]);

$collection->create('agent_twitter_following_list', [
    'path'         => '/twitter/following/{account_id}',
    'controller'   => 'AgentBundle:TwitterUser:listFollowing',
    'requirements' => ['account_id' => '\\d+'],
    'options'      => ['fragment_name' => 'tw-following', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_status_ajaxmasssave', [
    'path'       => '/twitter/status/ajax-mass-save.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxMassSave',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_note', [
    'path'       => '/twitter/status/ajax-note.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveNote',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_retweet', [
    'path'       => '/twitter/status/ajax-retweet.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveRetweet',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_unretweet', [
    'path'       => '/twitter/status/ajax-unretweet.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveUnretweet',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_reply', [
    'path'       => '/twitter/status/ajax-reply.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveReply',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_archive', [
    'path'       => '/twitter/status/ajax-archive.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveArchive',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_delete', [
    'path'       => '/twitter/status/ajax-delete.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveDelete',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_edit', [
    'path'       => '/twitter/status/ajax-edit',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveEdit',
]);

$collection->create('agent_twitter_status_ajaxsave_favorite', [
    'path'       => '/twitter/status/ajax-favorite.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveFavorite',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_ajaxsave_assign', [
    'path'       => '/twitter/status/ajax-assign.json',
    'controller' => 'AgentBundle:TwitterStatus:ajaxSaveAssign',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_status_tweet_overlay', [
    'path'       => '/twitter/status/tweet-overlay',
    'controller' => 'AgentBundle:TwitterStatus:tweetOverlay',
]);

$collection->create('agent_twitter_user', [
    'path'         => '/twitter/user/{user_id}',
    'controller'   => 'AgentBundle:TwitterUser:view',
    'requirements' => ['user_id' => '\\d+'],
    'options'      => ['fragment_name' => 'twitter'],
]);

$collection->create('agent_twitter_user_statuses', [
    'path'         => '/twitter/user/{user_id}/statuses',
    'controller'   => 'AgentBundle:TwitterUser:viewUserStatuses',
    'requirements' => ['user_id' => '\\d+'],
]);

$collection->create('agent_twitter_user_following', [
    'path'         => '/twitter/user/{user_id}/following',
    'controller'   => 'AgentBundle:TwitterUser:viewUserFollowing',
    'requirements' => ['user_id' => '\\d+'],
]);

$collection->create('agent_twitter_user_followers', [
    'path'         => '/twitter/user/{user_id}/followers',
    'controller'   => 'AgentBundle:TwitterUser:viewUserFollowers',
    'requirements' => ['user_id' => '\\d+'],
]);

$collection->create('agent_twitter_user_find', [
    'path'       => '/twitter/user/find',
    'controller' => 'AgentBundle:TwitterUser:find',
]);

$collection->create('agent_twitter_user_message_overlay', [
    'path'         => '/twitter/user/{user_id}/message-overlay',
    'controller'   => 'AgentBundle:TwitterUser:messageOverlay',
    'requirements' => ['user_id' => '\\d+'],
]);

$collection->create('agent_twitter_user_ajaxsave_follow', [
    'path'       => '/twitter/user/ajax-follow.json',
    'controller' => 'AgentBundle:TwitterUser:ajaxSaveFollow',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_user_ajaxsave_unfollow', [
    'path'       => '/twitter/user/ajax-unfollow.json',
    'controller' => 'AgentBundle:TwitterUser:ajaxSaveUnfollow',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_user_ajaxsave_message', [
    'path'       => '/twitter/user/ajax-message.json',
    'controller' => 'AgentBundle:TwitterUser:ajaxSaveMessage',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_user_ajaxsave_archive', [
    'path'       => '/twitter/user/ajax-archive.json',
    'controller' => 'AgentBundle:TwitterUser:ajaxSaveArchive',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_user_ajaxsave_person', [
    'path'       => '/twitter/user/ajax-person.json',
    'controller' => 'AgentBundle:TwitterUser:ajaxSavePerson',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_user_ajaxsave_organization', [
    'path'       => '/twitter/user/ajax-organization.json',
    'controller' => 'AgentBundle:TwitterUser:ajaxSaveOrganization',
    'methods'    => ['POST'],
]);

$collection->create('agent_twitter_getsectiondata', [
    'path'       => '/twitter/get-section-data.json',
    'controller' => 'AgentBundle:Twitter:getSectionData',
]);

$collection->create('agent_twitter_updategrouping', [
    'path'       => '/twitter/update-grouping.json',
    'controller' => 'AgentBundle:Twitter:updateGrouping',
]);

$collection->create('agent_twitter_run_search', [
    'path'         => '/twitter/{account_id}/search/{search_id}',
    'controller'   => 'AgentBundle:Twitter:runSearch',
    'requirements' => ['account_id' => '\\d+', 'search_id' => '\\d+'],
    'options'      => ['fragment_name' => 'searches', 'fragment_type' => 'list'],
]);

$collection->create('agent_twitter_search_delete', [
    'path'         => '/twitter/{account_id}/search/delete/{security_token}',
    'controller'   => 'AgentBundle:Twitter:deleteSearch',
    'requirements' => ['account_id' => '\\d+'],
]);

$collection->create('agent_twitter_new_search', [
    'path'         => '/twitter/{account_id}/search/new',
    'controller'   => 'AgentBundle:Twitter:newSearch',
    'requirements' => ['account_id' => '\\d+'],
]);

$collection->create('agent_task_new', [
    'path'       => '/tasks/new',
    'controller' => 'AgentBundle:Task:new',
    'options'    => ['fragment_name' => 'nt'],
]);

$collection->create('agent_task_save', [
    'path'       => '/tasks/save',
    'controller' => 'AgentBundle:Task:create',
    'methods'    => ['POST'],
]);

$collection->create('agent_task_delete', [
    'path'         => '/tasks/{task_id}/delete',
    'controller'   => 'AgentBundle:Task:deleteTask',
    'requirements' => ['task_id' => '\\d+'],
]);

$collection->create('agent_tasksearch_getsectiondata', [
    'path'       => '/tasks/get-section-data.json',
    'controller' => 'AgentBundle:Task:getSectionData',
]);

$collection->create('agent_task_list', [
    'path'       => '/tasks/list/{search_type}/{search_category}',
    'controller' => 'AgentBundle:Task:taskList',
    'defaults'   => ['search_type' => null, 'search_category' => null],
    'options'    => ['fragment_name' => 'tasks', 'fragment_type' => 'list'],
]);

$collection->create('agent_task_ajax_labels_save', [
    'path'         => '/tasks/{task_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Task:ajaxSaveLabels',
    'requirements' => ['task_id' => '\\d+'],
]);

$collection->create('agent_task_ajaxsave_comment', [
    'path'         => '/tasks/{task_id}/ajax-save-comment',
    'controller'   => 'AgentBundle:Task:ajaxSaveComment',
    'requirements' => ['task_id' => '\\d+', 'person_id' => '\\d+'],
]);

$collection->create('agent_task_ajaxsave', [
    'path'       => '/tasks/{task_id}/ajax-save',
    'controller' => 'AgentBundle:Task:ajaxSave',
]);

$collection->create('agent_task_ics_all_tasks', [
    'path'         => '/tasks/{id}-{authcode}/all.ics',
    'controller'   => 'AgentBundle:Task:iCal',
    'defaults'     => ['filter' => 'all'],
    'requirements' => ['authcode' => '.*', 'id' => '^\\d+$'],
]);

$collection->create('agent_task_ics_assigned_tasks', [
    'path'         => '/tasks/{id}-{authcode}/assigned.ics',
    'controller'   => 'AgentBundle:Task:iCal',
    'defaults'     => ['filter' => 'assigned'],
    'requirements' => ['authcode' => '.*', 'id' => '^\\d+$'],
]);

$collection->create('agent_task_ics_delegated_tasks', [
    'path'         => '/tasks/{id}-{authcode}/delegated.ics',
    'controller'   => 'AgentBundle:Task:iCal',
    'defaults'     => ['filter' => 'delegated'],
    'requirements' => ['authcode' => '.*', 'id' => '^\\d+$'],
]);

$collection->create('agent_publish_getsectiondata', [
    'path'       => '/publish/get-section-data.json',
    'controller' => 'AgentBundle:Publish:getSectionData',
]);

$collection->create('agent_publish_ratingwhovoted', [
    'path'       => '/publish/rating-who-voted/{objectType}/{objectId}',
    'controller' => 'AgentBundle:Publish:ratingWhoVoted',
]);

$collection->create('agent_publish_whoviewed', [
    'path'       => '/publish/who-viewed/{objectType}/{objectId}/{viewAction}',
    'controller' => 'AgentBundle:Publish:whoViewed',
    'defaults'   => ['viewAction' => 1],
]);

$collection->create('agent_publish_save_stickysearchwords', [
    'path'       => '/publish/save-sticky-search-words/{type}/{content_id}',
    'controller' => 'AgentBundle:Publish:saveStickySearchWords',
]);

$collection->create('agent_publish_validatingcontent', [
    'path'       => '/publish/content/validating',
    'controller' => 'AgentBundle:Publish:listValidatingContent',
    'options'    => [
        'fragment_type' => 'list',
        'fragment_name' => 'validating_content',
    ],
]);

$collection->create('agent_feedback_validatingcontent', [
    'path'       => '/feedback/content/validating',
    'controller' => 'AgentBundle:Feedback:listValidatingContent',
    'options'    => ['fragment_type' => 'list', 'fragment_name' => 'fb_content'],
]);

$collection->create('agent_feedback_validatingcomments', [
    'path'       => '/feedback/comments/validating',
    'controller' => 'AgentBundle:Publish:listValidatingFeedbackComments',
    'options'    => ['fragment_type' => 'list', 'fragment_name' => 'fb_comments'],
]);

$collection->create(
    'agent_feedback_modify_feedback_approvement',
    [
        'path'         => '/publish/content/{action}/feedback/{feedbackId}.json',
        'controller'   => 'AgentBundle:Feedback:modifyFeedbackApprovement',
        'requirements' => ['action' => 'approve|disapprove', 'feedbackId' => '\\d+'],
    ]
);

$collection->create('feedback_validatingcontent_mass', [
    'path'       => '/feedback/validating-mass-actions/{action}',
    'controller' => 'AgentBundle:Feedback:validatingMassActions',
]);

$collection->create(
    'agent_feedback_validating_next',
    [
        'path'         => '/publish/content/get-next-validating/feedback/{feedbackId}.json',
        'controller'   => 'AgentBundle:Feedback:nextValidatingFeedback',
        'options'      => ['fragment_name' => 'pending', 'fragment_type' => 'list'],
        'requirements' => ['feedbackId' => '\\d+'],
    ]
);

$collection->create('agent_publish_listcomments', [
    'path'       => '/publish/comments/list/{brandId}/{type}',
    'controller' => 'AgentBundle:Publish:listComments',
    'options'    => ['fragment_type' => 'list', 'fragment_name' => 'list_comments'],
]);

$collection->create('agent_publish_validatingcomments', [
    'path'       => '/publish/comments/validating',
    'controller' => 'AgentBundle:Publish:listValidatingComments',
    'options'    => [
        'fragment_type' => 'list',
        'fragment_name' => 'validating_comments',
    ],
]);

$collection->create('agent_publish_approve_comment', [
    'path'       => '/publish/comments/approve/{typename}/{commentId}',
    'controller' => 'AgentBundle:Publish:approveComment',
]);

$collection->create('agent_publish_delete_comment', [
    'path'       => '/publish/comments/delete/{typename}/{commentId}',
    'controller' => 'AgentBundle:Publish:deleteComment',
]);

$collection->create('agent_publish_comment_info', [
    'path'       => '/publish/comments/info/{typename}/{commentId}',
    'controller' => 'AgentBundle:Publish:commentInfo',
]);

$collection->create('agent_publish_comment_save', [
    'path'       => '/publish/comments/save-comment/{typename}/{commentId}',
    'controller' => 'AgentBundle:Publish:saveComment',
]);

$collection->create('agent_public_comment_newticketinfo', [
    'path'       => '/publish/comments/new-ticket-info/{typename}/{commentId}.json',
    'controller' => 'AgentBundle:Publish:getNewTicketCommentInfo',
]);

$collection->create('agent_publish_validatingcomments_mass', [
    'path'       => '/publish/comments/validating-mass-actions/{action}',
    'controller' => 'AgentBundle:Publish:validatingCommentsMassActions',
]);

$collection->create('agent_publish_savecats', [
    'path'       => '/publish/save-categories/{type}',
    'controller' => 'AgentBundle:Publish:saveCategories',
]);

$collection->create('agent_publish_cats_adddel', [
    'path'       => '/publish/categories/{type}/delete-category',
    'controller' => 'AgentBundle:Publish:deleteCategory',
]);

$collection->create('agent_publish_cats_addcat', [
    'path'       => '/publish/categories/{type}/add-category',
    'controller' => 'AgentBundle:Publish:addCategory',
]);

$collection->create('agent_publish_cats_updateorders', [
    'path'       => '/publish/categories/{type}/update-orders',
    'controller' => 'AgentBundle:Publish:updateCategoryOrders',
]);

$collection->create('agent_publish_cats_updatetitles', [
    'path'       => '/publish/categories/{type}/update-titles',
    'controller' => 'AgentBundle:Publish:updateCategoryTitles',
]);

$collection->create('agent_publish_cats_update', [
    'path'       => '/publish/categories/{type}/update/{category_id}',
    'controller' => 'AgentBundle:Publish:updateCategory',
]);

$collection->create('agent_publish_cats_updatestructure', [
    'path'       => '/publish/categories/{type}/update-structure',
    'controller' => 'AgentBundle:Publish:updateCategoryStructure',
]);

$collection->create('agent_publish_cats_newform', [
    'path'       => '/publish/categories/{type}/new-form',
    'controller' => 'AgentBundle:Publish:addCategoryForm',
]);

$collection->create('agent_publish_cats_newform_save', [
    'path'       => '/publish/categories/{type}/new-form/save',
    'controller' => 'AgentBundle:Publish:addCategoryFormSave',
]);

$collection->create('agent_public_pending_approval', [
    'path'       => '/publish/pending_approval/{type}',
    'controller' => 'AgentBundle:Publish:listPendingApproval',
    'options'    => ['fragment_name' => 'pending_approval', 'fragment_type' => 'list'],
]);

$collection->create('agent_public_drafts', [
    'path'       => '/publish/drafts/{type}',
    'controller' => 'AgentBundle:Publish:listDrafts',
    'options'    => ['fragment_name' => 'drafts', 'fragment_type' => 'list'],
]);

$collection->create('agent_public_drafts_mass', [
    'path'       => '/publish/drafts/mass-actions/{action}',
    'controller' => 'AgentBundle:Publish:draftsMassActions',
]);

$collection->create('agent_publish_search', [
    'path'       => '/publish/search',
    'controller' => 'AgentBundle:Publish:search',
]);

$collection->create('agent_kb_newarticle_save', [
    'path'       => '/kb/article/new/save',
    'controller' => 'AgentBundle:Kb:newArticleSave',
]);

$collection->create('agent_kb_newarticle', [
    'path'       => '/kb/article/new',
    'controller' => 'AgentBundle:Kb:newArticle',
]);

$collection->create('agent_kb_article', [
    'path'         => '/kb/article/{article_id}',
    'controller'   => 'AgentBundle:Kb:viewArticle',
    'requirements' => ['article_id' => '\\d+'],
    'options'      => ['fragment_name' => 'a'],
]);

$collection->create('agent_kb_ajaxsavecustomfields', [
    'path'         => '/kb/article/{article_id}/ajax-save-custom-fields',
    'controller'   => 'AgentBundle:Kb:ajaxSaveCustomFields',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_kb_article_info', [
    'path'         => '/kb/article/{article_id}/info',
    'controller'   => 'AgentBundle:Kb:articleInfo',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_kb_article_revisionstab', [
    'path'         => '/kb/article/{article_id}/view-revisions',
    'controller'   => 'AgentBundle:Kb:viewRevisions',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_kb_article_ajaxsave', [
    'path'         => '/kb/article/{article_id}/ajax-save',
    'controller'   => 'AgentBundle:Kb:ajaxSave',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_kb_ajax_save_comment', [
    'path'         => '/kb/article/{article_id}/ajax-save-comment',
    'controller'   => 'AgentBundle:Kb:ajaxSaveComment',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_kb_ajax_labels_save', [
    'path'         => '/kb/article/{article_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Kb:ajaxSaveLabels',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_kb_ajax_get_categories', [
    'path'         => '/kb/article/categories/brand/{brand_id}',
    'controller'   => 'AgentBundle:Kb:ajaxGetCategoriesByBrand',
    'requirements' => ['brand_id' => '\\d+'],
]);

$collection->create('agent_kb_comparerevs', [
    'path'       => '/kb/compare-revs/{rev_old_id}/{rev_new_id}',
    'controller' => 'AgentBundle:Kb:compareRevisions',
]);

$collection->create('agent_kb_newpending', [
    'path'       => '/kb/pending-articles/new',
    'controller' => 'AgentBundle:Kb:newPendingArticle',
]);

$collection->create('agent_kb_pending_remove', [
    'path'       => '/kb/pending-articles/{pending_article_id}/remove',
    'controller' => 'AgentBundle:Kb:removePendingArticle',
]);

$collection->create('agent_kb_pending_info', [
    'path'       => '/kb/pending-articles/{pending_article_id}/info',
    'controller' => 'AgentBundle:Kb:pendingArticleInfo',
]);

$collection->create('agent_kb_pending', [
    'path'       => '/kb/pending-articles',
    'controller' => 'AgentBundle:Kb:listPendingArticles',
    'options'    => ['fragment_name' => 'pending', 'fragment_type' => 'list'],
]);

$collection->create('agent_kb_pending_massactions', [
    'path'       => '/kb/pending-articles/mass-actions/{action}',
    'controller' => 'AgentBundle:Kb:pendingArticlesMassActions',
]);

$collection->create('agent_kb_list', [
    'path'       => '/kb/list/{category_id}',
    'controller' => 'AgentBundle:Kb:list',
    'defaults'   => ['category_id' => '0'],
    'options'    => ['fragment_name' => 'knowledgebase', 'fragment_type' => 'list'],
]);

$collection->create('agent_kb_cat', [
    'path'       => '/kb/category/{category_id}',
    'controller' => 'AgentBundle:Kb:list',
]);

$collection->create('agent_kb_mass_save', [
    'path'       => '/kb/article/ajax-mass-save',
    'controller' => 'AgentBundle:Kb:ajaxMassSave',
]);

$collection->create('agent_glossary_newword_json', [
    'path'       => '/glossary/new-word.json',
    'controller' => 'AgentBundle:Glossary:glossaryNewWordJson',
]);

$collection->create('agent_glossary_word_json', [
    'path'       => '/glossary/{word_id}.json',
    'controller' => 'AgentBundle:Glossary:glossaryWordJson',
]);

$collection->create('agent_glossary_saveword_json', [
    'path'       => '/glossary/{word_id}/edit.json',
    'controller' => 'AgentBundle:Glossary:glossarySaveWordJson',
]);

$collection->create('agent_glossary_delword_json', [
    'path'       => '/glossary/{word_id}/delete.json',
    'controller' => 'AgentBundle:Glossary:glossaryDeleteWordJson',
]);

$collection->create('agent_glossary_word_tip', [
    'path'       => '/glossary/{word}/tip',
    'controller' => 'AgentBundle:Glossary:tip',
]);

$collection->create('agent_news_list', [
    'path'       => '/news/list/{category_id}',
    'controller' => 'AgentBundle:News:list',
    'defaults'   => ['category_id' => '0'],
    'options'    => ['fragment_name' => 'news', 'fragment_type' => 'list'],
]);

$collection->create('agent_news_view', [
    'path'         => '/news/post/{news_id}',
    'controller'   => 'AgentBundle:News:view',
    'requirements' => ['news_id' => '\\d+'],
    'options'      => ['fragment_name' => 'n'],
]);

$collection->create('agent_news_revisionstab', [
    'path'         => '/news/post/{news_id}/view-revisions',
    'controller'   => 'AgentBundle:News:viewRevisions',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_news_save', [
    'path'         => '/news/post/{news_id}/ajax-save',
    'controller'   => 'AgentBundle:News:ajaxSave',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_news_ajax_labels_save', [
    'path'         => '/news/{news_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:News:ajaxSaveLabels',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_news_ajax_save_comment', [
    'path'         => '/news/post/{news_id}/ajax-save-comment',
    'controller'   => 'AgentBundle:News:ajaxSaveComment',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_news_new_save', [
    'path'       => '/news/new/save',
    'controller' => 'AgentBundle:News:newNewsSave',
]);

$collection->create('agent_news_new', [
    'path'       => '/news/new',
    'controller' => 'AgentBundle:News:newNews',
]);

$collection->create('agent_news_comparerevs', [
    'path'       => '/news/compare-revs/{rev_old_id}/{rev_new_id}',
    'controller' => 'AgentBundle:News:compareRevisions',
]);

$collection->create('agent_news_ajax_get_categories', [
    'path'         => '/news/categories/brand/{brand_id}',
    'controller'   => 'AgentBundle:News:ajaxGetCategoriesByBrand',
    'requirements' => ['brand_id' => '\\d+'],
]);

$collection->create('agent_downloads_list', [
    'path'       => '/downloads/list/{category_id}',
    'controller' => 'AgentBundle:Downloads:list',
    'defaults'   => ['category_id' => '0'],
    'options'    => ['fragment_name' => 'downloads', 'fragment_type' => 'list'],
]);

$collection->create('agent_downloads_view', [
    'path'         => '/downloads/file/{download_id}',
    'controller'   => 'AgentBundle:Downloads:view',
    'requirements' => ['download_id' => '\\d+'],
    'options'      => ['fragment_name' => 'd'],
]);

$collection->create('agent_downloads_info', [
    'path'         => '/downloads/file/{download_id}/info',
    'controller'   => 'AgentBundle:Downloads:info',
    'requirements' => ['download_id' => '\\d+'],
]);

$collection->create('agent_kb_downloads_revisionstab', [
    'path'         => '/downloads/file/{download_id}/view-revisions',
    'controller'   => 'AgentBundle:Downloads:viewRevisions',
    'requirements' => ['article_id' => '\\d+'],
]);

$collection->create('agent_downloads_save', [
    'path'         => '/downloads/file/{download_id}/ajax-save',
    'controller'   => 'AgentBundle:Downloads:ajaxSave',
    'requirements' => ['download_id' => '\\d+'],
]);

$collection->create('agent_downloads_ajax_labels_save', [
    'path'         => '/downloads/file/{download_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Downloads:ajaxSaveLabels',
    'requirements' => ['download_id' => '\\d+'],
]);

$collection->create('agent_downloads_ajax_save_comment', [
    'path'         => '/downloads/file/{download_id}/ajax-save-comment',
    'controller'   => 'AgentBundle:Downloads:ajaxSaveComment',
    'requirements' => ['download_id' => '\\d+'],
]);

$collection->create('agent_downloads_new_save', [
    'path'       => '/downloads/new/save',
    'controller' => 'AgentBundle:Downloads:newDownloadSave',
]);

$collection->create('agent_downloads_new', [
    'path'       => '/downloads/new',
    'controller' => 'AgentBundle:Downloads:newDownload',
]);

$collection->create('agent_downloads_comparerevs', [
    'path'       => '/downloads/compare-revs/{rev_old_id}/{rev_new_id}',
    'controller' => 'AgentBundle:Downloads:compareRevisions',
]);

$collection->create('agent_downloads_ajax_get_categories', [
    'path'         => '/downloads/categories/brand/{brand_id}',
    'controller'   => 'AgentBundle:Downloads:ajaxGetCategoriesByBrand',
    'requirements' => ['brand_id' => '\\d+'],
]);

$collection->create('agent_feedback_category', [
    'path'       => '/feedback/category/{category_id}',
    'controller' => 'AgentBundle:Feedback:categoryList',
    'options'    => ['fragment_name' => 'category', 'fragment_type' => 'list'],
]);

$collection->create('agent_feedback_status', [
    'path'       => '/feedback/status/{status}',
    'controller' => 'AgentBundle:Feedback:statusList',
    'options'    => ['fragment_name' => 'status', 'fragment_type' => 'list'],
]);

$collection->create('agent_feedback_label', [
    'path'         => '/feedback/label/{label}',
    'controller'   => 'AgentBundle:Feedback:labelList',
    'options'      => ['fragment_name' => 'label', 'fragment_type' => 'list'],
    'requirements' => ['label' => '.*'],
]);

$collection->create('agent_feedback_filter', [
    'path'       => '/feedback/filter',
    'controller' => 'AgentBundle:Feedback:filterList',
]);

$collection->create('agent_feedback_massactions', [
    'path'       => '/feedback/filter/mass-actions/{action}',
    'controller' => 'AgentBundle:Feedback:massActions',
]);

$collection->create('agent_feedback_getsectiondata', [
    'path'       => '/feedback/get-section-data.json',
    'controller' => 'AgentBundle:Feedback:getSectionData',
]);

$collection->create('agent_feedback_new', [
    'path'       => '/feedback/new',
    'controller' => 'AgentBundle:Feedback:newFeedback',
]);

$collection->create('agent_feedback_new_save', [
    'path'       => '/feedback/new/save',
    'controller' => 'AgentBundle:Feedback:newFeedbackSave',
]);

$collection->create('agent_feedback_view', [
    'path'       => '/feedback/view/{feedback_id}',
    'controller' => 'AgentBundle:Feedback:view',
    'options'    => ['fragment_name' => 'i'],
]);

$collection->create('agent_feedback_comparerevs', [
    'path'       => '/feedback/compare-revs/{rev_old_id}/{rev_new_id}',
    'controller' => 'AgentBundle:Feedback:compareRevisions',
]);

$collection->create('agent_feedback_ajaxsavecustomfields', [
    'path'         => '/feedback/view/{feedback_id}/ajax-save-custom-fields',
    'controller'   => 'AgentBundle:Feedback:ajaxSaveCustomFields',
    'requirements' => ['feedback_id' => '\\d+'],
]);

$collection->create('agent_feedback_who_voted', [
    'path'       => '/feedback/view/{feedback_id}/who-voted',
    'controller' => 'AgentBundle:Feedback:whoVoted',
]);

$collection->create('agent_feedback_save', [
    'path'         => '/feedback/view/{feedback_id}/ajax-save',
    'controller'   => 'AgentBundle:Feedback:ajaxSave',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_feedback_ajax_labels_save', [
    'path'         => '/feedback/view/{feedback_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:Feedback:ajaxSaveLabels',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_feedback_ajax_save_comment', [
    'path'         => '/feedback/view/{feedback_id}/ajax-save-comment',
    'controller'   => 'AgentBundle:Feedback:ajaxSaveComment',
    'requirements' => ['news_id' => '\\d+'],
]);

$collection->create('agent_feedback_ajaxsavecomment', [
    'path'       => '/feedback/view/{feedback_id}/ajax-save-comment',
    'controller' => 'AgentBundle:Feedback:ajaxSaveComment',
]);

$collection->create('agent_feedback_ajaxsaveeditables', [
    'path'       => '/feedback/view/{feedback_id}/ajax-save-editables',
    'controller' => 'AgentBundle:Feedback:ajaxSaveEditables',
]);

$collection->create('agent_feedback_ajaxupdatecat', [
    'path'       => '/feedback/view/{feedback_id}/ajax-update-category/{category_id}',
    'controller' => 'AgentBundle:Feedback:ajaxUpdateCategory',
]);

$collection->create('agent_feedback_ajaxupdatestatus', [
    'path'       => '/feedback/view/{feedback_id}/ajax-update-status/{status_code}',
    'controller' => 'AgentBundle:Feedback:ajaxUpdateStatus',
]);

$collection->create('agent_feedback_merge_overlay', [
    'path'         => '/feedback/merge-overlay/{feedback_id}/{other_feedback_id}',
    'controller'   => 'AgentBundle:Feedback:mergeOverlay',
    'requirements' => ['feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'],
]);

$collection->create('agent_feedback_merge', [
    'path'         => '/feedback/merge/{feedback_id}/{other_feedback_id}',
    'controller'   => 'AgentBundle:Feedback:merge',
    'requirements' => ['feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'],
]);

$collection->create('agent_topic_new', [
    'path'       => '/guides/new',
    'controller' => 'AgentBundle:Guide:newTopic',
]);

$collection->create('agent_topic_view', [
    'path'         => '/guides/topic/{topic_id}',
    'controller'   => 'AgentBundle:Guide:view',
    'requirements' => ['topic_id' => '\\d+'],
    'options'      => ['fragment_name' => 'm'],
]);

$collection->create('agent_topic_revisionstab', [
    'path'         => '/guides/topic/{topic_id}/view-revisions',
    'controller'   => 'AgentBundle:Guide:viewRevisions',
    'requirements' => ['topic_id' => '\\d+'],
]);

$collection->create('agent_topic_save', [
    'path'         => '/guides/topic/{topic_id}/ajax-save',
    'controller'   => 'AgentBundle:Guide:ajaxSave',
    'requirements' => ['topic_id' => '\\d+'],
]);

$collection->create('agent_topic_new_save', [
    'path'       => '/guides/new/save',
    'controller' => 'AgentBundle:Guide:newTopicSave',
]);

$collection->create('agent_guides_list', [
    'path'       => '/guides/list/{guide_id}',
    'controller' => 'AgentBundle:Guide:list',
    'defaults'   => ['guide_id' => '0'],
    'options'    => ['fragment_name' => 'guides', 'fragment_type' => 'list'],
]);

$collection->create('agent_topic_ajax_save_comment', [
    'path'         => '/guides/topic/{topic_id}/ajax-save-comment',
    'controller'   => 'AgentBundle:Guide:ajaxSaveComment',
    'requirements' => ['topic_id' => '\\d+'],
]);

$collection->create('agent_publish_guides_newform', [
    'path'       => '/guides/new-form',
    'controller' => 'AgentBundle:Guide:addCategoryForm',
]);

$collection->create('agent_topic_ajax_get_guides', [
    'path'         => '/guides/brand/{brand_id}',
    'controller'   => 'AgentBundle:Guide:ajaxGetGuidesByBrand',
    'requirements' => ['brand_id' => '\\d+'],
]);

$collection->create('agent_topic_ajax_get_topics', [
    'path'         => '/guides/topics/{guide_id}',
    'controller'   => 'AgentBundle:Guide:ajaxGetTopicsByGuide',
    'requirements' => ['guide_id' => '\\d+'],
]);

$collection->create('agent_guides_comparerevs', [
    'path'       => '/guides/compare-revs/{rev_old_id}/{rev_new_id}',
    'controller' => 'AgentBundle:Guide:compareRevisions',
]);

$collection->create('agent_agentchat_getonlineagents', [
    'path'       => '/agent-chat/get-online-agents.json',
    'controller' => 'AgentBundle:AgentChat:getOnlineAgents',
]);

$collection->create('agent_agentchat_get_last_convo', [
    'path'       => '/agent-chat/get-last-convo',
    'controller' => 'AgentBundle:AgentChat:loadConvoMessages',
]);

$collection->create('agent_agentchat_send_message', [
    'path'       => '/agent-chat/send-message/{conversation_id}',
    'controller' => 'AgentBundle:AgentChat:sendMessage',
]);

$collection->create('agent_agentchat_send_agent_message', [
    'path'       => '/agent-chat/send-agent-message/{convo_id}',
    'controller' => 'AgentBundle:AgentChat:sendAgentMessage',
]);

$collection->create('agent_agentchat_history', [
    'path'       => '/agent-chat/agent-history/{agent_id}',
    'controller' => 'AgentBundle:AgentChat:agentHistory',
]);

$collection->create('agent_agentchat_history_team', [
    'path'       => '/agent-chat/agent-history/team/{agent_team_id}',
    'controller' => 'AgentBundle:AgentChat:agentTeamHistory',
]);

$collection->create('agent_agentchat_view', [
    'path'       => '/agent-chat/agent-transcript/{conversation_id}',
    'controller' => 'AgentBundle:AgentChat:agentChatTranscript',
]);

$collection->create('agent_agentchat_getsectiondata', [
    'path'       => '/agent-chat/get-section-data.json',
    'controller' => 'AgentBundle:AgentChat:getSectionData',
]);

$collection->create('agent_userchat_view', [
    'path'       => '/chat/view/{conversation_id}/{action}',
    'controller' => 'AgentBundle:UserChat:view',
    'defaults'   => ['action' => '-1'],
    'options'    => ['fragment_name' => 'c'],
]);

$collection->create('agent_userchat_delete', [
    'path'       => '/chat/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:delete',
    'methods'    => ['DELETE'],
]);

$collection->create('agent_userchat_join', [
    'path'       => '/chat/join/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:joinChat',
]);

$collection->create('agent_userchat_save_fields', [
    'path'       => '/chat/{conversation_id}/save-fields',
    'controller' => 'AgentBundle:UserChat:saveFields',
    'methods'    => ['POST'],
]);

$collection->create('agent_userchat_blockuser', [
    'path'       => '/chat/block-user/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:blockUser',
]);

$collection->create('agent_userchat_unblockuser', [
    'path'       => '/chat/unblock-user/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:unblockUser',
]);

$collection->create('agent_userchat_ajax_labels_save', [
    'path'         => '/chat/{conversation_id}/ajax-save-labels',
    'controller'   => 'AgentBundle:UserChat:ajaxSaveLabels',
    'requirements' => ['conversation_id' => '\\d+'],
]);

$collection->create('agent_userchat_open_counts', [
    'path'       => '/chat/open-counts.json',
    'controller' => 'AgentBundle:UserChat:getOpenCounts',
]);

$collection->create('agent_userchat_filterlist_group_counts', [
    'path'       => '/chat/group-count.json',
    'controller' => 'AgentBundle:UserChat:getGroupByCounts',
]);

$collection->create('agent_userchat_filterlist', [
    'path'       => '/chat/filter/{filter_id}',
    'controller' => 'AgentBundle:UserChat:filter',
    'options'    => ['fragment_name' => 'ended', 'fragment_type' => 'list'],
]);

$collection->create('agent_userchat_list_new', [
    'path'       => '/chat/list-new/{department_id}',
    'controller' => 'AgentBundle:UserChat:listNewChats',
    'defaults'   => ['department_id' => '-1'],
    'options'    => ['fragment_name' => 'new', 'fragment_type' => 'list'],
]);

$collection->create('agent_userchat_list_active', [
    'path'       => '/chat/list-active/{agent_id}',
    'controller' => 'AgentBundle:UserChat:listActiveChats',
    'defaults'   => ['agent_id' => '-1'],
    'options'    => ['fragment_name' => 'active', 'fragment_type' => 'list'],
]);

$collection->create('agent_userchat_typing', [
    'path'       => '/chat/typing/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:typing',
]);

$collection->create('agent_userchat_send_messageview', [
    'path'       => '/chat/send-message/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:sendMessage',
]);

$collection->create('agent_userchat_send_filemessage', [
    'path'       => '/chat/send-file-message/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:sendFile',
]);

$collection->create('agent_userchat_assign', [
    'path'       => '/chat/assign/{conversation_id}/{agent_id}',
    'controller' => 'AgentBundle:UserChat:assignChat',
]);

$collection->create('agent_userchat_syncpart', [
    'path'       => '/chat/sync-parts/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:syncParts',
]);

$collection->create('agent_userchat_addpart', [
    'path'       => '/chat/add-part/{conversation_id}/{agent_id}',
    'controller' => 'AgentBundle:UserChat:addPart',
]);

$collection->create('agent_userchat_end', [
    'path'       => '/chat/end-chat/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:endChat',
]);

$collection->create('agent_userchat_leave', [
    'path'       => '/chat/leave/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:leaveChat',
]);

$collection->create('agent_userchat_invite', [
    'path'       => '/chat/invite/{conversation_id}/{agent_id}',
    'controller' => 'AgentBundle:UserChat:sendInvite',
]);

$collection->create('agent_userchat_changeprop', [
    'path'       => '/chat/change-props/{conversation_id}',
    'controller' => 'AgentBundle:UserChat:changeProperties',
]);

$collection->create('agent_userchat_getsectiondata', [
    'path'       => '/chat/get-section-data.json',
    'controller' => 'AgentBundle:UserChat:getSectionData',
]);

$collection->create('agent_mediamanager', [
    'path'       => '/media-manager',
    'controller' => 'AgentBundle:MediaManager:window',
]);

$collection->create('agent_mediamanager_upload', [
    'path'       => '/media-manager/upload',
    'controller' => 'AgentBundle:MediaManager:upload',
]);

$collection->create('agent_mediamanager_browse', [
    'path'       => '/media-manager/browse',
    'controller' => 'AgentBundle:MediaManager:browse',
]);

$collection->create('agent_textsnippets_widget_shell', [
    'path'       => '/text-snippets/{typename}/widget-shell.txt',
    'controller' => 'AgentBundle:TextSnippets:getWidgetShell',
]);

$collection->create('agent_textsnippets_reloadclient', [
    'path'       => '/text-snippets/{typename}/reload-client.json',
    'controller' => 'AgentBundle:TextSnippets:reloadClient',
]);

$collection->create('agent_textsnippets_reloadclient_batch', [
    'path'       => '/text-snippets/{typename}/reload-client/{batch}.json',
    'controller' => 'AgentBundle:TextSnippets:reloadClientBatch',
]);

$collection->create('agent_textsnippets_filtersnippets', [
    'path'       => '/text-snippets/{typename}/filter.json',
    'controller' => 'AgentBundle:TextSnippets:filterSnippets',
]);

$collection->create('agent_textsnippets_getsnippet', [
    'path'       => '/text-snippets/{typename}/{id}.json',
    'controller' => 'AgentBundle:TextSnippets:getSnippet',
]);

$collection->create('agent_textsnippets_savesnippet', [
    'path'       => '/text-snippets/{typename}/{id}/save.json',
    'controller' => 'AgentBundle:TextSnippets:saveSnippet',
]);

$collection->create('agent_textsnippets_delsnippet', [
    'path'       => '/text-snippets/{typename}/{id}/delete.json',
    'controller' => 'AgentBundle:TextSnippets:deleteSnippet',
]);

$collection->create('agent_textsnippets_savecat', [
    'path'       => '/text-snippets/{typename}/categories/{id}/save.json',
    'controller' => 'AgentBundle:TextSnippets:saveCategory',
]);

$collection->create('agent_textsnippets_delcat', [
    'path'       => '/text-snippets/{typename}/categories/{id}/delete.json',
    'controller' => 'AgentBundle:TextSnippets:deleteCategory',
]);

$collection->create('agent_apps_run', [
    'path'         => '/apps/{app_id}/{action}',
    'defaults'     => ['action' => 'default'],
    'controller'   => 'AgentBundle:Apps:run',
    'requirements' => ['app_id' => '\\d+'],
]);

$collection->create('agent_label_definitions_list', [
    'path'       => '/labels/definitions',
    'controller' => 'AgentBundle:Labels:listDefinitions',
    'methods'    => ['GET'],
]);

$collection->create('agent_jira_meta', [
    'path'       => '/jira/meta',
    'controller' => 'AgentBundle:Jira:getMeta',
    'methods'    => ['GET'],
]);

$collection->create('agent_jira_createmeta', [
    'path'       => '/jira/createmeta',
    'controller' => 'AgentBundle:Jira:getCreateMeta',
    'methods'    => ['GET'],
]);

$collection->create('agent_jira_search', [
    'path'       => '/jira/search',
    'controller' => 'AgentBundle:Jira:search',
    'methods'    => ['GET'],
]);

$collection->create('agent_jira_ticket_issues_create', [
    'path'         => '/jira/ticket/{ticketId}/issue',
    'controller'   => 'AgentBundle:Jira:createIssue',
    'methods'      => ['POST'],
    'requirements' => ['ticketId' => '\\d+'],
]);

$collection->create('agent_jira_ticket_issue_update', [
    'path'         => '/jira/ticket/{ticketId}/issue/{issueId}',
    'controller'   => 'AgentBundle:Jira:updateIssue',
    'methods'      => ['PUT'],
    'requirements' => ['ticketId' => '\\d+', 'issueId' => '\\d+'],
]);

$collection->create('agent_jira_ticket_issues_list', [
    'path'         => '/jira/ticket/{ticketId}/issue',
    'controller'   => 'AgentBundle:Jira:issues',
    'methods'      => ['GET'],
    'requirements' => ['ticketId' => '\\d+'],
]);

$collection->create('agent_jira_ticket_issue_comments', [
    'path'         => '/jira/ticket/{ticketId}/issue/{issueId}/comments',
    'controller'   => 'AgentBundle:Jira:addComment',
    'methods'      => ['POST'],
    'requirements' => ['ticketId' => '\\d+', 'issueId' => '\\d+'],
]);

$collection->create('agent_jira_ticket_issue_link', [
    'path'         => '/jira/ticket/{ticketId}/issue/{issueId}/link',
    'controller'   => 'AgentBundle:Jira:link',
    'methods'      => ['POST'],
    'requirements' => ['ticketId' => '\\d+', 'issueId' => '\\d+'],
]);

$collection->create('agent_jira_ticket_issue_unlink', [
    'path'         => '/jira/ticket/{ticketId}/issue/{issueId}/link',
    'controller'   => 'AgentBundle:Jira:unlink',
    'methods'      => ['DELETE'],
    'requirements' => ['ticketId' => '\\d+', 'issueId' => '\\d+'],
]);

$collection->create('gregwar_captcha.agent.generate_captcha', [
    'path'       => '/generate-captcha/{key}',
    'controller' => 'AgentBundle:Captcha:generate',
    'methods'    => ['GET'],
]);

$collection->create('go_to_ticket_id', [
    'path'         => '/go/ticket/{id}',
    'controller'   => 'AgentBundle:GoTo:ticketId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_ticket_ref', [
    'path'       => '/go/ticket/{ref}',
    'controller' => 'AgentBundle:GoTo:ticketRef',
    'methods'    => ['GET'],
]);

$collection->create('go_to_person_id', [
    'path'         => '/go/person/{id}',
    'controller'   => 'AgentBundle:GoTo:personId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_person_email', [
    'path'       => '/go/person/{emailAddress}',
    'controller' => 'AgentBundle:GoTo:personEmailAddress',
    'methods'    => ['GET'],
]);

$collection->create('go_to_organization_id', [
    'path'         => '/go/organization/{id}',
    'controller'   => 'AgentBundle:GoTo:organizationId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_article_id', [
    'path'         => '/go/article/{id}',
    'controller'   => 'AgentBundle:GoTo:articleId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_download_id', [
    'path'         => '/go/download/{id}',
    'controller'   => 'AgentBundle:GoTo:downloadId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_news_id', [
    'path'         => '/go/news/{id}',
    'controller'   => 'AgentBundle:GoTo:newsId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_feedback_id', [
    'path'         => '/go/feedback/{id}',
    'controller'   => 'AgentBundle:GoTo:feedbackId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_chat_id', [
    'path'         => '/go/chat/{id}',
    'controller'   => 'AgentBundle:GoTo:chatId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('go_to_topic_id', [
    'path'         => '/go/topic/{id}',
    'controller'   => 'AgentBundle:GoTo:topicId',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+'],
]);

$collection->create('reports-interface', [
    'path'       => '/reports-interface',
    'controller' => 'AgentBundle:Interface:interface',
    'defaults'   => ['interface' => 'reports'],
]);

$collection->create('reports-interface-headless-view', [
    'path'         => '/reports-interface/r/{id}/{authcode}',
    'controller'   => 'ReportsInterfaceBundle:Headless:view',
    'methods'      => ['GET'],
    'requirements' => ['id' => '\\d+', 'authcode' => '[a-zA-Z0-9]+'],
]);

$collection->create('iface_load_views', [
    'path'       => '/viewer/load-views',
    'controller' => 'AgentBundle:Interface:loadViews',
]);

return $collection;
