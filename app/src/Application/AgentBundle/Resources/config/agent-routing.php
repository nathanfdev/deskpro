<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('agent', array(
	'path'        => '/',
	'controller'  => 'AgentBundle:Main:index',
));

$collection->create('agent_savedom', array(
	'path'        => '/save-dom.json',
	'controller'  => 'AgentBundle:Misc:saveDom',
));

$collection->create('agent_requirejs_loader', array(
	'path'        => '/requirejs-loader.js',
	'controller'  => 'AgentBundle:Misc:getRequirejsLoader',
));

$collection->create('agent_apps_config_js', array(
	'path'        => '/apps-config.js',
	'controller'  => 'AgentBundle:Misc:getAppsConfig',
));

$collection->create('agent_combined_sectiondata', array(
	'path'        => '/get-combined-section-data.json',
	'controller'  => 'AgentBundle:Main:getCombinedSectionData',
));

$collection->create('agent_load_recent_tabs', array(
	'path'        => '/ui/load-recent-tabs.json',
	'controller'  => 'AgentBundle:Main:loadRecentTabs',
));

$collection->create('agent_accept_upload', array(
	'path'        => '/misc/accept-upload',
	'controller'  => 'AgentBundle:Misc:acceptTempUpload',
));

$collection->create('agent_accept_redactor_image_upload', array(
	'path'        => '/misc/accept-redactor-image-upload',
	'controller'  => 'AgentBundle:Misc:acceptRedactorImageUpload',
));

$collection->create('agent_redactor_autosave', array(
	'path'          => '/misc/redactor-autosave/{content_type}/{content_id}',
	'controller'    => 'AgentBundle:Misc:redactorAutosave',
	'requirements'  => array('content_id' => '\\d+'),
));

$collection->create('agent_submit_deskpro_feedback', array(
	'path'        => '/misc/submit-deskpro-feedback.json',
	'controller'  => 'AgentBundle:Misc:submitDeskproFeedback',
));

$collection->create('agent_parse_vcard', array(
	'path'        => '/misc/parse-vcard',
	'controller'  => 'AgentBundle:Misc:parseVCard',
));

$collection->create('agent_get_server_time', array(
	'path'        => '/misc/get-server-time',
	'controller'  => 'AgentBundle:Misc:getServerTime',
));

$collection->create('agent_parse_vcard', array(
	'path'         => '/misc/parse-vcard/{blob_id}',
	'controller'   => 'AgentBundle:Misc:parseVCard',
	'requirements' => array('blob_id' => '\\d+'),
));

$collection->create('agent_ajax_save_prefs', array(
	'path'        => '/misc/ajax-save-prefs',
	'controller'  => 'AgentBundle:Misc:ajaxSavePrefs',
));

$collection->create('agent_ajax_labels_autocomplete', array(
	'path'          => '/misc/ajax-labels/{label_type}',
	'controller'    => 'AgentBundle:Misc:ajaxLabelsAutocomplete',
	'requirements'  => array('label_type' => '[a-z]+'),
));

$collection->create('agent_interface_data_js', array(
	'path'        => '/misc/interface-data.js',
	'controller'  => 'AgentBundle:Misc:getInterfaceData',
));

$collection->create('agent_dismiss_help_message', array(
	'path'        => '/misc/dismiss-help-message/{id}',
	'controller'  => 'AgentBundle:Misc:dismissHelpMessage',
));

$collection->create('agent_set_agent_status', array(
	'path'        => '/misc/set-agent-status/{status}',
	'controller'  => 'AgentBundle:Misc:setAgentStatus',
));

$collection->create('agent_proxy', array(
	'path'        => '/misc/proxy',
	'controller'  => 'AgentBundle:Misc:proxy',
));

$collection->create('agent_load_version_notice', array(
	'path'        => '/misc/version-notices/{id}/log.html',
	'controller'  => 'AgentBundle:Main:loadVersionNotice',
));

$collection->create('agent_dismiss_version_notice', array(
	'path'        => '/misc/version-notices/{id}/dismiss.json',
	'controller'  => 'AgentBundle:Main:dismissVersionNotice',
));

$collection->create('agent_dpnews_view', array(
	'path'         => '/misc/view-dp-news/{id}',
	'controller'   => 'AgentBundle:Misc:viewDpNews',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('GET'),
));

$collection->create('agent_dpnews_dismiss', array(
	'path'         => '/misc/view-dp-news/{id}/dismiss',
	'controller'   => 'AgentBundle:Misc:dismissDpNews',
	'requirements' => array('id' => '\d+'),
	'methods'      => array('POST'),
));

$collection->create('agent_redirect_out', array(
	'path'          => '/redirect-out/{url}',
	'controller'    => 'AgentBundle:Misc:redirectExternal',
	'requirements'  => array('url' => '.+'),
));

$collection->create('agent_redirect_out_info', array(
	'path'          => '/redirect-out-info/{url}',
	'controller'    => 'AgentBundle:Misc:redirectExternalInfo',
	'requirements'  => array('url' => '.+'),
));

$collection->create('agent_user_interface_frame', array(
	'path'          => '/user-interface-frame',
	'controller'    => 'AgentBundle:Misc:userInterfaceFrame',
));

$collection->create('agent_password_confirm_code', array(
	'path'        => '/password-confirm-code.json',
	'controller'  => 'AgentBundle:Misc:getPasswordConfirmCode',
));

$collection->create('agent_quicksearch', array(
	'path'        => '/quick-search.json',
	'controller'  => 'AgentBundle:Main:quickSearch',
));

$collection->create('agent_recyclebin', array(
	'path'        => '/recycle-bin',
	'controller'  => 'AgentBundle:RecycleBin:list',
	'options'     => array('fragment_name' => 'recycle-bin', 'fragment_type' => 'list'),
));

$collection->create('agent_recyclebin_more', array(
	'path'        => '/recycle-bin/{type}/{page}',
	'controller'  => 'AgentBundle:RecycleBin:listMore',
));

$collection->create('agent_login_preload_sources', array(
	'path'        => '/login/preload-sources',
	'controller'  => 'AgentBundle:Login:preloadSources',
));

$collection->create('agent_browser_requirements', array(
	'path'        => '/browser-requirements',
	'controller'  => 'AgentBundle:Login:browserRequirements',
));

$collection->create('agent_browser_requirements_ie_compat', array(
	'path'        => '/browser-requirements/ie-compat-mode',
	'controller'  => 'AgentBundle:Login:ieCompatMode',
));

$collection->create('agent_login', array(
	'path'        => '/login',
	'controller'  => 'AgentBundle:Login:index',
));

$collection->create('agent_login_authenticate_local', array(
	'path'        => '/login/authenticate-password',
	'controller'  => 'AgentBundle:Login:authenticateLocal',
	'defaults'    => array('usersource_id' => 0),
));

$collection->create('agent_login_authenticate', array(
	'path'          => '/login/authenticate/{usersource_id}',
	'controller'    => 'AgentBundle:Login:authenticate',
	'defaults'      => array('usersource_id' => 0),
	'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('agent_login_callback', array(
	'path'          => '/login/authenticate-callback/{usersource_id}',
	'controller'    => 'AgentBundle:Login:authenticateCallback',
	'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('agent_login_adminlogin', array(
	'path'        => '/login/admin-login/{code}',
	'controller'  => 'AgentBundle:Login:authAdminLogin',
));

$collection->create('agent_send_lost', array(
	'path'        => '/login/send-lost.json',
	'controller'  => 'AgentBundle:Login:sendResetPassword',
	'defaults'    => array('_format' => 'json'),
));

$collection->create('agent_whitelist_ip', array(
	'path'        => '/whitelist-ip/{code}',
	'controller'  => 'AgentBundle:Login:whitelistIp'
));


$collection->create('agent_settings', array(
	'path'        => '/settings',
	'controller'  => 'AgentBundle:Settings:profile',
));

$collection->create('agent_settings_profile_save', array(
	'path'        => '/settings/profile/save.json',
	'controller'  => 'AgentBundle:Settings:profileSave',
));

$collection->create('agent_settings_profile_savewelcome', array(
	'path'        => '/settings/profile/save-welcome.json',
	'controller'  => 'AgentBundle:Settings:profileSaveWelcome',
));

$collection->create('agent_settings_signature', array(
	'path'        => '/settings/signature',
	'controller'  => 'AgentBundle:Settings:signature',
));

$collection->create('agent_settings_signature_save', array(
	'path'        => '/settings/signature/save.json',
	'controller'  => 'AgentBundle:Settings:signatureSave',
));

$collection->create('agent_settings_profile_updatetimezone', array(
	'path'        => '/settings/profile/update-timezone.json',
	'controller'  => 'AgentBundle:Settings:updateTimezone',
));

$collection->create('agent_settings_ticketnotif', array(
	'path'        => '/settings/ticket-notifications',
	'controller'  => 'AgentBundle:Settings:ticketNotifications',
));

$collection->create('agent_settings_ticketnotif_save', array(
	'path'        => '/settings/ticket-notifications/save.json',
	'controller'  => 'AgentBundle:Settings:ticketNotificationsSave',
));

$collection->create('agent_settings_othernotif', array(
	'path'        => '/settings/other-notifications',
	'controller'  => 'AgentBundle:Settings:otherNotifications',
));

$collection->create('agent_settings_othernotif_save', array(
	'path'        => '/settings/other-notifications/save.json',
	'controller'  => 'AgentBundle:Settings:otherNotificationsSave',
));

$collection->create('agent_settings_ticketmacros', array(
	'path'        => '/settings/ticket-macros',
	'controller'  => 'AgentBundle:Settings:ticketMacros',
));

$collection->create('agent_settings_ticketmacros_edit', array(
	'path'          => '/settings/ticket-macros/{macro_id}/edit',
	'controller'    => 'AgentBundle:Settings:ticketMacroEdit',
	'requirements'  => array('macro_id' => '\\d+'),
));

$collection->create('agent_settings_ticketmacros_edit_save', array(
	'path'          => '/settings/ticket-macros/{macro_id}/save',
	'controller'    => 'AgentBundle:Settings:ticketMacroEditSave',
	'requirements'  => array('macro_id' => '\\d+'),
));

$collection->create('agent_settings_ticketmacros_new', array(
	'path'        => '/settings/ticket-macros/new',
	'controller'  => 'AgentBundle:Settings:ticketMacroEdit',
	'defaults'    => array('macro_id' => 0),
));

$collection->create('agent_settings_ticketmacros_del', array(
	'path'          => '/settings/ticket-macros/{macro_id}/delete',
	'controller'    => 'AgentBundle:Settings:ticketMacroDelete',
	'requirements'  => array('macro_id' => '\\d+'),
));

$collection->create('agent_settings_ticketfilters', array(
	'path'        => '/settings/ticket-filters',
	'controller'  => 'AgentBundle:Settings:ticketFilters',
));

$collection->create('agent_settings_ticketfilters_edit', array(
	'path'          => '/settings/ticket-filters/{filter_id}/edit',
	'controller'    => 'AgentBundle:Settings:ticketFilterEdit',
	'requirements'  => array('filter_id' => '\\d+'),
));

$collection->create('agent_settings_ticketfilters_edit_save', array(
	'path'          => '/settings/ticket-filters/{filter_id}/edit/save',
	'controller'    => 'AgentBundle:Settings:ticketFilterEditSave',
	'requirements'  => array('filter_id' => '\\d+'),
));

$collection->create('agent_settings_ticketfilters_del', array(
	'path'          => '/settings/ticket-filters/{filter_id}/delete',
	'controller'    => 'AgentBundle:Settings:ticketFilterDelete',
	'requirements'  => array('filter_id' => '\\d+'),
));

$collection->create('agent_settings_ticketfilters_new', array(
	'path'        => '/settings/ticket-filters/new-filter',
	'controller'  => 'AgentBundle:Settings:ticketFilterEdit',
	'defaults'    => array('filter_id' => 0),
));

$collection->create('agent_settings_ticketslas', array(
	'path'        => '/settings/ticket-slas',
	'controller'  => 'AgentBundle:Settings:ticketSlas',
));

$collection->create('agent_people_validate_email', array(
	'path'          => '/people/validate-email/{id}/{security_token}',
	'controller'    => 'AgentBundle:Person:validateEmailAddress',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('agent_people_view', array(
	'path'          => '/people/{person_id}',
	'controller'    => 'AgentBundle:Person:view',
	'requirements'  => array('person_id' => '\\d+'),
	'options'       => array('fragment_name' => 'p'),
));

$collection->create('agent_people_view_basicjson', array(
	'path'          => '/people/{person_id}/basic.json',
	'controller'    => 'AgentBundle:Person:getBasicInfo',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_people_viewsession', array(
	'path'          => '/people/session/{session_id}',
	'controller'    => 'AgentBundle:Person:viewSession',
	'requirements'  => array('session_id' => '\\d+'),
));

$collection->create('agent_people_validate_list', array(
	'path'        => '/people/validate/list',
	'controller'  => 'AgentBundle:PeopleSearch:validateList',
));

$collection->create('agent_people_validate_approve', array(
	'path'        => '/people/validate/approve',
	'controller'  => 'AgentBundle:PeopleSearch:validateApprove',
));

$collection->create('agent_people_validate_delete', array(
	'path'        => '/people/validate/delete',
	'controller'  => 'AgentBundle:PeopleSearch:validateDelete',
));

$collection->create('agent_people_new', array(
	'path'        => '/people/new',
	'controller'  => 'AgentBundle:Person:newPerson',
));

$collection->create('agent_people_new_save', array(
	'path'        => '/people/new/save',
	'controller'  => 'AgentBundle:Person:newPersonSave',
));

$collection->create('agent_people_ajaxsave', array(
	'path'          => '/people/{person_id}/ajax-save',
	'controller'    => 'AgentBundle:Person:ajaxSave',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_people_savecontactdata', array(
	'path'        => '/people/{person_id}/save-contact-data.json',
	'controller'  => 'AgentBundle:Person:saveContactData',
));

$collection->create('agent_people_unban_email', array(
	'path'        => '/people/{person_id}/unban-email/{email_id}.json',
	'controller'  => 'AgentBundle:Person:unbanEmail',
));

$collection->create('agent_people_merge_overlay', array(
	'path'          => '/people/{person_id}/merge-overlay/{other_person_id}',
	'controller'    => 'AgentBundle:Person:mergeOverlay',
	'requirements'  => array('person_id' => '\\d+', 'other_person_id' => '\\d+'),
));

$collection->create('agent_people_merge', array(
	'path'          => '/people/{person_id}/merge/{other_person_id}',
	'controller'    => 'AgentBundle:Person:merge',
	'requirements'  => array('person_id' => '\\d+', 'other_person_id' => '\\d+'),
));

$collection->create('agent_people_delete', array(
	'path'        => '/people/{person_id}/delete/{security_token}',
	'controller'  => 'AgentBundle:Person:deletePerson',
));

$collection->create('agent_people_login_as', array(
	'path'        => '/people/{person_id}/login-as',
	'controller'  => 'AgentBundle:Person:loginAs',
));

$collection->create('agent_people_changepicoverlay', array(
	'path'          => '/people/{person_id}/change-picture-overlay',
	'controller'    => 'AgentBundle:Person:changePictureOverlay',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_people_ajaxsave_note', array(
	'path'          => '/people/{person_id}/ajax-save-note',
	'controller'    => 'AgentBundle:Person:ajaxSaveNote',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_people_ajaxsave_file', array(
	'path'          => '/people/{person_id}/ajax-save-file',
	'controller'    => 'AgentBundle:Person:ajaxSaveFile',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_people_ajaxsave_organization', array(
	'path'          => '/people/{person_id}/ajax-save-organization',
	'controller'    => 'AgentBundle:Person:ajaxSaveOrganization',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_person_get_tickets', array(
	'path'          => '/person/{person_id}/tickets',
	'controller'    => 'AgentBundle:Person:getPersonTickets',
	'requirements'  => array('person_id' => '\\d+' ),
));

$collection->create('agent_person_ajax_labels_save', array(
	'path'          => '/person/{person_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Person:ajaxSaveLabels',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_person_ajaxsavecustomfields', array(
	'path'          => '/person/{person_id}/ajax-save-custom-fields',
	'controller'    => 'AgentBundle:Person:ajaxSaveCustomFields',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_peoplesearch_usergroup', array(
	'path'        => '/people-search/usergroup/{id}',
	'controller'  => 'AgentBundle:PeopleSearch:showUsergroup',
	'options'     => array('fragment_name' => 'usergroup', 'fragment_type' => 'list'),
));

$collection->create('agent_peoplesearch_organization', array(
	'path'        => '/people-search/organization/{id}',
	'controller'  => 'AgentBundle:PeopleSearch:showOrganizationMembers',
	'options'     => array(
		'fragment_name'  => 'organization-members',
		'fragment_type'  => 'list',
	),
));

$collection->create('agent_peoplesearch_customfilter', array(
	'path'        => '/people-search/search/{letter}',
	'controller'  => 'AgentBundle:PeopleSearch:search',
	'defaults'    => array('letter' => '*'),
	'options'     => array('fragment_name' => 'people', 'fragment_type' => 'list'),
));

$collection->create('agent_peoplesearch_getpage', array(
	'path'        => '/people-search/get-page',
	'controller'  => 'AgentBundle:PeopleSearch:getPeoplePage',
));

$collection->create('agent_peoplesearch_performquick', array(
	'path'        => '/people-search/search-quick',
	'controller'  => 'AgentBundle:PeopleSearch:performQuickSearch',
));

$collection->create('agent_peoplesearch_quickfind', array(
	'path'        => '/people-search/quick-find',
	'controller'  => 'AgentBundle:PeopleSearch:quickFind',
));

$collection->create('agent_peoplesearch_quickfind_search', array(
	'path'        => '/people-search/quick-find-search.json',
	'controller'  => 'AgentBundle:PeopleSearch:quickFindSearch',
));

$collection->create('agent_peoplesearch_getsectiondata', array(
	'path'        => '/people/get-section-data.json',
	'controller'  => 'AgentBundle:PeopleSearch:getSectionData',
));

$collection->create('agent_peoplesearch_getsectiondata_reloadcounts', array(
	'path'        => '/people/get-section-data/reload-counts.json',
	'controller'  => 'AgentBundle:PeopleSearch:reloadCounts',
));

$collection->create('agent_peoplesearch_reload_label_sectiondata', array(
	'path'        => '/people/get-section-data/labels.json',
	'controller'  => 'AgentBundle:PeopleSearch:reloadLabelData',
));

$collection->create('agent_org_view', array(
	'path'          => '/organizations/{organization_id}',
	'controller'    => 'AgentBundle:Organization:view',
	'requirements'  => array('organization_id' => '\\d+'),
	'options'       => array('fragment_name' => 'o'),
));

$collection->create('agent_org_new', array(
	'path'        => '/organizations/new',
	'controller'  => 'AgentBundle:Organization:newOrganization',
));

$collection->create('agent_org_new_save', array(
	'path'        => '/organizations/new/save',
	'controller'  => 'AgentBundle:Organization:newOrganizationSave',
));

$collection->create('agent_org_ajaxsave', array(
	'path'          => '/organizations/{organization_id}/ajax-save',
	'controller'    => 'AgentBundle:Organization:ajaxSave',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_domain_assign', array(
	'path'          => '/organizations/{organization_id}/assign-domain',
	'controller'    => 'AgentBundle:Organization:assignDomain',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_domain_unassign', array(
	'path'          => '/organizations/{organization_id}/unassign-domain',
	'controller'    => 'AgentBundle:Organization:unassignDomain',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_domain_moveusers', array(
	'path'          => '/organizations/{organization_id}/domain/move-users',
	'controller'    => 'AgentBundle:Organization:moveDomainUsers',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_domain_moveusers_exist', array(
	'path'          => '/organizations/{organization_id}/domain/reassign-users',
	'controller'    => 'AgentBundle:Organization:moveTakenDomainUsers',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_save_member_pos', array(
	'path'          => '/organizations/{organization_id}/save-member-pos/{person_id}',
	'controller'    => 'AgentBundle:Organization:savePosition',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_save_member_manager', array(
	'path'          => '/organizations/{organization_id}/save-member-manager/{person_id}',
	'controller'    => 'AgentBundle:Organization:saveManager',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_savecontactdata', array(
	'path'          => '/organizations/{organization_id}/save-contact-data.json',
	'controller'    => 'AgentBundle:Organization:saveContactData',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('agent_org_delete', array(
	'path'        => '/organizations/{organization_id}/delete/{security_token}',
	'controller'  => 'AgentBundle:Organization:deleteOrganization',
));

$collection->create('agent_org_ajaxsave_note', array(
	'path'          => '/organizations/{organization_id}/ajax-save-note',
	'controller'    => 'AgentBundle:Organization:ajaxSaveNote',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_ajaxsave_file', array(
	'path'          => '/organizations/{organization_id}/ajax-save-file',
	'controller'    => 'AgentBundle:Organization:ajaxSaveFile',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_ajax_labels_save', array(
	'path'          => '/organizations/{organization_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Organization:ajaxSaveLabels',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_ajaxsavecustomfields', array(
	'path'          => '/organizations/{organization_id}/ajax-save-custom-fields',
	'controller'    => 'AgentBundle:Organization:ajaxSaveCustomFields',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_org_changepicoverlay', array(
	'path'          => '/organizations/{organization_id}/change-picture-overlay',
	'controller'    => 'AgentBundle:Organization:changePictureOverlay',
	'requirements'  => array('organization_id' => '\\d+'),
));

$collection->create('agent_orgsearch_getpage', array(
	'path'        => '/organization-search/get-page',
	'controller'  => 'AgentBundle:OrganizationSearch:getOrgPage',
));

$collection->create('agent_orgsearch_customfilter', array(
	'path'        => '/organization-search/search',
	'controller'  => 'AgentBundle:OrganizationSearch:search',
	'options'     => array('fragment_name' => 'orgs', 'fragment_type' => 'list'),
));

$collection->create('agent_orgsearch_quicknamesearch', array(
	'path'        => '/organization-search/quick-name-search.json',
	'controller'  => 'AgentBundle:OrganizationSearch:performQuickNameSearch',
));

$collection->create('agent_orgsearch_namelookup', array(
	'path'        => '/organization-search/name-lookup.json',
	'controller'  => 'AgentBundle:OrganizationSearch:checkName',
));

$collection->create('agent_ticketsearch_getsectiondata', array(
	'path'        => '/ticket-search/get-section-data.json',
	'controller'  => 'AgentBundle:TicketSearch:getSectionData',
));

$collection->create('agent_ticketsearch_getsection_reloadarchive', array(
	'path'        => '/ticket-search/get-section-data/reload-archive-section',
	'controller'  => 'AgentBundle:TicketSearch:reloadArchiveSection',
));

$collection->create('agent_ticketsearch_refreshsectiondata', array(
	'path'        => '/ticket-search/refresh-section-data/{section}.json',
	'controller'  => 'AgentBundle:TicketSearch:refreshSectionData',
));

$collection->create('agent_ticketsearch_getlabelssection', array(
	'path'        => '/ticket-search/get-section/labels',
	'controller'  => 'AgentBundle:TicketSearch:getLabelsSection',
));

$collection->create('agent_ticketsearch_getfiltercounts', array(
	'path'        => '/ticket-search/get-filter-counts.json',
	'controller'  => 'AgentBundle:TicketSearch:getFilterCounts',
));

$collection->create('agent_ticketsearch_getslacounts', array(
	'path'        => '/ticket-search/get-sla-counts.json',
	'controller'  => 'AgentBundle:TicketSearch:getSlaCounts',
));

$collection->create('agent_ticketsearch_grouptickets', array(
	'path'        => '/ticket-search/group-tickets.json',
	'controller'  => 'AgentBundle:TicketSearch:groupTickets',
));

$collection->create('agent_ticketsearch_getpage', array(
	'path'        => '/ticket-search/get-page',
	'controller'  => 'AgentBundle:TicketSearch:getTicketPage',
));

$collection->create('agent_ticketsearch_getflaggedsectiondata', array(
	'path'        => '/tickets/get-flagged-section-data.json',
	'controller'  => 'AgentBundle:TicketSearch:getFlaggedSectionData',
));

$collection->create('agent_ticketsearch_runcustomfilter', array(
	'path'        => '/ticket-search/custom-filter/run',
	'controller'  => 'AgentBundle:TicketSearch:runCustomFilter',
));

$collection->create('agent_ticketsearch_quicksearch', array(
	'path'        => '/ticket-search/quick-search',
	'controller'  => 'AgentBundle:TicketSearch:quickSearch',
));

$collection->create('agent_ticketsearch_singleticketrow', array(
	'path'          => '/ticket-search/single-ticket-row/{content_type}/{content_id}',
	'controller'    => 'AgentBundle:TicketSearch:getSingleTicketRow',
	'requirements'  => array('content_id' => '\\d+'),
));

$collection->create('agent_ticketsearch_getticketrows', array(
	'path'       => '/ticket-search/ticket-rows.json',
	'controller' => 'AgentBundle:TicketSearch:getTicketRows',
));

$collection->create('agent_ticketsearch_runfilter', array(
	'path'          => '/ticket-search/filter/{filter_id}',
	'controller'    => 'AgentBundle:TicketSearch:runFilter',
	'requirements'  => array('filter_id' => '\\d+'),
	'options'       => array('fragment_name' => 'filter', 'fragment_type' => 'list'),
));

$collection->create('agent_ticketsearch_getsubgroupcounts', array(
	'path'       => '/ticket-search/subgroup-counts.json',
	'controller' => 'AgentBundle:TicketSearch:getSubgroupCounts',
));

$collection->create('agent_ticketsearch_runnamedfilter', array(
	'path'        => '/ticket-search/filter/{filter_name}',
	'controller'  => 'AgentBundle:TicketSearch:runNamedFilter',
	'options'     => array('fragment_name' => 'inbox', 'fragment_type' => 'list'),
));

$collection->create('agent_ticketsearch_runsla', array(
	'path'          => '/ticket-search/sla/{sla_id}/{sla_status}',
	'controller'    => 'AgentBundle:TicketSearch:runSla',
	'defaults'      => array('sla_status' => ''),
	'requirements'  => array('sla_id' => '\\d+'),
	'options'       => array('fragment_name' => 'sla', 'fragment_type' => 'list'),
));

$collection->create('agent_ticketsearch_ajax_get_macro', array(
	'path'        => '/ticket-search/ajax-get-macro',
	'controller'  => 'AgentBundle:TicketSearch:ajaxGetMacro',
));

$collection->create('agent_ticketsearch_ajax_get_macro_actions', array(
	'path'        => '/ticket-search/ajax-get-macro-actions',
	'controller'  => 'AgentBundle:TicketSearch:ajaxGetMacroActions',
));

$collection->create('agent_ticketsearch_ajax_save_actions', array(
	'path'        => '/ticket-search/ajax-save-actions',
	'controller'  => 'AgentBundle:TicketSearch:ajaxSaveActions',
));

$collection->create('agent_ticketsearch_ajax_delete_tickets', array(
	'path'        => '/ticket-search/ajax-delete-tickets',
	'controller'  => 'AgentBundle:TicketSearch:ajaxDeleteTickets',
));

$collection->create('agent_ticketsearch_ajax_release_locks', array(
	'path'        => '/ticket-search/ajax-release-locks',
	'controller'  => 'AgentBundle:TicketSearch:ajaxReleaseLocks',
));

$collection->create('agent_ticket_new', array(
	'path'        => '/tickets/new',
	'controller'  => 'AgentBundle:Ticket:new',
	'options'     => array('fragment_name' => 'nt'),
));

$collection->create('agent_ticket_new_save', array(
	'path'        => '/tickets/new/save',
	'controller'  => 'AgentBundle:Ticket:newSave',
));

$collection->create('agent_ticket_new_getpersonrow', array(
	'path'        => '/tickets/new/get-person-row/{person_id}',
	'controller'  => 'AgentBundle:Ticket:newticketGetPersonRow',
));

$collection->create('agent_ticket_getmessagetpl', array(
	'path'        => '/tickets/get-message-template/{id}.json',
	'controller'  => 'AgentBundle:Ticket:getTicketMessageTemplate',
));

$collection->create('agent_ticket_update_drafts', array(
	'path'        => '/tickets/update-drafts',
	'controller'  => 'AgentBundle:Ticket:updateDrafts',
));

$collection->create('agent_ticket_getmessagetext', array(
	'path'        => '/tickets/messages/{message_id}/get-message-text.json',
	'controller'  => 'AgentBundle:Ticket:ajaxGetMessageText',
));

$collection->create('agent_ticket_getfullmessage', array(
	'path'        => '/tickets/messages/{message_id}/get-full-message.json',
	'controller'  => 'AgentBundle:Ticket:ajaxGetFullMessage',
));

$collection->create('agent_ticket_savemessagetext', array(
	'path'        => '/tickets/messages/{message_id}/save-message-text.json',
	'controller'  => 'AgentBundle:Ticket:ajaxSaveMessageText',
));

$collection->create('agent_ticket_setmessagenote', array(
	'path'        => '/tickets/messages/{message_id}/set-message-note.json',
	'controller'  => 'AgentBundle:Ticket:ajaxSetNote',
));

$collection->create('agent_ticket_message_attachments', array(
	'path'        => '/tickets/messages/{message_id}/attachments',
	'controller'  => 'AgentBundle:Ticket:getMessageAttachments',
));

$collection->create('agent_ticket_message_attachment_delete', array(
	'path'        => '/tickets/messages/{message_id}/attachments/{attachment_id}/delete',
	'controller'  => 'AgentBundle:Ticket:deleteMessageAttachment',
	'methods'     => array('POST'),
));

$collection->create('agent_ticket_message_delete', array(
	'path'        => '/tickets/messages/{message_id}/delete',
	'controller'  => 'AgentBundle:Ticket:deleteMessage',
	'methods'     => array('POST'),
));

$collection->create('agent_ticket_view', array(
	'path'          => '/tickets/{ticket_id}',
	'controller'    => 'AgentBundle:Ticket:view',
	'requirements'  => array('ticket_id' => '\\d+'),
	'options'       => array('fragment_name' => 't'),
));

$collection->create('agent_ticket_loadlogs', array(
	'path'          => '/tickets/{ticket_id}/load-logs',
	'controller'    => 'AgentBundle:Ticket:loadTicketLogs',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_loadattachlist', array(
	'path'          => '/tickets/{ticket_id}/load-attach-list',
	'controller'    => 'AgentBundle:Ticket:loadAttachList',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_download_debug_report', array(
	'path'          => '/tickets/{ticket_id}/download-debug-report',
	'controller'    => 'AgentBundle:Ticket:downloadTicketDebug',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_messagepage', array(
	'path'          => '/tickets/{ticket_id}/message-page/{page}',
	'controller'    => 'AgentBundle:Ticket:getMessagePage',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_doupdate', array(
	'path'          => '/tickets/{ticket_id}/update-views.json',
	'controller'    => 'AgentBundle:Ticket:updateViews',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_lock', array(
	'path'          => '/tickets/{ticket_id}/lock-ticket.json',
	'controller'    => 'AgentBundle:Ticket:lockTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_unlock', array(
	'path'          => '/tickets/{ticket_id}/unlock-ticket.json',
	'controller'    => 'AgentBundle:Ticket:unlockTicket',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_release_lock', array(
	'path'          => '/tickets/{ticket_id}/release-lock.json',
	'controller'    => 'AgentBundle:Ticket:releaseLock',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_split', array(
	'path'          => '/tickets/{ticket_id}/split/{message_id}',
	'controller'    => 'AgentBundle:Ticket:split',
	'defaults'      => array('message_id' => 0),
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_split_save', array(
	'path'          => '/tickets/{ticket_id}/split-save',
	'controller'    => 'AgentBundle:Ticket:splitSave',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_merge_overlay', array(
	'path'          => '/tickets/{ticket_id}/merge-overlay/{other_ticket_id}',
	'controller'    => 'AgentBundle:Ticket:mergeOverlay',
	'requirements'  => array('ticket_id' => '\\d+', 'other_ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_fwd_overlay', array(
	'path'          => '/tickets/{ticket_id}/forward/{message_id}',
	'controller'    => 'AgentBundle:Ticket:forwardOverlay',
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
));

$collection->create('agent_ticket_fwd_send', array(
	'path'          => '/tickets/{ticket_id}/forward/{message_id}/send',
	'controller'    => 'AgentBundle:Ticket:forwardSend',
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('agent_ticket_merge', array(
	'path'          => '/tickets/{ticket_id}/merge/{other_ticket_id}',
	'controller'    => 'AgentBundle:Ticket:merge',
	'requirements'  => array('ticket_id' => '\\d+', 'other_ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_changeuser_overlay', array(
	'path'          => '/tickets/{ticket_id}/change-user-overlay',
	'controller'    => 'AgentBundle:Ticket:changeUserOverlay',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_changeuser_overlay_preview', array(
	'path'          => '/tickets/{ticket_id}/change-user-overlay/preview/{new_person_id}',
	'controller'    => 'AgentBundle:Ticket:changeUserOverlayPreview',
	'requirements'  => array('ticket_id' => '\\d+', 'new_person_id' => '\\d+'),
));

$collection->create('agent_ticket_changeuser', array(
	'path'          => '/tickets/{ticket_id}/change-user',
	'controller'    => 'AgentBundle:Ticket:changeUser',
	'requirements'  => array('ticket_id' => '\\d+', 'new_person_id' => '\\d+'),
));

$collection->create('agent_ticket_ajaxsavecustomfields', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-custom-fields',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveCustomFields',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajaxsavereply', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-reply',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveReply',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajaxsavesubject', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-subject.json',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveSubject',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajaxchangeuseremail', array(
	'path'          => '/tickets/{ticket_id}/ajax-change-email.json',
	'controller'    => 'AgentBundle:Ticket:ajaxChangeUserEmail',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajaxsaveoptions', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-options',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveOptions',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajaxsaveflagged', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-flagged',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveFlagged',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_addpart', array(
	'path'        => '/tickets/{ticket_id}/add-part',
	'controller'  => 'AgentBundle:Ticket:addParticipant',
));

$collection->create('agent_ticket_set_agent_parts', array(
	'path'        => '/tickets/{ticket_id}/set-agent-parts.json',
	'controller'  => 'AgentBundle:Ticket:setAgentParticipants',
));

$collection->create('agent_ticket_delpart', array(
	'path'        => '/tickets/{ticket_id}/remove-part.json',
	'controller'  => 'AgentBundle:Ticket:removeParticipant',
));

$collection->create('agent_ticket_ajaxtab_releated_content', array(
	'path'          => '/tickets/{ticket_id}/ajax-tab-related-content',
	'controller'    => 'AgentBundle:Ticket:ajaxTabRelatedContent',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajax_labels_save', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveLabels',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajax_get_macro', array(
	'path'          => '/tickets/{ticket_id}/ajax-get-macro',
	'controller'    => 'AgentBundle:Ticket:ajaxGetMacro',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_ajax_apply_macro', array(
	'path'          => '/tickets/{ticket_id}/{macro_id}/apply-macro.json',
	'controller'    => 'AgentBundle:Ticket:applyMacro',
	'requirements'  => array('ticket_id' => '\\d+', 'macro_id' => '\\d+'),
));

$collection->create('agent_ticket_ajax_save_actions', array(
	'path'          => '/tickets/{ticket_id}/ajax-save-actions',
	'controller'    => 'AgentBundle:Ticket:ajaxSaveActions',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_message_raw', array(
	'path'          => '/tickets/{ticket_id}/message-details/{message_id}/view-raw',
	'controller'    => 'AgentBundle:Ticket:viewRawMessage',
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
));

$collection->create('agent_ticket_message_window', array(
	'path'          => '/tickets/{ticket_id}/message-details/{message_id}/window/{type}',
	'controller'    => 'AgentBundle:Ticket:viewMessageWindow',
	'defaults'      => array('type' => 'normal'),
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
));

$collection->create('agent_ticket_message_ajax_getquote', array(
	'path'          => '/tickets/{ticket_id}/message-details/{message_id}/ajax-get-quote',
	'controller'    => 'AgentBundle:Ticket:ajaxGetMessageQuote',
	'requirements'  => array('ticket_id' => '\\d+', 'message_id' => '\\d+'),
));

$collection->create('agent_ticket_saveagentparts', array(
	'path'          => '/ticket/{ticket_id}/save-agent-parts',
	'controller'    => 'AgentBundle:Ticket:saveAgentParts',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_addcharge', array(
	'path'          => '/ticket/{ticket_id}/add-charge',
	'controller'    => 'AgentBundle:Ticket:addCharge',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_editcharge', array(
	'path'          => '/ticket/{ticket_id}/edit-charge/{charge_id}',
	'controller'    => 'AgentBundle:Ticket:editCharge',
	'requirements'  => array('ticket_id' => '\\d+', 'charge_id' => '\\d+'),
));

$collection->create('agent_ticket_chargedelete', array(
	'path'          => '/ticket/{ticket_id}/charge/{charge_id}/delete/{security_token}',
	'controller'    => 'AgentBundle:Ticket:deleteCharge',
	'requirements'  => array('ticket_id' => '\\d+', 'charge_id' => '\\d+'),
));

$collection->create('agent_ticket_addsla', array(
	'path'          => '/ticket/{ticket_id}/add-sla',
	'controller'    => 'AgentBundle:Ticket:addSla',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_sladelete', array(
	'path'          => '/ticket/{ticket_id}/sla/{sla_id}/delete/{security_token}',
	'controller'    => 'AgentBundle:Ticket:deleteSla',
	'requirements'  => array('ticket_id' => '\\d+', 'sla_id' => '\\d+'),
));

$collection->create('agent_ticket_delete', array(
	'path'          => '/tickets/{ticket_id}/delete',
	'controller'    => 'AgentBundle:Ticket:delete',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_spam', array(
	'path'          => '/tickets/{ticket_id}/spam',
	'controller'    => 'AgentBundle:Ticket:spam',
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_link_existing_overlay', array(
	'path' => '/tickets/{ticket_id}/link-overlay',
	'controller' => 'AgentBundle:Ticket:linkExistingOverlay',
	'requirements' => array('ticket_id' => '\\d+'),
));

$collection->create('agent_ticket_link_existing', array(
	'path' => '/tickets/{ticket_id}/link/{linked_ticket_id}',
	'controller' => 'AgentBundle:Ticket:linkExisting',
	'methods'     => array('POST'),
));

$collection->create('agent_ticket_unlink', array(
		'path' => '/tickets/{ticket_id}/unlink-ticket',
		'controller' => 'AgentBundle:Ticket:unlinkTicket',
		'methods'     => array('POST'),
	));

$collection->create('agent_twitter_new', array(
	'path'        => '/twitter/new',
	'controller'  => 'AgentBundle:Twitter:newTweet',
));

$collection->create('agent_twitter_new_save', array(
	'path'        => '/twitter/new/save',
	'controller'  => 'AgentBundle:Twitter:newTweetSave',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_mine_list', array(
	'path'          => '/twitter/mine/{account_id}/{group}/{group_value}',
	'controller'    => 'AgentBundle:TwitterStatus:listMine',
	'defaults'      => array('group' => '', 'group_value' => ''),
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-own', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_team_list', array(
	'path'          => '/twitter/team/{account_id}/{group}/{group_value}',
	'controller'    => 'AgentBundle:TwitterStatus:listTeam',
	'defaults'      => array('group' => '', 'group_value' => ''),
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-team', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_unassigned_list', array(
	'path'          => '/twitter/unassigned/{account_id}/{group}/{group_value}',
	'controller'    => 'AgentBundle:TwitterStatus:listUnassigned',
	'defaults'      => array('group' => '', 'group_value' => ''),
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-unassigned', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_all_list', array(
	'path'          => '/twitter/all/{account_id}/{group}/{group_value}',
	'controller'    => 'AgentBundle:TwitterStatus:listAll',
	'defaults'      => array('group' => '', 'group_value' => ''),
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-all', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_sent_list', array(
	'path'          => '/twitter/sent/{account_id}/{group}/{group_value}',
	'controller'    => 'AgentBundle:TwitterStatus:listSent',
	'defaults'      => array('group' => '', 'group_value' => ''),
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-sent', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_timeline_list', array(
	'path'          => '/twitter/timeline/{account_id}/{group}/{group_value}',
	'controller'    => 'AgentBundle:TwitterStatus:listTimeline',
	'defaults'      => array('group' => '', 'group_value' => ''),
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-timeline', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_followers_list', array(
	'path'          => '/twitter/followers/{account_id}',
	'controller'    => 'AgentBundle:TwitterUser:listFollowers',
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-followers', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_followers_list_new', array(
	'path'          => '/twitter/followers/{account_id}/new',
	'controller'    => 'AgentBundle:TwitterUser:listNewFollowers',
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array(
		'fragment_name'  => 'tw-newfollowers',
		'fragment_type'  => 'list',
	),
));

$collection->create('agent_twitter_following_list', array(
	'path'          => '/twitter/following/{account_id}',
	'controller'    => 'AgentBundle:TwitterUser:listFollowing',
	'requirements'  => array('account_id' => '\\d+'),
	'options'       => array('fragment_name' => 'tw-following', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_status_ajaxmasssave', array(
	'path'        => '/twitter/status/ajax-mass-save.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxMassSave',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_note', array(
	'path'        => '/twitter/status/ajax-note.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveNote',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_retweet', array(
	'path'        => '/twitter/status/ajax-retweet.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveRetweet',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_unretweet', array(
	'path'        => '/twitter/status/ajax-unretweet.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveUnretweet',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_reply', array(
	'path'        => '/twitter/status/ajax-reply.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveReply',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_archive', array(
	'path'        => '/twitter/status/ajax-archive.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveArchive',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_delete', array(
	'path'        => '/twitter/status/ajax-delete.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveDelete',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_edit', array(
	'path'        => '/twitter/status/ajax-edit',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveEdit',
));

$collection->create('agent_twitter_status_ajaxsave_favorite', array(
	'path'        => '/twitter/status/ajax-favorite.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveFavorite',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_ajaxsave_assign', array(
	'path'        => '/twitter/status/ajax-assign.json',
	'controller'  => 'AgentBundle:TwitterStatus:ajaxSaveAssign',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_status_tweet_overlay', array(
	'path'        => '/twitter/status/tweet-overlay',
	'controller'  => 'AgentBundle:TwitterStatus:tweetOverlay',
));

$collection->create('agent_twitter_user', array(
	'path'          => '/twitter/user/{user_id}',
	'controller'    => 'AgentBundle:TwitterUser:view',
	'requirements'  => array('user_id' => '\\d+'),
	'options'       => array('fragment_name' => 'twitter'),
));

$collection->create('agent_twitter_user_statuses', array(
	'path'          => '/twitter/user/{user_id}/statuses',
	'controller'    => 'AgentBundle:TwitterUser:viewUserStatuses',
	'requirements'  => array('user_id' => '\\d+'),
));

$collection->create('agent_twitter_user_following', array(
	'path'          => '/twitter/user/{user_id}/following',
	'controller'    => 'AgentBundle:TwitterUser:viewUserFollowing',
	'requirements'  => array('user_id' => '\\d+'),
));

$collection->create('agent_twitter_user_followers', array(
	'path'          => '/twitter/user/{user_id}/followers',
	'controller'    => 'AgentBundle:TwitterUser:viewUserFollowers',
	'requirements'  => array('user_id' => '\\d+'),
));

$collection->create('agent_twitter_user_find', array(
	'path'        => '/twitter/user/find',
	'controller'  => 'AgentBundle:TwitterUser:find',
));

$collection->create('agent_twitter_user_message_overlay', array(
	'path'          => '/twitter/user/{user_id}/message-overlay',
	'controller'    => 'AgentBundle:TwitterUser:messageOverlay',
	'requirements'  => array('user_id' => '\\d+'),
));

$collection->create('agent_twitter_user_ajaxsave_follow', array(
	'path'        => '/twitter/user/ajax-follow.json',
	'controller'  => 'AgentBundle:TwitterUser:ajaxSaveFollow',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_user_ajaxsave_unfollow', array(
	'path'        => '/twitter/user/ajax-unfollow.json',
	'controller'  => 'AgentBundle:TwitterUser:ajaxSaveUnfollow',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_user_ajaxsave_message', array(
	'path'        => '/twitter/user/ajax-message.json',
	'controller'  => 'AgentBundle:TwitterUser:ajaxSaveMessage',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_user_ajaxsave_archive', array(
	'path'        => '/twitter/user/ajax-archive.json',
	'controller'  => 'AgentBundle:TwitterUser:ajaxSaveArchive',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_user_ajaxsave_person', array(
	'path'        => '/twitter/user/ajax-person.json',
	'controller'  => 'AgentBundle:TwitterUser:ajaxSavePerson',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_user_ajaxsave_organization', array(
	'path'        => '/twitter/user/ajax-organization.json',
	'controller'  => 'AgentBundle:TwitterUser:ajaxSaveOrganization',
	'methods'     => array('POST'),
));

$collection->create('agent_twitter_getsectiondata', array(
	'path'        => '/twitter/get-section-data.json',
	'controller'  => 'AgentBundle:Twitter:getSectionData',
));

$collection->create('agent_twitter_updategrouping', array(
	'path'        => '/twitter/update-grouping.json',
	'controller'  => 'AgentBundle:Twitter:updateGrouping',
));

$collection->create('agent_twitter_run_search', array(
	'path'          => '/twitter/{account_id}/search/{search_id}',
	'controller'    => 'AgentBundle:Twitter:runSearch',
	'requirements'  => array('account_id' => '\\d+', 'search_id' => '\\d+'),
	'options'       => array('fragment_name' => 'searches', 'fragment_type' => 'list'),
));

$collection->create('agent_twitter_search_delete', array(
	'path'          => '/twitter/{account_id}/search/delete/{security_token}',
	'controller'    => 'AgentBundle:Twitter:deleteSearch',
	'requirements'  => array('account_id' => '\\d+'),
));

$collection->create('agent_twitter_new_search', array(
	'path'          => '/twitter/{account_id}/search/new',
	'controller'    => 'AgentBundle:Twitter:newSearch',
	'requirements'  => array('account_id' => '\\d+'),
));

$collection->create('agent_task_new', array(
	'path'        => '/tasks/new',
	'controller'  => 'AgentBundle:Task:new',
	'options'     => array('fragment_name' => 'nt'),
));

$collection->create('agent_task_save', array(
	'path'        => '/tasks/save',
	'controller'  => 'AgentBundle:Task:create',
	'methods'     => array('POST'),
));

$collection->create('agent_task_delete', array(
	'path'          => '/tasks/{task_id}/delete',
	'controller'    => 'AgentBundle:Task:deleteTask',
	'requirements'  => array('task_id' => '\\d+'),
));

$collection->create('agent_tasksearch_getsectiondata', array(
	'path'        => '/tasks/get-section-data.json',
	'controller'  => 'AgentBundle:Task:getSectionData',
));

$collection->create('agent_task_list', array(
	'path'        => '/tasks/list/{search_type}/{search_category}',
	'controller'  => 'AgentBundle:Task:taskList',
	'defaults'    => array('search_type' => NULL, 'search_category' => NULL),
	'options'     => array('fragment_name' => 'tasks', 'fragment_type' => 'list'),
));

$collection->create('agent_task_ajax_labels_save', array(
	'path'          => '/tasks/{task_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Task:ajaxSaveLabels',
	'requirements'  => array('task_id' => '\\d+'),
));

$collection->create('agent_task_ajaxsave_comment', array(
	'path'          => '/tasks/{task_id}/ajax-save-comment',
	'controller'    => 'AgentBundle:Task:ajaxSaveComment',
	'requirements'  => array('task_id' => '\\d+', 'person_id' => '\\d+'),
));

$collection->create('agent_task_ajaxsave', array(
	'path'        => '/tasks/{task_id}/ajax-save',
	'controller'  => 'AgentBundle:Task:ajaxSave',
));

$collection->create('agent_task_ics_all_tasks', array(
	'path'         => '/tasks/{id}-{authcode}/all.ics',
	'controller'   => 'AgentBundle:Task:iCal',
	'defaults'     => array('filter' => 'all'),
	'requirements' => array('authcode' => '.*', 'id' => '^\\d+$' )
));

$collection->create('agent_task_ics_assigned_tasks', array(
	'path'         => '/tasks/{id}-{authcode}/assigned.ics',
	'controller'   => 'AgentBundle:Task:iCal',
	'defaults'     => array('filter' => 'assigned'),
	'requirements' => array('authcode' => '.*', 'id' => '^\\d+$')
));

$collection->create('agent_task_ics_delegated_tasks', array(
	'path'         => '/tasks/{id}-{authcode}/delegated.ics',
	'controller'   => 'AgentBundle:Task:iCal',
	'defaults'     => array('filter' => 'delegated'),
	'requirements' => array('authcode' => '.*', 'id' => '^\\d+$')
));

$collection->create('agent_dealearch_getsectiondata', array(
	'path'        => '/deal/get-section-data.json',
	'controller'  => 'AgentBundle:Deal:getSectionData',
));

$collection->create('agent_deal_list', array(
	'path'          => '/deals/list/{owner_type}/{deal_status}/{deal_type_id}',
	'controller'    => 'AgentBundle:Deal:dealList',
	'defaults'      => array(
		'owner_type'    => NULL,
		'deal_status'   => NULL,
		'deal_type_id'  => NULL,
	),
	'requirements'  => array('deal_type_id' => '\\d+'),
));

$collection->create('agent_deal_view', array(
	'path'          => '/deal/{deal_id}',
	'controller'    => 'AgentBundle:Deal:view',
	'requirements'  => array('deal_id' => '\\d+'),
	'options'       => array('fragment_name' => 'd'),
));

$collection->create('agent_deal_ajaxsave_note', array(
	'path'          => '/deal/{deal_id}/ajax-save-note',
	'controller'    => 'AgentBundle:Deal:ajaxSaveNote',
	'requirements'  => array('deal_id' => '\\d+'),
));

$collection->create('agent_deal_ajax_labels_save', array(
	'path'          => '/deal/{deal_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Deal:ajaxSaveLabels',
	'requirements'  => array('deal_id' => '\\d+'),
));

$collection->create('agent_deal_ajaxsavecustomfields', array(
	'path'          => '/deal/{deal_id}/ajax-save-custom-fields',
	'controller'    => 'AgentBundle:Deal:ajaxSaveCustomFields',
	'requirements'  => array('deal_id' => '\\d+'),
));

$collection->create('agent_deal_set_agent_parts', array(
	'path'        => '/deals/{deal_id}/{agent_id}/set-agent-parts.json',
	'controller'  => 'AgentBundle:Deal:setAgentParticipants',
));

$collection->create('agent_deal_ajaxsave', array(
	'path'          => '/deals/{deal_id}/ajax-save',
	'controller'    => 'AgentBundle:Deal:ajaxSave',
	'requirements'  => array('deal_id' => '\\d+'),
));

$collection->create('agent_deal_new', array(
	'path'        => '/deals/new',
	'controller'  => 'AgentBundle:Deal:new',
	'options'     => array('fragment_name' => 'nt'),
));

$collection->create('agent_deal_new_save', array(
	'path'        => '/deals/new/save',
	'controller'  => 'AgentBundle:Deal:newSave',
));

$collection->create('agent_deal_new_getpersonrow', array(
	'path'        => '/deals/new/get-person-row/{person_id}',
	'controller'  => 'AgentBundle:Deal:newdealGetPersonRow',
));

$collection->create('agent_deal_new_getorganizationrow', array(
	'path'        => '/deals/new/get-organization-row/{org_id}',
	'controller'  => 'AgentBundle:Deal:newdealGetOrganizationRow',
));

$collection->create('agent_deal_create_setpersonrow', array(
	'path'        => '/deals/new/create-person-row/{person_id}',
	'controller'  => 'AgentBundle:Deal:newdealCreatePersonRow',
));

$collection->create('agent_deal_new_setpersonrow', array(
	'path'        => '/deals/new/set-person-row/{person_id}',
	'controller'  => 'AgentBundle:Deal:newdealSetPersonRow',
));

$collection->create('agent_deal_new_setorganizationrow', array(
	'path'        => '/deals/new/set-organization-row/{org_id}',
	'controller'  => 'AgentBundle:Deal:newdealSetOrganizationRow',
));

$collection->create('agent_deal_create_setorganizationrow', array(
	'path'        => '/deals/new/create-organization-row/{org_id}',
	'controller'  => 'AgentBundle:Deal:newdealCreateOrganizationRow',
));

$collection->create('agent_publish_getsectiondata', array(
	'path'        => '/publish/get-section-data.json',
	'controller'  => 'AgentBundle:Publish:getSectionData',
));

$collection->create('agent_publish_ratingwhovoted', array(
	'path'        => '/publish/rating-who-voted/{object_type}/{object_id}',
	'controller'  => 'AgentBundle:Publish:ratingWhoVoted',
));

$collection->create('agent_publish_whoviewed', array(
	'path'        => '/publish/who-viewed/{object_type}/{object_id}/{view_action}',
	'controller'  => 'AgentBundle:Publish:whoViewed',
	'defaults'    => array('view_action' => 1),
));

$collection->create('agent_publish_save_stickysearchwords', array(
	'path'        => '/publish/save-sticky-search-words/{type}/{content_id}',
	'controller'  => 'AgentBundle:Publish:saveStickySearchWords',
));

$collection->create('agent_publish_validatingcontent', array(
	'path'        => '/publish/content/validating',
	'controller'  => 'AgentBundle:Publish:listValidatingContent',
	'options'     => array(
		'fragment_type'  => 'list',
		'fragment_name'  => 'validating_content',
	),
));

$collection->create('agent_feedback_validatingcontent', array(
	'path'        => '/feedback/content/validating',
	'controller'  => 'AgentBundle:Publish:listValidatingFeedbackContent',
	'options'     => array('fragment_type' => 'list', 'fragment_name' => 'fb_content'),
));

$collection->create('agent_feedback_validatingcomments', array(
	'path'        => '/feedback/comments/validating',
	'controller'  => 'AgentBundle:Publish:listValidatingFeedbackComments',
	'options'     => array('fragment_type' => 'list', 'fragment_name' => 'fb_comments'),
));

$collection->create('agent_publish_validatingcontent_approve', array(
	'path'        => '/publish/content/approve/{type}/{content_id}.json',
	'controller'  => 'AgentBundle:Publish:approveContent',
));

$collection->create('agent_publish_validatingcontent_disapprove', array(
	'path'        => '/publish/content/disapprove/{type}/{content_id}.json',
	'controller'  => 'AgentBundle:Publish:disapproveContent',
));

$collection->create('agent_publish_validatingcontent_mass', array(
	'path'        => '/publish/content/validating-mass-actions/{action}',
	'controller'  => 'AgentBundle:Publish:validatingMassActions',
));

$collection->create('agent_publish_validatingcontent_next', array(
	'path'        => '/publish/content/get-next-validating/{type}/{content_id}.json',
	'controller'  => 'AgentBundle:Publish:nextValidatingContent',
	'options'     => array('fragment_name' => 'pending', 'fragment_type' => 'list'),
));

$collection->create('agent_publish_listcomments', array(
	'path'        => '/publish/comments/list/{type}',
	'controller'  => 'AgentBundle:Publish:listComments',
	'options'     => array('fragment_type' => 'list', 'fragment_name' => 'list_comments'),
));

$collection->create('agent_publish_validatingcomments', array(
	'path'        => '/publish/comments/validating',
	'controller'  => 'AgentBundle:Publish:listValidatingComments',
	'options'     => array(
		'fragment_type'  => 'list',
		'fragment_name'  => 'validating_comments',
	),
));

$collection->create('agent_publish_approve_comment', array(
	'path'        => '/publish/comments/approve/{typename}/{comment_id}',
	'controller'  => 'AgentBundle:Publish:approveComment',
));

$collection->create('agent_publish_delete_comment', array(
	'path'        => '/publish/comments/delete/{typename}/{comment_id}',
	'controller'  => 'AgentBundle:Publish:deleteComment',
));

$collection->create('agent_publish_comment_info', array(
	'path'        => '/publish/comments/info/{typename}/{comment_id}',
	'controller'  => 'AgentBundle:Publish:commentInfo',
));

$collection->create('agent_publish_comment_save', array(
	'path'        => '/publish/comments/save-comment/{typename}/{comment_id}',
	'controller'  => 'AgentBundle:Publish:saveComment',
));

$collection->create('agent_public_comment_newticketinfo', array(
	'path'        => '/publish/comments/new-ticket-info/{typename}/{comment_id}.json',
	'controller'  => 'AgentBundle:Publish:getNewTicketCommentInfo',
));

$collection->create('agent_publish_validatingcomments_mass', array(
	'path'        => '/publish/comments/validating-mass-actions/{action}',
	'controller'  => 'AgentBundle:Publish:validatingCommentsMassActions',
));

$collection->create('agent_publish_savecats', array(
	'path'        => '/publish/save-categories/{type}',
	'controller'  => 'AgentBundle:Publish:saveCategories',
));

$collection->create('agent_publish_cats_adddel', array(
	'path'        => '/publish/categories/{type}/delete-category',
	'controller'  => 'AgentBundle:Publish:deleteCategory',
));

$collection->create('agent_publish_cats_addcat', array(
	'path'        => '/publish/categories/{type}/add-category',
	'controller'  => 'AgentBundle:Publish:addCategory',
));

$collection->create('agent_publish_cats_updateorders', array(
	'path'        => '/publish/categories/{type}/update-orders',
	'controller'  => 'AgentBundle:Publish:updateCategoryOrders',
));

$collection->create('agent_publish_cats_updatetitles', array(
	'path'        => '/publish/categories/{type}/update-titles',
	'controller'  => 'AgentBundle:Publish:updateCategoryTitles',
));

$collection->create('agent_publish_cats_update', array(
	'path'        => '/publish/categories/{type}/update/{category_id}',
	'controller'  => 'AgentBundle:Publish:updateCategory',
));

$collection->create('agent_publish_cats_updatestructure', array(
	'path'        => '/publish/categories/{type}/update-structure',
	'controller'  => 'AgentBundle:Publish:updateCategoryStructure',
));

$collection->create('agent_publish_cats_newform', array(
	'path'        => '/publish/categories/{type}/new-form',
	'controller'  => 'AgentBundle:Publish:addCategoryForm',
));

$collection->create('agent_publish_cats_newform_save', array(
	'path'        => '/publish/categories/{type}/new-form/save',
	'controller'  => 'AgentBundle:Publish:addCategoryFormSave',
));

$collection->create('agent_public_drafts', array(
	'path'        => '/publish/drafts/{type}',
	'controller'  => 'AgentBundle:Publish:listDrafts',
	'options'     => array('fragment_name' => 'drafts', 'fragment_type' => 'list'),
));

$collection->create('agent_public_drafts_mass', array(
	'path'        => '/publish/drafts/mass-actions/{action}',
	'controller'  => 'AgentBundle:Publish:draftsMassActions',
));

$collection->create('agent_publish_search', array(
	'path'        => '/publish/search',
	'controller'  => 'AgentBundle:Publish:search',
));

$collection->create('agent_kb_newarticle_save', array(
	'path'        => '/kb/article/new/save',
	'controller'  => 'AgentBundle:Kb:newArticleSave',
));

$collection->create('agent_kb_newarticle', array(
	'path'        => '/kb/article/new',
	'controller'  => 'AgentBundle:Kb:newArticle',
));

$collection->create('agent_kb_article', array(
	'path'          => '/kb/article/{article_id}',
	'controller'    => 'AgentBundle:Kb:viewArticle',
	'requirements'  => array('article_id' => '\\d+'),
	'options'       => array('fragment_name' => 'a'),
));

$collection->create('agent_kb_ajaxsavecustomfields', array(
	'path'          => '/kb/article/{article_id}/ajax-save-custom-fields',
	'controller'    => 'AgentBundle:Kb:ajaxSaveCustomFields',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_kb_article_info', array(
	'path'          => '/kb/article/{article_id}/info',
	'controller'    => 'AgentBundle:Kb:articleInfo',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_kb_article_revisionstab', array(
	'path'          => '/kb/article/{article_id}/view-revisions',
	'controller'    => 'AgentBundle:Kb:viewRevisions',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_kb_article_ajaxsave', array(
	'path'          => '/kb/article/{article_id}/ajax-save',
	'controller'    => 'AgentBundle:Kb:ajaxSave',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_kb_ajax_save_comment', array(
	'path'          => '/kb/article/{article_id}/ajax-save-comment',
	'controller'    => 'AgentBundle:Kb:ajaxSaveComment',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_kb_ajax_labels_save', array(
	'path'          => '/kb/article/{article_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Kb:ajaxSaveLabels',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_kb_comparerevs', array(
	'path'        => '/kb/compare-revs/{rev_old_id}/{rev_new_id}',
	'controller'  => 'AgentBundle:Kb:compareRevisions',
));

$collection->create('agent_kb_newpending', array(
	'path'        => '/kb/pending-articles/new',
	'controller'  => 'AgentBundle:Kb:newPendingArticle',
));

$collection->create('agent_kb_pending_remove', array(
	'path'        => '/kb/pending-articles/{pending_article_id}/remove',
	'controller'  => 'AgentBundle:Kb:removePendingArticle',
));

$collection->create('agent_kb_pending_info', array(
	'path'        => '/kb/pending-articles/{pending_article_id}/info',
	'controller'  => 'AgentBundle:Kb:pendingArticleInfo',
));

$collection->create('agent_kb_pending', array(
	'path'        => '/kb/pending-articles',
	'controller'  => 'AgentBundle:Kb:listPendingArticles',
	'options'     => array('fragment_name' => 'pending', 'fragment_type' => 'list'),
));

$collection->create('agent_kb_pending_massactions', array(
	'path'        => '/kb/pending-articles/mass-actions/{action}',
	'controller'  => 'AgentBundle:Kb:pendingArticlesMassActions',
));

$collection->create('agent_kb_list', array(
	'path'        => '/kb/list/{category_id}',
	'controller'  => 'AgentBundle:Kb:list',
	'defaults'    => array('category_id' => '0'),
	'options'     => array('fragment_name' => 'knowledgebase', 'fragment_type' => 'list'),
));

$collection->create('agent_kb_cat', array(
	'path'        => '/kb/category/{category_id}',
	'controller'  => 'AgentBundle:Kb:list',
));

$collection->create('agent_kb_mass_save', array(
	'path'        => '/kb/article/ajax-mass-save',
	'controller'  => 'AgentBundle:Kb:ajaxMassSave',
));

$collection->create('agent_glossary_newword_json', array(
	'path'        => '/glossary/new-word.json',
	'controller'  => 'AgentBundle:Glossary:glossaryNewWordJson',
));

$collection->create('agent_glossary_word_json', array(
	'path'        => '/glossary/{word_id}.json',
	'controller'  => 'AgentBundle:Glossary:glossaryWordJson',
));

$collection->create('agent_glossary_saveword_json', array(
	'path'        => '/glossary/{word_id}/edit.json',
	'controller'  => 'AgentBundle:Glossary:glossarySaveWordJson',
));

$collection->create('agent_glossary_delword_json', array(
	'path'        => '/glossary/{word_id}/delete.json',
	'controller'  => 'AgentBundle:Glossary:glossaryDeleteWordJson',
));

$collection->create('agent_glossary_word_tip', array(
	'path'        => '/glossary/{word}/tip',
	'controller'  => 'AgentBundle:Glossary:tip',
));

$collection->create('agent_news_list', array(
	'path'        => '/news/list/{category_id}',
	'controller'  => 'AgentBundle:News:list',
	'defaults'    => array('category_id' => '0'),
	'options'     => array('fragment_name' => 'news', 'fragment_type' => 'list'),
));

$collection->create('agent_news_view', array(
	'path'          => '/news/post/{news_id}',
	'controller'    => 'AgentBundle:News:view',
	'requirements'  => array('news_id' => '\\d+'),
	'options'       => array('fragment_name' => 'n'),
));

$collection->create('agent_news_revisionstab', array(
	'path'          => '/news/post/{news_id}/view-revisions',
	'controller'    => 'AgentBundle:News:viewRevisions',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_news_save', array(
	'path'          => '/news/post/{news_id}/ajax-save',
	'controller'    => 'AgentBundle:News:ajaxSave',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_news_ajax_labels_save', array(
	'path'          => '/news/{news_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:News:ajaxSaveLabels',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_news_ajax_save_comment', array(
	'path'          => '/news/post/{news_id}/ajax-save-comment',
	'controller'    => 'AgentBundle:News:ajaxSaveComment',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_news_new_save', array(
	'path'        => '/news/new/save',
	'controller'  => 'AgentBundle:News:newNewsSave',
));

$collection->create('agent_news_new', array(
	'path'        => '/news/new',
	'controller'  => 'AgentBundle:News:newNews',
));

$collection->create('agent_news_comparerevs', array(
	'path'        => '/news/compare-revs/{rev_old_id}/{rev_new_id}',
	'controller'  => 'AgentBundle:News:compareRevisions',
));

$collection->create('agent_downloads_list', array(
	'path'        => '/downloads/list/{category_id}',
	'controller'  => 'AgentBundle:Downloads:list',
	'defaults'    => array('category_id' => '0'),
	'options'     => array('fragment_name' => 'downloads', 'fragment_type' => 'list'),
));

$collection->create('agent_downloads_view', array(
	'path'          => '/downloads/file/{download_id}',
	'controller'    => 'AgentBundle:Downloads:view',
	'requirements'  => array('download_id' => '\\d+'),
	'options'       => array('fragment_name' => 'd'),
));

$collection->create('agent_downloads_info', array(
	'path'          => '/downloads/file/{download_id}/info',
	'controller'    => 'AgentBundle:Downloads:info',
	'requirements'  => array('download_id' => '\\d+'),
));

$collection->create('agent_kb_downloads_revisionstab', array(
	'path'          => '/downloads/file/{download_id}/view-revisions',
	'controller'    => 'AgentBundle:Downloads:viewRevisions',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('agent_downloads_save', array(
	'path'          => '/downloads/file/{download_id}/ajax-save',
	'controller'    => 'AgentBundle:Downloads:ajaxSave',
	'requirements'  => array('download_id' => '\\d+'),
));

$collection->create('agent_downloads_ajax_labels_save', array(
	'path'          => '/downloads/file/{download_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Downloads:ajaxSaveLabels',
	'requirements'  => array('download_id' => '\\d+'),
));

$collection->create('agent_downloads_ajax_save_comment', array(
	'path'          => '/downloads/file/{download_id}/ajax-save-comment',
	'controller'    => 'AgentBundle:Downloads:ajaxSaveComment',
	'requirements'  => array('download_id' => '\\d+'),
));

$collection->create('agent_downloads_new_save', array(
	'path'        => '/downloads/new/save',
	'controller'  => 'AgentBundle:Downloads:newDownloadSave',
));

$collection->create('agent_downloads_new', array(
	'path'        => '/downloads/new',
	'controller'  => 'AgentBundle:Downloads:newDownload',
));

$collection->create('agent_downloads_comparerevs', array(
	'path'        => '/downloads/compare-revs/{rev_old_id}/{rev_new_id}',
	'controller'  => 'AgentBundle:Downloads:compareRevisions',
));

$collection->create('agent_feedback_category', array(
	'path'        => '/feedback/category/{category_id}',
	'controller'  => 'AgentBundle:Feedback:categoryList',
	'options'     => array('fragment_name' => 'category', 'fragment_type' => 'list'),
));

$collection->create('agent_feedback_status', array(
	'path'        => '/feedback/status/{status}',
	'controller'  => 'AgentBundle:Feedback:statusList',
	'options'     => array('fragment_name' => 'status', 'fragment_type' => 'list'),
));

$collection->create('agent_feedback_label', array(
	'path'         => '/feedback/label/{label}',
	'controller'   => 'AgentBundle:Feedback:labelList',
	'options'      => array('fragment_name' => 'label', 'fragment_type' => 'list'),
	'requirements' => array('label' => '.*'),
));

$collection->create('agent_feedback_filter', array(
	'path'        => '/feedback/filter',
	'controller'  => 'AgentBundle:Feedback:filterList',
));

$collection->create('agent_feedback_massactions', array(
	'path'        => '/feedback/filter/mass-actions/{action}',
	'controller'  => 'AgentBundle:Feedback:massActions',
));

$collection->create('agent_feedback_getsectiondata', array(
	'path'        => '/feedback/get-section-data.json',
	'controller'  => 'AgentBundle:Feedback:getSectionData',
));

$collection->create('agent_feedback_new', array(
	'path'        => '/feedback/new',
	'controller'  => 'AgentBundle:Feedback:newFeedback',
));

$collection->create('agent_feedback_new_save', array(
	'path'        => '/feedback/new/save',
	'controller'  => 'AgentBundle:Feedback:newFeedbackSave',
));

$collection->create('agent_feedback_view', array(
	'path'        => '/feedback/view/{feedback_id}',
	'controller'  => 'AgentBundle:Feedback:view',
	'options'     => array('fragment_name' => 'i'),
));

$collection->create('agent_feedback_comparerevs', array(
	'path'        => '/feedback/compare-revs/{rev_old_id}/{rev_new_id}',
	'controller'  => 'AgentBundle:Feedback:compareRevisions',
));

$collection->create('agent_feedback_ajaxsavecustomfields', array(
	'path'          => '/feedback/view/{feedback_id}/ajax-save-custom-fields',
	'controller'    => 'AgentBundle:Feedback:ajaxSaveCustomFields',
	'requirements'  => array('feedback_id' => '\\d+'),
));

$collection->create('agent_feedback_who_voted', array(
	'path'        => '/feedback/view/{feedback_id}/who-voted',
	'controller'  => 'AgentBundle:Feedback:whoVoted',
));

$collection->create('agent_feedback_save', array(
	'path'          => '/feedback/view/{feedback_id}/ajax-save',
	'controller'    => 'AgentBundle:Feedback:ajaxSave',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_feedback_ajax_labels_save', array(
	'path'          => '/feedback/view/{feedback_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:Feedback:ajaxSaveLabels',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_feedback_ajax_save_comment', array(
	'path'          => '/feedback/view/{feedback_id}/ajax-save-comment',
	'controller'    => 'AgentBundle:Feedback:ajaxSaveComment',
	'requirements'  => array('news_id' => '\\d+'),
));

$collection->create('agent_feedback_ajaxsavecomment', array(
	'path'        => '/feedback/view/{feedback_id}/ajax-save-comment',
	'controller'  => 'AgentBundle:Feedback:ajaxSaveComment',
));

$collection->create('agent_feedback_ajaxsaveeditables', array(
	'path'        => '/feedback/view/{feedback_id}/ajax-save-editables',
	'controller'  => 'AgentBundle:Feedback:ajaxSaveEditables',
));

$collection->create('agent_feedback_ajaxupdatecat', array(
	'path'        => '/feedback/view/{feedback_id}/ajax-update-category/{category_id}',
	'controller'  => 'AgentBundle:Feedback:ajaxUpdateCategory',
));

$collection->create('agent_feedback_ajaxupdatestatus', array(
	'path'        => '/feedback/view/{feedback_id}/ajax-update-status/{status_code}',
	'controller'  => 'AgentBundle:Feedback:ajaxUpdateStatus',
));

$collection->create('agent_feedback_merge_overlay', array(
	'path'          => '/feedback/merge-overlay/{feedback_id}/{other_feedback_id}',
	'controller'    => 'AgentBundle:Feedback:mergeOverlay',
	'requirements'  => array('feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'),
));

$collection->create('agent_feedback_merge', array(
	'path'          => '/feedback/merge/{feedback_id}/{other_feedback_id}',
	'controller'    => 'AgentBundle:Feedback:merge',
	'requirements'  => array('feedback_id' => '\\d+', 'other_feedback_id' => '\\d+'),
));

$collection->create('agent_agentchat_getonlineagents', array(
	'path'        => '/agent-chat/get-online-agents.json',
	'controller'  => 'AgentBundle:AgentChat:getOnlineAgents',
));

$collection->create('agent_agentchat_get_last_convo', array(
	'path'        => '/agent-chat/get-last-convo',
	'controller'  => 'AgentBundle:AgentChat:loadConvoMessages',
));

$collection->create('agent_agentchat_send_message', array(
	'path'        => '/agent-chat/send-message/{conversation_id}',
	'controller'  => 'AgentBundle:AgentChat:sendMessage',
));

$collection->create('agent_agentchat_send_agent_message', array(
	'path'        => '/agent-chat/send-agent-message/{convo_id}',
	'controller'  => 'AgentBundle:AgentChat:sendAgentMessage',
));

$collection->create('agent_agentchat_history', array(
	'path'        => '/agent-chat/agent-history/{agent_id}',
	'controller'  => 'AgentBundle:AgentChat:agentHistory',
));

$collection->create('agent_agentchat_history_team', array(
	'path'        => '/agent-chat/agent-history/team/{agent_team_id}',
	'controller'  => 'AgentBundle:AgentChat:agentTeamHistory',
));

$collection->create('agent_agentchat_view', array(
	'path'        => '/agent-chat/agent-transcript/{conversation_id}',
	'controller'  => 'AgentBundle:AgentChat:agentChatTranscript',
));

$collection->create('agent_agentchat_getsectiondata', array(
	'path'        => '/agent-chat/get-section-data.json',
	'controller'  => 'AgentBundle:AgentChat:getSectionData',
));

$collection->create('agent_userchat_view', array(
	'path'        => '/chat/view/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:view',
	'options'     => array('fragment_name' => 'c'),
));

$collection->create('agent_userchat_save_fields', array(
	'path'        => '/chat/{conversation_id}/save-fields',
	'controller'  => 'AgentBundle:UserChat:saveFields',
	'methods'     => array('POST'),
));

$collection->create('agent_userchat_blockuser', array(
	'path'        => '/chat/block-user/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:blockUser',
));

$collection->create('agent_userchat_unblockuser', array(
	'path'        => '/chat/unblock-user/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:unblockUser',
));

$collection->create('agent_userchat_ajax_labels_save', array(
	'path'          => '/chat/{conversation_id}/ajax-save-labels',
	'controller'    => 'AgentBundle:UserChat:ajaxSaveLabels',
	'requirements'  => array('conversation_id' => '\\d+'),
));

$collection->create('agent_userchat_open_counts', array(
	'path'        => '/chat/open-counts.json',
	'controller'  => 'AgentBundle:UserChat:getOpenCounts',
));

$collection->create('agent_userchat_filterlist_group_counts', array(
	'path'        => '/chat/group-count.json',
	'controller'  => 'AgentBundle:UserChat:getGroupByCounts',
));

$collection->create('agent_userchat_filterlist', array(
	'path'        => '/chat/filter/{filter_id}',
	'controller'  => 'AgentBundle:UserChat:filter',
	'options'     => array('fragment_name' => 'ended', 'fragment_type' => 'list'),
));

$collection->create('agent_userchat_list_new', array(
	'path'        => '/chat/list-new/{department_id}',
	'controller'  => 'AgentBundle:UserChat:listNewChats',
	'defaults'    => array('department_id' => '-1'),
	'options'     => array('fragment_name' => 'new', 'fragment_type' => 'list'),
));

$collection->create('agent_userchat_list_active', array(
	'path'        => '/chat/list-active/{agent_id}',
	'controller'  => 'AgentBundle:UserChat:listActiveChats',
	'defaults'    => array('agent_id' => '-1'),
	'options'     => array('fragment_name' => 'active', 'fragment_type' => 'list'),
));

$collection->create('agent_userchat_send_messageview', array(
	'path'        => '/chat/send-message/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:sendMessage',
));

$collection->create('agent_userchat_send_filemessage', array(
	'path'        => '/chat/send-file-message/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:sendFile',
));

$collection->create('agent_userchat_assign', array(
	'path'        => '/chat/assign/{conversation_id}/{agent_id}',
	'controller'  => 'AgentBundle:UserChat:assignChat',
));

$collection->create('agent_userchat_syncpart', array(
	'path'        => '/chat/sync-parts/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:syncParts',
));

$collection->create('agent_userchat_addpart', array(
	'path'        => '/chat/add-part/{conversation_id}/{agent_id}',
	'controller'  => 'AgentBundle:UserChat:addPart',
));

$collection->create('agent_userchat_end', array(
	'path'        => '/chat/end-chat/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:endChat',
));

$collection->create('agent_userchat_leave', array(
	'path'        => '/chat/leave/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:leaveChat',
));

$collection->create('agent_userchat_invite', array(
	'path'        => '/chat/invite/{conversation_id}/{agent_id}',
	'controller'  => 'AgentBundle:UserChat:sendInvite',
));

$collection->create('agent_userchat_changeprop', array(
	'path'        => '/chat/change-props/{conversation_id}',
	'controller'  => 'AgentBundle:UserChat:changeProperties',
));

$collection->create('agent_userchat_getsectiondata', array(
	'path'        => '/chat/get-section-data.json',
	'controller'  => 'AgentBundle:UserChat:getSectionData',
));

$collection->create('agent_usertrack_winheadertable', array(
	'path'        => '/user-track/win-header-table.html',
	'controller'  => 'AgentBundle:UserTrack:winHeaderTable',
));

$collection->create('agent_usertrack_view', array(
	'path'          => '/user-track/{visitor_id}',
	'controller'    => 'AgentBundle:UserTrack:view',
	'requirements'  => array('visitor_id' => '\\d+'),
	'methods'       => array('GET'),
));

$collection->create('agent_mediamanager', array(
	'path'        => '/media-manager',
	'controller'  => 'AgentBundle:MediaManager:window',
));

$collection->create('agent_mediamanager_upload', array(
	'path'        => '/media-manager/upload',
	'controller'  => 'AgentBundle:MediaManager:upload',
));

$collection->create('agent_mediamanager_browse', array(
	'path'        => '/media-manager/browse',
	'controller'  => 'AgentBundle:MediaManager:browse',
));

$collection->create('agent_textsnippets_widget_shell', array(
	'path'        => '/text-snippets/{typename}/widget-shell.txt',
	'controller'  => 'AgentBundle:TextSnippets:getWidgetShell',
));

$collection->create('agent_textsnippets_reloadclient', array(
	'path'        => '/text-snippets/{typename}/reload-client.json',
	'controller'  => 'AgentBundle:TextSnippets:reloadClient',
));

$collection->create('agent_textsnippets_reloadclient_batch', array(
	'path'        => '/text-snippets/{typename}/reload-client/{batch}.json',
	'controller'  => 'AgentBundle:TextSnippets:reloadClientBatch',
));

$collection->create('agent_textsnippets_filtersnippets', array(
	'path'        => '/text-snippets/{typename}/filter.json',
	'controller'  => 'AgentBundle:TextSnippets:filterSnippets',
));

$collection->create('agent_textsnippets_getsnippet', array(
	'path'        => '/text-snippets/{typename}/{id}.json',
	'controller'  => 'AgentBundle:TextSnippets:getSnippet',
));

$collection->create('agent_textsnippets_savesnippet', array(
	'path'        => '/text-snippets/{typename}/{id}/save.json',
	'controller'  => 'AgentBundle:TextSnippets:saveSnippet',
));

$collection->create('agent_textsnippets_delsnippet', array(
	'path'        => '/text-snippets/{typename}/{id}/delete.json',
	'controller'  => 'AgentBundle:TextSnippets:deleteSnippet',
));

$collection->create('agent_textsnippets_savecat', array(
	'path'        => '/text-snippets/{typename}/categories/{id}/save.json',
	'controller'  => 'AgentBundle:TextSnippets:saveCategory',
));

$collection->create('agent_textsnippets_delcat', array(
	'path'        => '/text-snippets/{typename}/categories/{id}/delete.json',
	'controller'  => 'AgentBundle:TextSnippets:deleteCategory',
));

$collection->create('agent_apps_run', array(
	'path'          => '/apps/{app_id}/{action}',
	'defaults'      => array('action' => 'default'),
	'controller'    => 'AgentBundle:Apps:run',
	'requirements'  => array('app_id' => '\\d+'),
));

$collection->create('jira_widget', array(
	'path'          => '/jira/widget/{ticket_id}',
	'controller'    => 'AgentBundle:Jira:widget',
	'defaults'		=> array('ticket_id' => '-1'),
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('jira_export', array(
	'path'          => '/jira/export/{ticket_id}',
	'controller'    => 'AgentBundle:Jira:export',
	'defaults'		=> array('ticket_id' => '-1'),
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('jira_unlink', array(
	'path'          => '/jira/unlink/{ticket_id}/{issue_id}',
	'controller'    => 'AgentBundle:Jira:unlink',
	'defaults'		=> array('ticket_id' => '-1', 'issue_id' => '-1'),
	'requirements'  => array('ticket_id' => '\\d+', 'issue_id' => '\\d+'),
));

$collection->create('jira_lookup', array(
	'path'          => '/jira/lookup',
	'controller'    => 'AgentBundle:Jira:lookup',
));

$collection->create('jira_associated_issues', array(
	'path'          => '/jira/issue/{ticket_id}',
	'controller'    => 'AgentBundle:Jira:getAssociatedIssues',
	'defaults'		=> array('ticket_id' => '-1'),
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('jira_fetchcomments', array(
	'path'          => '/jira/issue/{ticket_id}/fetchcomments',
	'controller'    => 'AgentBundle:Jira:getComments',
	'defaults'		=> array('ticket_id' => '-1'),
	'requirements'  => array('ticket_id' => '\\d+'),
));

$collection->create('jira_fetchallcomments', array(
	'path'          => '/jira/fetchcomments',
	'controller'    => 'AgentBundle:Jira:fetchAllComment',
));

$collection->create('jira_post_comment', array(
	'path'          => '/jira/{issue_id}/comment',
	'controller'    => 'AgentBundle:Jira:postComment',
	'defaults'		=> array('issue_id' => '-1'),
	'requirements'  => array('issue_id' => '\\d+'),
));

return $collection;
