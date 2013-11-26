<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;
use Application\DeskPRO\Routing\Route;

$collection = new RouteCollection();

$collection->create('admin_test', array(
	'path'        => '/test',
	'controller'  => 'AdminBundle:Test:index',
));

$collection->create('admin_submit_deskpro_feedback', array(
	'path'        => '/submit-deskpro-feedback.json',
	'controller'  => 'AdminBundle:Main:submitDeskproFeedback',
));

$collection->create('admin_onboard_complete', array(
	'path'        => '/onboard-mark-complete/{type}/{id}.json',
	'controller'  => 'AdminBundle:Main:onboardMarkComplete',
	'methods'     => array('POST'),
));

$collection->create('admin_welcome', array(
	'path'        => '/welcome',
	'controller'  => 'AdminBundle:Settings:quickSetup',
));

$collection->create('admin_apps', array(
	'path'        => '/apps',
	'controller'  => 'AdminBundle:Settings:apps',
));

$collection->create('admin_apps_toggle', array(
	'path'        => '/apps/toggle',
	'controller'  => 'AdminBundle:Settings:appToggle',
));

$collection->create('admin_change_picture', array(
	'path'        => '/misc/change-picture',
	'controller'  => 'AdminBundle:Main:changePicture',
));

$collection->create('admin_change_picture_save', array(
	'path'        => '/misc/change-picture/save',
	'controller'  => 'AdminBundle:Main:changePictureSave',
	'methods'     => array('POST'),
));

$collection->create('admin_networkcheck', array(
	'path'        => '/misc/network',
	'controller'  => 'AdminBundle:Settings:quickSetup',
));

$collection->create('admin_check_task_queue', array(
	'path'        => '/misc/check-task/{task_queue_id}',
	'controller'  => 'AdminBundle:Main:checkTaskQueue',
));

$collection->create('admin_check_task_queue_group', array(
	'path'        => '/misc/check-task/group/{task_group}',
	'controller'  => 'AdminBundle:Main:checkTaskQueueGroup',
));

$collection->create('admin_quick_person_search', array(
	'path'        => '/misc/quick-person-search',
	'controller'  => 'AdminBundle:Main:quickPersonSearch',
));

$collection->create('admin_quick_organization_search', array(
	'path'        => '/misc/quick-organization-search',
	'controller'  => 'AdminBundle:Main:quickOrganizationSearch',
));

$collection->create('admin_skip_setup_todo', array(
	'path'        => '/misc/skip-setup-todo',
	'controller'  => 'AdminBundle:Main:skipSetupStep',
));

$collection->create('admin_old', array(
	'path'        => '/old',
	'controller'  => 'AdminBundle:Main:index',
));

$collection->create('admin_dash_versioninfo', array(
	'path'        => '/dashboard/load-version-info.html',
	'controller'  => 'AdminBundle:Main:dashVersionInfo',
));

$collection->create('admin_dash_versionnotice', array(
	'path'        => '/dashboard/load-version-notice.html',
	'controller'  => 'AdminBundle:Main:dashVersionNotice',
));

$collection->create('admin_upgrade', array(
	'path'        => '/upgrade',
	'controller'  => 'AdminBundle:Upgrade:start',
));

$collection->create('admin_upgrade_abort', array(
	'path'        => '/upgrade/abort',
	'controller'  => 'AdminBundle:Upgrade:stop',
));

$collection->create('admin_upgrade_watch', array(
	'path'        => '/upgrade/watch',
	'controller'  => 'AdminBundle:Upgrade:watch',
));

$collection->create('admin_upgrade_watch_checkstarted', array(
	'path'        => '/upgrade/watch/check-started.json',
	'controller'  => 'AdminBundle:Upgrade:checkStarted',
));

$collection->create('admin_license_reqdemo', array(
	'path'        => '/license/generate-demo',
	'controller'  => 'AdminBundle:License:requestDemo',
));

$collection->create('admin_license_input_save', array(
	'path'        => '/license/input/save',
	'controller'  => 'AdminBundle:License:saveNewLicense',
	'methods'     => array('POST'),
));

$collection->create('admin_license_keyfile', array(
	'path'        => '/license/download/deskpro-license-sign.key',
	'controller'  => 'AdminBundle:License:keyFile',
));

$collection->create('admin_tickets_fields', array(
	'path'        => '/tickets/fields',
	'controller'  => 'AdminBundle:TicketProperties:list',
));

$collection->create('admin_tickets_editor_reset', array(
	'path'        => '/tickets/editor/reset-all/{security_token}',
	'controller'  => 'AdminBundle:TicketProperties:resetEditor',
));

$collection->create('admin_tickets_editor', array(
	'path'          => '/tickets/editor/{department_id}/{section}',
	'controller'    => 'AdminBundle:TicketProperties:editor',
	'defaults'      => array('department_id' => 0, 		'section' => 'create'),
	'requirements'  => array('department_id' => '\\d+'),
));

$collection->create('admin_tickets_editor_toggleper', array(
	'path'        => '/tickets/editor/toggle-per-department',
	'controller'  => 'AdminBundle:TicketProperties:togglePerDepartment',
));

$collection->create('admin_tickets_editor_dep_init', array(
	'path'        => '/tickets/editor/{department_id}/{section}/init',
	'controller'  => 'AdminBundle:TicketProperties:initEditor',
	'defaults'    => array('section' => 'create'),
));

$collection->create('admin_tickets_editor_dep_revert', array(
	'path'        => '/tickets/editor/{department_id}/{section}/revert',
	'controller'  => 'AdminBundle:TicketProperties:revertEditor',
	'defaults'    => array('section' => 'create'),
));

$collection->create('admin_tickets_editor_dep', array(
	'path'        => '/tickets/editor/{department_id}/{section}',
	'controller'  => 'AdminBundle:TicketProperties:editor',
	'defaults'    => array('section' => 'create'),
));

$collection->create('admin_tickets_editor_dep_save', array(
	'path'        => '/tickets/editor/{department_id}/{section}/save',
	'controller'  => 'AdminBundle:TicketProperties:saveEditor',
	'methods'     => array('POST'),
));

$collection->create('admin_tickets_filters', array(
	'path'        => '/tickets/filters',
	'controller'  => 'AdminBundle:TicketFilters:index',
));

$collection->create('admin_tickets_filters_edit', array(
	'path'          => '/tickets/filters/{filter_id}',
	'controller'    => 'AdminBundle:TicketFilters:edit',
	'requirements'  => array('filter_id' => '\\d+'),
));

$collection->create('admin_tickets_filters_delete', array(
	'path'          => '/tickets/filters/{filter_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:TicketFilters:delete',
	'requirements'  => array('filter_id' => '\\d+'),
));

$collection->create('admin_tickets_filters_new', array(
	'path'        => '/tickets/filters/new',
	'controller'  => 'AdminBundle:TicketFilters:edit',
	'defaults'    => array('filter_id' => '0'),
));

$collection->create('admin_ticketcats', array(
	'path'        => '/tickets/categories',
	'controller'  => 'AdminBundle:TicketCategories:list',
));

$collection->create('admin_ticketcats_setdefault', array(
	'path'        => '/tickets/categories/set-default',
	'controller'  => 'AdminBundle:TicketCategories:setDefault',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketcats_toggle', array(
	'path'        => '/tickets/categories/toggle-feature/{enable}',
	'controller'  => 'AdminBundle:TicketCategories:toggleFeature',
));

$collection->create('admin_ticketcats_savenew', array(
	'path'        => '/tickets/categories/save-new',
	'controller'  => 'AdminBundle:TicketCategories:saveNew',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketcats_savetitle', array(
	'path'        => '/tickets/categories/save-title',
	'controller'  => 'AdminBundle:TicketCategories:saveTitle',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketcats_updateorders', array(
	'path'        => '/tickets/categories/update-orders',
	'controller'  => 'AdminBundle:TicketCategories:updateOrders',
));

$collection->create('admin_ticketcats_del', array(
	'path'          => '/tickets/categories/{category_id}/delete',
	'controller'    => 'AdminBundle:TicketCategories:delete',
	'requirements'  => array('category_id' => '\\d+'),
));

$collection->create('admin_ticketcats_dodel', array(
	'path'          => '/tickets/categories/{category_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:TicketCategories:doDelete',
	'requirements'  => array(
		'category_id'     => '\\d+',
		'security_token'  => '[a-zA-Z0-9\\-]+',
	),
));

$collection->create('admin_ticketpris', array(
	'path'        => '/tickets/priorities',
	'controller'  => 'AdminBundle:TicketPriorities:list',
));

$collection->create('admin_ticketpris_toggle', array(
	'path'        => '/tickets/priorities/toggle-feature/{enable}',
	'controller'  => 'AdminBundle:TicketPriorities:toggleFeature',
));

$collection->create('admin_ticketpris_savenew', array(
	'path'        => '/tickets/priorities/save-new',
	'controller'  => 'AdminBundle:TicketPriorities:saveNew',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketpris_setdefault', array(
	'path'        => '/tickets/priorities/set-default',
	'controller'  => 'AdminBundle:TicketPriorities:setDefault',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketpris_savetitle', array(
	'path'        => '/tickets/priorities/save-title',
	'controller'  => 'AdminBundle:TicketPriorities:saveTitle',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketpris_del', array(
	'path'          => '/tickets/priorities/{priority_id}/delete',
	'controller'    => 'AdminBundle:TicketPriorities:delete',
	'requirements'  => array('priority_id' => '\\d+'),
));

$collection->create('admin_ticketpris_dodel', array(
	'path'          => '/tickets/priorities/{priority_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:TicketPriorities:doDelete',
	'requirements'  => array(
		'priority_id'     => '\\d+',
		'security_token'  => '[a-zA-Z0-9\\-]+',
	),
));

$collection->create('admin_ticketworks', array(
	'path'        => '/tickets/workflows',
	'controller'  => 'AdminBundle:TicketWorkflows:list',
));

$collection->create('admin_ticketworks_setdefault', array(
	'path'        => '/tickets/workflows/set-default',
	'controller'  => 'AdminBundle:TicketWorkflows:setDefault',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketworks_toggle', array(
	'path'        => '/tickets/workflows/toggle-feature/{enable}',
	'controller'  => 'AdminBundle:TicketWorkflows:toggleFeature',
));

$collection->create('admin_ticketworks_savenew', array(
	'path'        => '/tickets/workflows/save-new',
	'controller'  => 'AdminBundle:TicketWorkflows:saveNew',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketworks_savetitle', array(
	'path'        => '/tickets/workflows/save-title',
	'controller'  => 'AdminBundle:TicketWorkflows:saveTitle',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketworks_del', array(
	'path'          => '/tickets/workflows/{workflow_id}/delete',
	'controller'    => 'AdminBundle:TicketWorkflows:delete',
	'requirements'  => array('workflow_id' => '\\d+'),
));

$collection->create('admin_ticketworks_dodel', array(
	'path'          => '/tickets/workflows/{workflow_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:TicketWorkflows:doDelete',
	'requirements'  => array(
		'workflow_id'     => '\\d+',
		'security_token'  => '[a-zA-Z0-9\\-]+',
	),
));

$collection->create('admin_ticketworks_updateorders', array(
	'path'        => '/tickets/workflows/update-orders',
	'controller'  => 'AdminBundle:TicketWorkflows:updateOrders',
));

$collection->create('admin_tickets_slas', array(
	'path'        => '/tickets/slas',
	'controller'  => 'AdminBundle:TicketSlas:list',
));

$collection->create('admin_tickets_slas_new', array(
	'path'        => '/tickets/slas/new',
	'controller'  => 'AdminBundle:TicketSlas:edit',
	'defaults'    => array('sla_id' => 0),
));

$collection->create('admin_tickets_sla_edit', array(
	'path'          => '/tickets/slas/{sla_id}/edit',
	'controller'    => 'AdminBundle:TicketSlas:edit',
	'requirements'  => array('sla_id' => '\\d+'),
));

$collection->create('admin_tickets_sla_delete', array(
	'path'          => '/tickets/slas/{sla_id}/delete',
	'controller'    => 'AdminBundle:TicketSlas:delete',
	'requirements'  => array('sla_id' => '\\d+'),
));

$collection->create('admin_accept_upload', array(
	'path'        => '/misc/accept-upload',
	'controller'  => 'AdminBundle:Main:acceptTempUpload',
));

$collection->create('admin_website_embeds', array(
	'path'        => '/website-embeds',
	'controller'  => 'AdminBundle:Portal:widgets',
));

$collection->create('admin_portal_uploadfavicon', array(
	'path'        => '/portal/upload-favicon',
	'controller'  => 'AdminBundle:Portal:uploadFavicon',
));

$collection->create('admin_login', array(
	'path'        => '/login',
	'controller'  => 'AdminBundle:Login:index',
));

$collection->create('admin_login_authenticate_local', array(
	'path'        => '/login/authenticate-password',
	'controller'  => 'AdminBundle:Login:authenticateLocal',
	'defaults'    => array('usersource_id' => 0),
));

$collection->create('admin_login_logoupload', array(
	'path'        => '/login/accept-logo-upload',
	'controller'  => 'AdminBundle:Login:acceptLogoUpload',
));

$collection->create('admin_settings', array(
	'path'        => '/settings',
	'controller'  => 'AdminBundle:Settings:settings',
));

$collection->create('admin_settings_saveform', array(
	'path'        => '/settings/save-settings/{type}/{auth}',
	'controller'  => 'AdminBundle:Settings:settingsSaveForm',
	'methods'     => array('POST'),
));

$collection->create('admin_settings_silent_settings', array(
	'path'        => '/settings/welcome/set-settings-silent.json',
	'controller'  => 'AdminBundle:Settings:setSilentSettings',
));

$collection->create('admin_settings_cron_check', array(
	'path'        => '/settings/cron/check.json',
	'controller'  => 'AdminBundle:Settings:checkCron',
));

$collection->create('admin_settings_cron', array(
	'path'        => '/settings/cron',
	'controller'  => 'AdminBundle:Settings:cron',
));

$collection->create('admin_settings_set', array(
	'path'        => '/settings/save-setting/{setting_name}/{security_token}',
	'controller'  => 'AdminBundle:Settings:saveSingleSetting',
	'methods'     => array('POST'),
));

$collection->create('admin_settings_adv', array(
	'path'        => '/settings/advanced',
	'controller'  => 'AdminBundle:Settings:advanced',
));

$collection->create('admin_settings_adv_set', array(
	'path'        => '/settings/advanced-set/{name}',
	'controller'  => 'AdminBundle:Settings:advancedSet',
));

$collection->create('admin_labels', array(
	'path'          => '/settings/labels/{label_type}',
	'controller'    => 'AdminBundle:Settings:labels',
	'requirements'  => array('label_type' => '[a-z]+'),
));

$collection->create('admin_labels_rename', array(
	'path'          => '/settings/labels/{label_type}/rename.json',
	'controller'    => 'AdminBundle:Settings:renameLabel',
	'requirements'  => array('label_type' => '[a-z]+'),
));

$collection->create('admin_labels_new', array(
	'path'          => '/settings/labels/new.json',
	'controller'    => 'AdminBundle:Settings:labelsAjaxNew',
	'requirements'  => array('label_type' => '[a-z]+'),
));

$collection->create('admin_labels_del', array(
	'path'          => '/settings/labels/{label_type}/delete.json',
	'controller'    => 'AdminBundle:Settings:labelsAjaxDelete',
	'requirements'  => array('label_type' => '[a-z]+'),
));

$collection->create('admin_userreg_options', array(
	'path'        => '/settings/user-registration',
	'controller'  => 'AdminBundle:UserReg:options',
));

$collection->create('admin_userreg_options_save', array(
	'path'        => '/settings/user-registration/save.json',
	'controller'  => 'AdminBundle:UserReg:saveOptions',
	'methods'     => array('POST'),
));

$collection->create('admin_userreg_facebook_toggle', array(
	'path'        => '/settings/user-registration/facebook/toggle',
	'controller'  => 'AdminBundle:UserReg:facebookToggle',
));

$collection->create('admin_userreg_facebook_edit', array(
	'path'        => '/settings/user-registration/facebook',
	'controller'  => 'AdminBundle:UserReg:facebookEdit',
));

$collection->create('admin_userreg_twitter_toggle', array(
	'path'        => '/settings/user-registration/twitter/toggle',
	'controller'  => 'AdminBundle:UserReg:twitterToggle',
));

$collection->create('admin_userreg_twitter_edit', array(
	'path'        => '/settings/user-registration/twitter',
	'controller'  => 'AdminBundle:UserReg:twitterEdit',
));

$collection->create('admin_userreg_google_toggle', array(
	'path'        => '/settings/user-registration/google/toggle',
	'controller'  => 'AdminBundle:UserReg:googleToggle',
));

$collection->create('admin_userreg_deskpro_source_toggle', array(
	'path'        => '/settings/user-registration/deskpro-source/toggle',
	'controller'  => 'AdminBundle:UserReg:deskproSourceToggle',
));

$collection->create('admin_userreg_usersource_choose', array(
	'path'        => '/settings/usersources/new/choose-type',
	'controller'  => 'AdminBundle:UserReg:usersourceNewChoose',
));

$collection->create('admin_userreg_usersource_edit', array(
	'path'        => '/settings/usersources/edit/{id}',
	'controller'  => 'AdminBundle:UserReg:usersourceEdit',
	'defaults'    => array('id' => '0'),
));

$collection->create('admin_userreg_usersource_test', array(
	'path'        => '/settings/usersources/test/{id}',
	'controller'  => 'AdminBundle:UserReg:usersourceTest',
));

$collection->create('admin_userreg_usersource_toggle', array(
	'path'        => '/settings/usersources/toggle/{id}',
	'controller'  => 'AdminBundle:UserReg:usersourceToggle',
));

$collection->create('admin_userreg_usersource_delete', array(
	'path'        => '/settings/usersources/delete/{id}/{security_token}',
	'controller'  => 'AdminBundle:UserReg:usersourceDelete',
));

$collection->create('admin_banning_emails', array(
	'path'        => '/banning/emails',
	'controller'  => 'AdminBundle:Banning:listEmails',
));

$collection->create('admin_banning_ips', array(
	'path'        => '/banning/ips',
	'controller'  => 'AdminBundle:Banning:listIps',
));

$collection->create('admin_banning_newip', array(
	'path'        => '/banning/ips/new',
	'controller'  => 'AdminBundle:Banning:newIpBan',
));

$collection->create('admin_banning_newemail', array(
	'path'        => '/banning/emails/new',
	'controller'  => 'AdminBundle:Banning:newEmailBan',
));

$collection->create('admin_banning_delip', array(
	'path'        => '/banning/ips/remove',
	'controller'  => 'AdminBundle:Banning:removeIpBan',
));

$collection->create('admin_banning_delemail', array(
	'path'        => '/banning/emails/remove',
	'controller'  => 'AdminBundle:Banning:removeEmailBan',
));

$collection->create('admin_agents', array(
	'path'        => '/agents',
	'controller'  => 'AdminBundle:Agents:agents',
));

$collection->create('admin_mass_add', array(
	'path'        => '/agents/mass-add-agents.json',
	'controller'  => 'AdminBundle:Agents:massAddAgents',
	'methods'     => array('POST'),
));

$collection->create('admin_agents_killsession', array(
	'path'        => '/agents/kill-session/{agent_id}',
	'controller'  => 'AdminBundle:Agents:killAgentSession',
	'methods'     => array('POST'),
));

$collection->create('admin_agents_deleted', array(
	'path'        => '/agents/deleted',
	'controller'  => 'AdminBundle:Agents:deletedAgents',
));

$collection->create('admin_agents_new', array(
	'path'        => '/agents/new',
	'controller'  => 'AdminBundle:Agents:editAgent',
	'defaults'    => array('person_id' => '0'),
));

$collection->create('admin_agents_newpre', array(
	'path'        => '/agents/new-pre',
	'controller'  => 'AdminBundle:Agents:newAgentPre',
));

$collection->create('admin_agents_remove', array(
	'path'        => '/agents/{agent_id}/remove',
	'controller'  => 'AdminBundle:Agents:removeAgent',
	'options'     => array('agent_id' => '\\d+'),
));

$collection->create('admin_agents_loginas', array(
	'path'        => '/agents/{agent_id}/login-as',
	'controller'  => 'AdminBundle:Agents:adminLoginAs',
	'options'     => array('agent_id' => '\\d+'),
));

$collection->create('admin_agents_login_logs', array(
	'path'        => '/agents/login-log/{agent_id}',
	'controller'  => 'AdminBundle:Agents:loginLogs',
	'defaults'    => array('agent_id' => '0'),
	'options'     => array('agent_id' => '\\d+'),
));

$collection->create('admin_agents_new_fromusersource', array(
	'path'        => '/agents/new-from-usersource/{usersource_id}',
	'controller'  => 'AdminBundle:Agents:newFromUsersource',
	'defaults'    => array('usersource_id' => '0'),
));

$collection->create('admin_agents_new_fromusersource_make', array(
	'path'        => '/agents/new-from-usersource/{usersource_id}/make',
	'controller'  => 'AdminBundle:Agents:newFromUsersourceMake',
	'defaults'    => array('usersource_id' => '0', '_method' => 'POST'),
));

$collection->create('admin_agents_new_fromusersource_search', array(
	'path'        => '/agents/new-from-usersource/{usersource_id}/search',
	'controller'  => 'AdminBundle:Agents:newFromUsersourceSearch',
	'defaults'    => array('usersource_id' => '0'),
));

$collection->create('admin_agents_edit', array(
	'path'          => '/agents/{person_id}/edit',
	'controller'    => 'AdminBundle:Agents:editAgent',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('admin_agents_edit_prefs', array(
	'path'          => '/agents/{person_id}/edit-prefs',
	'controller'    => 'AdminBundle:Agents:agentPrefs',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('admin_agents_edit_formvalidate', array(
	'path'          => '/agents/{person_id}/edit/validate-form.json',
	'controller'    => 'AdminBundle:Agents:quickEditFormValidate',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('admin_agents_setvacation', array(
	'path'          => '/agents/{person_id}/set-vacation-mode/{set_to}',
	'controller'    => 'AdminBundle:Agents:setVacationMode',
	'defaults'      => array('set_to' => '0'),
	'requirements'  => array('person_id' => '\\d+', 'set_to' => '(1|0)'),
));

$collection->create('admin_agents_setdeleted', array(
	'path'          => '/agents/{person_id}/set-deleted/{set_to}',
	'controller'    => 'AdminBundle:Agents:setDeleted',
	'defaults'      => array('set_to' => '0'),
	'requirements'  => array('person_id' => '\\d+', 'set_to' => '(1|0)'),
));

$collection->create('admin_agents_convertuser', array(
	'path'          => '/agents/{agent_id}/convert-user',
	'controller'    => 'AdminBundle:Agents:convertToUser',
	'requirements'  => array('agent_id' => '\\d+'),
));

$collection->create('admin_agents_getperms', array(
	'path'          => '/agents/{person_id}/get-perms.json',
	'controller'    => 'AdminBundle:Agents:getAgentPermissions',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('admin_agents_edit_save', array(
	'path'          => '/agents/{person_id}/edit/save',
	'controller'    => 'AdminBundle:Agents:editAgentSave',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_agents_teams_edit', array(
	'path'          => '/agents/teams/{team_id}/edit',
	'controller'    => 'AdminBundle:Agents:editTeam',
	'requirements'  => array('team_id' => '\\d+'),
));

$collection->create('admin_agents_teams_del', array(
	'path'          => '/agents/teams/{team_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Agents:deleteTeam',
	'requirements'  => array('team_id' => '\\d+'),
));

$collection->create('admin_agents_teams_new', array(
	'path'        => '/agents/teams/new',
	'controller'  => 'AdminBundle:Agents:editTeam',
	'defaults'    => array('team_id' => 0),
));

$collection->create('admin_agents_groups_edit', array(
	'path'          => '/agents/groups/{usergroup_id}/edit',
	'controller'    => 'AdminBundle:Agents:editGroup',
	'requirements'  => array('usergroup_id' => '\\d+'),
));

$collection->create('admin_agents_groups_del', array(
	'path'          => '/agents/groups/{usergroup_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Agents:deleteGroup',
	'requirements'  => array('usergroup_id' => '\\d+'),
));

$collection->create('admin_agents_groups_new', array(
	'path'        => '/agents/groups/new',
	'controller'  => 'AdminBundle:Agents:editGroup',
	'defaults'    => array('usergroup_id' => 0),
));

$collection->create('admin_agents_notifications', array(
	'path'        => '/agents/notifications',
	'controller'  => 'AdminBundle:Agents:notifications',
));

$collection->create('admin_agents_notifications_getagent', array(
	'path'          => '/agents/{person_id}/notifications/get-agent-options.json',
	'controller'    => 'AdminBundle:Agents:notificationsGet',
	'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('admin_agents_notifications_saveagent', array(
	'path'          => '/agents/{person_id}/notifications/save-agent-options.json',
	'controller'    => 'AdminBundle:Agents:notificationsSave',
	'requirements'  => array('person_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_login_logs', array(
	'path'        => '/login-logs',
	'controller'  => 'AdminBundle:Agents:loginLogs',
));

$collection->create('admin_templates_user', array(
	'path'        => '/templates/portal',
	'controller'  => 'AdminBundle:Templates:userList',
));

$collection->create('admin_templates_search', array(
	'path'        => '/templates/search.json',
	'controller'  => 'AdminBundle:Templates:searchTemplates',
));

$collection->create('admin_templates_email', array(
	'path'        => '/templates/email/{list_type}',
	'controller'  => 'AdminBundle:Templates:emailList',
	'defaults'    => array('list_type' => 'layout'),
));

$collection->create('admin_templates_editemail', array(
	'path'        => '/templates/email/edit/{name}',
	'controller'  => 'AdminBundle:Templates:emailEdit',
));

$collection->create('admin_templates_deletecustom', array(
	'path'        => '/templates/email/delete-custom/{name}',
	'controller'  => 'AdminBundle:Templates:deleteCustomTemplate',
));

$collection->create('admin_templates_other', array(
	'path'        => '/templates/other',
	'controller'  => 'AdminBundle:Templates:otherList',
));

$collection->create('admin_templates_createtpl', array(
	'path'        => '/templates/create-template',
	'controller'  => 'AdminBundle:Templates:createTemplate',
));

$collection->create('admin_templates_getcode', array(
	'path'        => '/templates/get-template-code',
	'controller'  => 'AdminBundle:Templates:getTemplateCode',
));

$collection->create('admin_templates_save', array(
	'path'        => '/templates/save-template.json',
	'controller'  => 'AdminBundle:Templates:saveTemplate',
	'methods'     => array('POST'),
));

$collection->create('admin_templates_revert', array(
	'path'        => '/templates/revert-template.json',
	'controller'  => 'AdminBundle:Templates:revertTemplate',
	'methods'     => array('POST'),
));

$collection->create('admin_templates_minimanager', array(
	'path'        => '/templates/mini-manager/{dirname}/{prefix}',
	'controller'  => 'AdminBundle:Templates:miniManager',
));

$collection->create('admin_templates_previewemail', array(
	'path'        => '/templates/preview-email-template/{tpl}',
	'controller'  => 'AdminBundle:Templates:previewEmailTemplate',
	'options'     => array('tpl' => '[A-Za-z:\\-_\\.]+'),
));

$collection->create('admin_langs', array(
	'path'        => '/languages',
	'controller'  => 'AdminBundle:Languages:index',
));

$collection->create('admin_langs_mass_update_tickets', array(
	'path'        => '/languages/mass-update-tickets',
	'controller'  => 'AdminBundle:Languages:massUpdateTickets',
));

$collection->create('admin_langs_mass_update_people', array(
	'path'        => '/languages/mass-update-people',
	'controller'  => 'AdminBundle:Languages:massUpdatePeople',
));

$collection->create('admin_langs_toggle_auto', array(
	'path'        => '/languages/settings/toggle-auto-install',
	'controller'  => 'AdminBundle:Languages:toggleAutoInstall',
));

$collection->create('admin_langs_editlang', array(
	'path'          => '/languages/{language_id}/edit',
	'controller'    => 'AdminBundle:Languages:editLanguage',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_dellang', array(
	'path'          => '/languages/{language_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Languages:deleteLanguage',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_install_pack', array(
	'path'        => '/languages/install-pack/{id}',
	'controller'  => 'AdminBundle:Languages:installPack',
));

$collection->create('admin_langs_newphrase', array(
	'path'        => '/languages/{language_id}/add-custom',
	'controller'  => 'AdminBundle:Languages:addCustomPhrase',
	'methods'     => array('POST'),
));

$collection->create('admin_langs_getphrasetext', array(
	'path'        => '/languages/get-phrase-text.json',
	'controller'  => 'AdminBundle:Languages:getPhraseText',
));

$collection->create('admin_langs_departments', array(
	'path'          => '/languages/{language_id}/phrases/departments/{type}',
	'controller'    => 'AdminBundle:Languages:departments',
	'requirements'  => array('language_id' => '\\d+', 'type' => '(tickets|chat)'),
));

$collection->create('admin_langs_ticketpriorities', array(
	'path'          => '/languages/{language_id}/phrases/ticket-priorities',
	'controller'    => 'AdminBundle:Languages:ticketPriorities',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_ticketworkflows', array(
	'path'          => '/languages/{language_id}/phrases/ticket-workflows',
	'controller'    => 'AdminBundle:Languages:ticketWorkflows',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_products', array(
	'path'          => '/languages/{language_id}/phrases/products',
	'controller'    => 'AdminBundle:Languages:products',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_ticketcategories', array(
	'path'          => '/languages/{language_id}/phrases/ticket-categories',
	'controller'    => 'AdminBundle:Languages:ticketCategories',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_feedback', array(
	'path'          => '/languages/{language_id}/phrases/feedback',
	'controller'    => 'AdminBundle:Languages:feedback',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_kbcats', array(
	'path'          => '/languages/{language_id}/phrases/kb-cats',
	'controller'    => 'AdminBundle:Languages:kbCats',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_customfields', array(
	'path'          => '/languages/{language_id}/phrases/fields/{field_type}',
	'controller'    => 'AdminBundle:Languages:customFields',
	'requirements'  => array('language_id' => '\\d+'),
));

$collection->create('admin_langs_editphrases', array(
	'path'          => '/languages/{language_id}/phrases/{group}',
	'controller'    => 'AdminBundle:Languages:editPhrases',
	'requirements'  => array(
		'language_id'  => '\\d+',
		'group'        => '[a-zA-Z0-9\\.\\-_]+',
	),
));

$collection->create('admin_langs_editphrases_save', array(
	'path'          => '/languages/{language_id}/phrases-save',
	'controller'    => 'AdminBundle:Languages:savePhrases',
	'requirements'  => array('language_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_langs_editphrases_savearray', array(
	'path'        => '/languages/phrases-save-array.json',
	'controller'  => 'AdminBundle:Languages:savePhraseArray',
	'methods'     => array('POST'),
));

$collection->create('admin_api_keylist', array(
	'path'        => '/api',
	'controller'  => 'AdminBundle:Api:index',
));

$collection->create('admin_api_delkey', array(
	'path'          => '/api/key/{id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Api:delKey',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_api_editkey', array(
	'path'          => '/api/key/{id}/edit',
	'controller'    => 'AdminBundle:Api:editKey',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_api_newkey', array(
	'path'        => '/api/key/new',
	'controller'  => 'AdminBundle:Api:editKey',
	'defaults'    => array('id' => 0),
));

$collection->create('admin_customdefpeople', array(
	'path'        => '/people-fields',
	'controller'  => 'AdminBundle:CustomDefPeople:index',
));

$collection->create('admin_customdefpeople_new_choosetype', array(
	'path'        => '/people-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefPeople:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdefpeople_edit', array(
	'path'          => '/people-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefPeople:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefpeople_delete', array(
	'path'          => '/people-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefPeople:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefpeople_setenabled', array(
	'path'          => '/people-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefPeople:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_features', array(
	'path'        => '/tickets/features',
	'controller'  => 'AdminBundle:TicketFeatures:index',
));

$collection->create('admin_features_work_hours', array(
	'path'        => '/tickets/features/work-hours',
	'controller'  => 'AdminBundle:TicketFeatures:workHours',
));

$collection->create('admin_features_work_hours_save', array(
	'path'        => '/tickets/features/work-hours/save',
	'controller'  => 'AdminBundle:TicketFeatures:workHoursSave',
	'methods'     => array('POST'),
));

$collection->create('admin_ticketfeatures_regensearch', array(
	'path'        => '/tickets/features/regenerate-search',
	'controller'  => 'AdminBundle:TicketFeatures:regenSearch',
));

$collection->create('admin_ticketfeatures_purgetrash', array(
	'path'        => '/tickets/features/purge-trash/{security_token}',
	'controller'  => 'AdminBundle:TicketFeatures:purgeTrash',
));

$collection->create('admin_customdeftickets', array(
	'path'        => '/ticket-fields',
	'controller'  => 'AdminBundle:CustomDefTickets:index',
));

$collection->create('admin_customdeftickets_new_choosetype', array(
	'path'        => '/ticket-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefTickets:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdeftickets_edit', array(
	'path'          => '/ticket-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefTickets:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeftickets_delete', array(
	'path'          => '/ticket-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefTickets:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeftickets_setenabled', array(
	'path'          => '/ticket-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefTickets:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_ticketwidgets', array(
	'path'        => '/tickets/widgets',
	'controller'  => 'AdminBundle:TicketWidgets:list',
));

$collection->create('admin_ticketwidgets_new_choosetype', array(
	'path'        => '/tickets/widgets/new-choose-type',
	'controller'  => 'AdminBundle:TicketWidgets:newChooseType',
));

$collection->create('admin_ticketwidgets_edit', array(
	'path'          => '/tickets/widgets/{widget_id}/edit',
	'controller'    => 'AdminBundle:TicketWidgets:edit',
	'requirements'  => array('widget_id' => '\\d+'),
));

$collection->create('admin_tickettriggers_export', array(
	'path'        => '/tickets/triggers/export',
	'controller'  => 'AdminBundle:TicketTriggers:exportTriggers',
));

$collection->create('admin_tickettriggers_export_download', array(
	'path'        => '/tickets/triggers/export/{type}',
	'controller'  => 'AdminBundle:TicketTriggers:exportTriggersDownload',
));

$collection->create('admin_tickettriggers_import', array(
	'path'        => '/tickets/triggers/process-import',
	'controller'  => 'AdminBundle:TicketTriggers:importTriggers',
	'methods'     => array('POST'),
));

$collection->create('admin_tickettriggers_new', array(
	'path'        => '/tickets/triggers/new-trigger/{trigger_type}',
	'controller'  => 'AdminBundle:TicketTriggers:editTrigger',
	'defaults'    => array('id' => '0'),
));

$collection->create('admin_ticketescalations_new', array(
	'path'        => '/tickets/escalations/new-escalation/{trigger_type}',
	'controller'  => 'AdminBundle:TicketTriggers:editEscalation',
	'defaults'    => array('id' => '0'),
));

$collection->create('admin_tickettriggers_edit', array(
	'path'          => '/tickets/triggers/{id}/edit',
	'controller'    => 'AdminBundle:TicketTriggers:editTrigger',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_ticketescalations_edit', array(
	'path'          => '/tickets/escalations/{id}/edit',
	'controller'    => 'AdminBundle:TicketTriggers:editEscalation',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_tickettriggers_save', array(
	'path'          => '/tickets/triggers/{id}/save',
	'controller'    => 'AdminBundle:TicketTriggers:saveTrigger',
	'requirements'  => array('id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_tickettriggers_updateorder', array(
	'path'        => '/tickets/triggers/update-orders.json',
	'controller'  => 'AdminBundle:TicketTriggers:updateOrder',
));

$collection->create('admin_tickettriggers_toggle', array(
	'path'        => '/tickets/triggers/toggle-enabled.json',
	'controller'  => 'AdminBundle:TicketTriggers:toggleEnabled',
));

$collection->create('admin_tickettriggers', array(
	'path'        => '/tickets/triggers/{list_type}',
	'controller'  => 'AdminBundle:TicketTriggers:listTriggers',
	'defaults'    => array('list_type' => ''),
));

$collection->create('admin_ticketescalations', array(
	'path'        => '/tickets/escalations',
	'controller'  => 'AdminBundle:TicketTriggers:listEscalations',
));

$collection->create('admin_tickettriggers_delete', array(
	'path'          => '/tickets/triggers/delete/{id}/{auth}',
	'controller'    => 'AdminBundle:TicketTriggers:delete',
	'requirements'  => array('id' => '[0-9]+'),
));

$collection->create('admin_customdeforganizations', array(
	'path'        => '/organization-fields',
	'controller'  => 'AdminBundle:CustomDefOrganizations:index',
));

$collection->create('admin_customdeforganizations_new_choosetype', array(
	'path'        => '/organization-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefOrganizations:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdeforganizations_edit', array(
	'path'          => '/organization-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefOrganizations:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeforganizations_delete', array(
	'path'          => '/organization-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefOrganizations:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeforganizations_setenabled', array(
	'path'          => '/organization-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefOrganizations:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_import', array(
	'path'          => '/import',
	'controller'    => 'AdminBundle:Import:index',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_import_csv_configure', array(
	'path'          => '/import/csv-configure',
	'controller'    => 'AdminBundle:Import:csvConfigure',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_import_csv_import', array(
	'path'          => '/import/csv-import',
	'controller'    => 'AdminBundle:Import:csvImport',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_userrules', array(
	'path'        => '/user-rules',
	'controller'  => 'AdminBundle:UserRules:list',
));

$collection->create('admin_userrules_new', array(
	'path'        => '/user-rules/new',
	'controller'  => 'AdminBundle:UserRules:edit',
	'defaults'    => array('rule_id' => 0),
));

$collection->create('admin_userrules_edit', array(
	'path'          => '/user-rules/{rule_id}',
	'controller'    => 'AdminBundle:UserRules:edit',
	'requirements'  => array('rule_id' => '\\d+'),
));

$collection->create('admin_userrules_apply', array(
	'path'          => '/user-rules/{rule_id}/apply',
	'controller'    => 'AdminBundle:UserRules:apply',
	'requirements'  => array('rule_id' => '\\d+'),
));

$collection->create('admin_userrules_applyrun', array(
	'path'          => '/user-rules/{rule_id}/apply-run',
	'controller'    => 'AdminBundle:UserRules:applyRun',
	'requirements'  => array('rule_id' => '\\d+'),
));

$collection->create('admin_userrules_delete', array(
	'path'          => '/user-rules/{rule_id}/delete',
	'controller'    => 'AdminBundle:UserRules:delete',
	'requirements'  => array('rule_id' => '\\d+'),
));

$collection->create('admin_usergroups', array(
	'path'        => '/usergroups',
	'controller'  => 'AdminBundle:Usergroups:list',
));

$collection->create('admin_usergroups_new', array(
	'path'          => '/usergroups/new',
	'controller'    => 'AdminBundle:Usergroups:edit',
	'defaults'      => array('id' => 0),
	'requirements'  => array('usergroup_id' => '\\d+'),
));

$collection->create('admin_usergroups_edit', array(
	'path'          => '/usergroups/{id}/edit',
	'controller'    => 'AdminBundle:Usergroups:edit',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_usergroups_delete', array(
	'path'          => '/usergroups/{id}/delete/{auth}',
	'controller'    => 'AdminBundle:Usergroups:delete',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_usergroups_toggle', array(
	'path'          => '/agents/groups/{id}/toggle',
	'controller'    => 'AdminBundle:Usergroups:toggleGroup',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_products', array(
	'path'        => '/products',
	'controller'  => 'AdminBundle:Products:list',
));

$collection->create('admin_products_toggle', array(
	'path'        => '/products/toggle-feature/{enable}',
	'controller'  => 'AdminBundle:Products:toggleFeature',
));

$collection->create('admin_products_savenew', array(
	'path'        => '/products/save-new',
	'controller'  => 'AdminBundle:Products:saveNew',
	'methods'     => array('POST'),
));

$collection->create('admin_products_setdefault', array(
	'path'        => '/products/set-default',
	'controller'  => 'AdminBundle:Products:setDefault',
	'methods'     => array('POST'),
));

$collection->create('admin_products_savetitle', array(
	'path'        => '/products/save-title',
	'controller'  => 'AdminBundle:Products:saveTitle',
	'methods'     => array('POST'),
));

$collection->create('admin_products_updateorders', array(
	'path'        => '/products/update-orders',
	'controller'  => 'AdminBundle:Products:updateOrders',
));

$collection->create('admin_products_edit', array(
	'path'          => '/products/{product_id}/edit',
	'controller'    => 'AdminBundle:Products:edit',
	'requirements'  => array('product_id' => '\\d+'),
));

$collection->create('admin_products_del', array(
	'path'          => '/products/{product_id}/delete',
	'controller'    => 'AdminBundle:Products:delete',
	'requirements'  => array('product_id' => '\\d+'),
));

$collection->create('admin_products_dodel', array(
	'path'          => '/products/{product_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Products:doDelete',
	'requirements'  => array(
		'product_id'      => '\\d+',
		'security_token'  => '[a-zA-Z0-9\\-]+',
	),
));

$collection->create('admin_customdefproducts', array(
	'path'        => '/product-fields',
	'controller'  => 'AdminBundle:CustomDefProducts:index',
));

$collection->create('admin_customdefproducts_new_choosetype', array(
	'path'        => '/product-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefProducts:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdefproducts_edit', array(
	'path'          => '/product-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefProducts:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefproducts_delete', array(
	'path'          => '/product-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefProducts:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefproducts_setenabled', array(
	'path'          => '/product-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefProducts:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_departments', array(
	'path'          => '/departments/{type}',
	'controller'    => 'AdminBundle:Departments:list',
	'defaults'      => array('type' => ''),
	'requirements'  => array('type' => '(tickets|chat|)'),
));

$collection->create('admin_departments_saveagents', array(
	'path'          => '/departments/{department_id}/save-agents.json',
	'controller'    => 'AdminBundle:Departments:saveAgents',
	'requirements'  => array('department_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_departments_savegateway', array(
	'path'          => '/departments/{department_id}/save-gateway-account.json',
	'controller'    => 'AdminBundle:Departments:saveGatewayAccount',
	'requirements'  => array('department_id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_departments_setdefault', array(
	'path'          => '/departments/{type}/set-default',
	'controller'    => 'AdminBundle:Departments:setDefault',
	'requirements'  => array('department_id' => '\\d+'),
));

$collection->create('admin_departments_setphrase', array(
	'path'        => '/departments/set-phrase',
	'controller'  => 'AdminBundle:Departments:setPhrase',
	'methods'     => array('POST'),
));

$collection->create('admin_departments_savenew', array(
	'path'        => '/departments/{type}/save-new',
	'controller'  => 'AdminBundle:Departments:saveNew',
	'methods'     => array('POST'),
));

$collection->create('admin_departments_savetitle', array(
	'path'        => '/departments/save-title',
	'controller'  => 'AdminBundle:Departments:saveTitle',
	'methods'     => array('POST'),
));

$collection->create('admin_departments_del', array(
	'path'          => '/departments/{department_id}/delete',
	'controller'    => 'AdminBundle:Departments:delete',
	'requirements'  => array('department_id' => '\\d+'),
));

$collection->create('admin_departments_dodel', array(
	'path'          => '/departments/{department_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Departments:doDelete',
	'requirements'  => array(
		'department_id'   => '\\d+',
		'security_token'  => '[a-zA-Z0-9\\-]+',
	),
));

$collection->create('admin_departments_updateorders', array(
	'path'        => '/departments/update-orders',
	'controller'  => 'AdminBundle:Departments:updateOrders',
));

$collection->create('admin_twitter_accounts', array(
	'path'        => '/twitter/accounts',
	'controller'  => 'AdminBundle:TwitterAccount:list',
));

$collection->create('admin_twitter_apps', array(
	'path'        => '/twitter/apps',
	'controller'  => 'AdminBundle:TwitterAccount:apps',
));

$collection->create('admin_twitter_set_cleanup', array(
	'path'        => '/twitter/set-cleanup',
	'controller'  => 'AdminBundle:TwitterAccount:setCleanup',
	'methods'     => array('POST'),
));

$collection->create('admin_twitter_accounts_new', array(
	'path'        => '/twitter/accounts/new',
	'controller'  => 'AdminBundle:TwitterAccount:new',
));

$collection->create('admin_twitter_accounts_edit', array(
	'path'        => '/twitter/accounts/{account_id}/edit',
	'controller'  => 'AdminBundle:TwitterAccount:edit',
));

$collection->create('admin_twitter_accounts_delete', array(
	'path'        => '/twitter/accounts/{account_id}/delete/{security_token}',
	'controller'  => 'AdminBundle:TwitterAccount:delete',
));

$collection->create('admin_plugins', array(
	'path'        => '/plugins',
	'controller'  => 'AdminBundle:Plugins:list',
));

$collection->create('admin_plugins_toggle', array(
	'path'        => '/plugins/toggle',
	'controller'  => 'AdminBundle:Plugins:toggle',
));

$collection->create('admin_plugins_install', array(
	'path'        => '/plugins/{plugin_id}/install',
	'controller'  => 'AdminBundle:Plugins:install',
));

$collection->create('admin_plugins_install_step', array(
	'path'        => '/plugins/{plugin_id}/install/{step}',
	'controller'  => 'AdminBundle:Plugins:install',
));

$collection->create('admin_plugins_uninstall', array(
	'path'        => '/plugins/{plugin_id}/uninstall',
	'controller'  => 'AdminBundle:Plugins:uninstall',
));

$collection->create('admin_plugins_plugin', array(
	'path'        => '/plugins/{plugin_id}/config',
	'controller'  => 'AdminBundle:Plugins:config',
));

$collection->create('admin_plugins_run', array(
	'path'        => '/plugins/{plugin_id}/run/{action}',
	'controller'  => 'AdminBundle:Plugins:run',
));

$collection->create('admin_feedback_statuses', array(
	'path'        => '/portal/feedback/statuses',
	'controller'  => 'AdminBundle:Feedback:statuses',
));

$collection->create('admin_feedback_statuses_ajaxadd', array(
	'path'        => '/portal/feedback/statuses/new',
	'controller'  => 'AdminBundle:Feedback:ajaxNewStatus',
));

$collection->create('admin_feedback_statuses_edit', array(
	'path'        => '/portal/feedback/statuses/{category_id}/edit',
	'controller'  => 'AdminBundle:Feedback:editStatus',
));

$collection->create('admin_feedback_statuses_del', array(
	'path'        => '/portal/feedback/statuses/{category_id}/delete',
	'controller'  => 'AdminBundle:Feedback:deleteStatus',
));

$collection->create('admin_feedback_status_updateorders', array(
	'path'        => '/portal/feedback/statuses/update-orders',
	'controller'  => 'AdminBundle:Feedback:updateStatusOrders',
));

$collection->create('admin_feedback_cats', array(
	'path'        => '/portal/feedback/types',
	'controller'  => 'AdminBundle:Feedback:categories',
));

$collection->create('admin_feedback_cats_edit', array(
	'path'        => '/portal/feedback/types/{category_id}/edit',
	'controller'  => 'AdminBundle:Feedback:editCategory',
));

$collection->create('admin_feedback_cats_del', array(
	'path'        => '/portal/feedback/types/{category_id}/delete',
	'controller'  => 'AdminBundle:Feedback:deleteCategory',
));

$collection->create('admin_feedback_cats_updateorders', array(
	'path'        => '/portal/feedback/types/update-orders',
	'controller'  => 'AdminBundle:Feedback:updateCategoryOrders',
));

$collection->create('admin_feedback_usercats', array(
	'path'        => '/portal/feedback/categories',
	'controller'  => 'AdminBundle:Feedback:userCategory',
));

$collection->create('admin_customdefarticles', array(
	'path'        => '/article-fields',
	'controller'  => 'AdminBundle:CustomDefArticles:index',
));

$collection->create('admin_customdefarticles_new_choosetype', array(
	'path'        => '/article-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefArticles:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdefarticles_edit', array(
	'path'          => '/article-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefArticles:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefarticles_delete', array(
	'path'          => '/article-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefArticles:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefarticles_setenabled', array(
	'path'          => '/article-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefArticles:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeffeedback', array(
	'path'        => '/feedback-fields',
	'controller'  => 'AdminBundle:CustomDefFeedback:index',
));

$collection->create('admin_customdeffeedback_new_choosetype', array(
	'path'        => '/feedback-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefFeedback:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdeffeedback_edit', array(
	'path'          => '/feedback-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefFeedback:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeffeedback_delete', array(
	'path'          => '/feedback-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefFeedback:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdeffeedback_setenabled', array(
	'path'          => '/feedback-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefFeedback:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_emailgateways', array(
	'path'        => '/email/incoming',
	'controller'  => 'AdminBundle:EmailGateways:list',
));

$collection->create('admin_emailgateways_savehdaddr', array(
	'path'        => '/email/incoming/save-helpdesk-addresses',
	'controller'  => 'AdminBundle:EmailGateways:saveHelpdeskAddresses',
	'methods'     => array('POST'),
));

$collection->create('admin_emailgateways_new', array(
	'path'        => '/email/incoming/new',
	'controller'  => 'AdminBundle:EmailGateways:editAccount',
	'defaults'    => array('id' => 0),
));

$collection->create('admin_emailgateways_edit', array(
	'path'          => '/email/incoming/accounts/{id}/edit',
	'controller'    => 'AdminBundle:EmailGateways:editAccount',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_emailgateways_quicktoggle', array(
	'path'          => '/email/incoming/accounts/{id}/quick-toggle.json',
	'controller'    => 'AdminBundle:EmailGateways:quickToggle',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_emailgateways_setlinkeddep', array(
	'path'          => '/email/incoming/accounts/set-linked-department.json',
	'controller'    => 'AdminBundle:EmailGateways:setLinkedDepartment',
	'requirements'  => array('id' => '\\d+'),
	'methods'       => array('POST'),
));

$collection->create('admin_emailgateways_del', array(
	'path'          => '/email/incoming/accounts/{id}/delete/{security_token}',
	'controller'    => 'AdminBundle:EmailGateways:delete',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_emailgateways_testaccount', array(
	'path'          => '/email/incoming/accounts/test-account.json',
	'controller'    => 'AdminBundle:EmailGateways:ajaxTest',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_ticket_msgtpl', array(
	'path'        => '/tickets/message-templates',
	'controller'  => 'AdminBundle:TicketMessageTemplates:index',
));

$collection->create('admin_ticket_msgtpl_new', array(
	'path'        => '/tickets/message-templates/new',
	'controller'  => 'AdminBundle:TicketMessageTemplates:edit',
	'defaults'    => array('id' => 0),
));

$collection->create('admin_ticket_msgtpl_edit', array(
	'path'        => '/tickets/message-templates/{id}',
	'controller'  => 'AdminBundle:TicketMessageTemplates:edit',
));

$collection->create('admin_ticket_msgtpl_delete', array(
	'path'        => '/tickets/message-templates/{id}/{security_token}',
	'controller'  => 'AdminBundle:TicketMessageTemplates:delete',
));

$collection->create('admin_emailtrans_set_default_from', array(
	'path'        => '/email/outgoing/update-default-from',
	'controller'  => 'AdminBundle:EmailTransports:setDefaultFrom',
));

$collection->create('admin_emailtrans_list', array(
	'path'        => '/email/outgoing',
	'controller'  => 'AdminBundle:EmailTransports:list',
));

$collection->create('admin_emailtrans_setup', array(
	'path'        => '/setup/default-smtp',
	'controller'  => 'AdminBundle:EmailTransports:setup',
));

$collection->create('admin_emailtrans_newaccount', array(
	'path'        => '/email/outgoing/accounts/new',
	'controller'  => 'AdminBundle:EmailTransports:editAccount',
	'defaults'    => array('id' => 0),
));

$collection->create('admin_emailtrans_editaccount', array(
	'path'        => '/email/outgoing/accounts/{id}/edit',
	'controller'  => 'AdminBundle:EmailTransports:editAccount',
));

$collection->create('admin_emailtrans_del', array(
	'path'          => '/email/outgoing/accounts/{id}/delete/{security_token}',
	'controller'    => 'AdminBundle:EmailTransports:delete',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_emailtrans_testaccount', array(
	'path'        => '/email/outgoing/accounts/test-account.json',
	'controller'  => 'AdminBundle:EmailTransports:ajaxTest',
));

$collection->create('admin_server_cron', array(
	'path'        => '/server/cron',
	'controller'  => 'AdminBundle:Cron:list',
));

$collection->create('admin_server_cron_logs', array(
	'path'        => '/server/cron/logs',
	'controller'  => 'AdminBundle:Cron:logs',
));

$collection->create('admin_server_cron_logs_clear', array(
	'path'        => '/server/cron/logs/clear',
	'controller'  => 'AdminBundle:Cron:clearLogs',
));

$collection->create('admin_server_checks', array(
	'path'        => '/server/checks',
	'controller'  => 'AdminBundle:Server:serverChecks',
));

$collection->create('admin_server_file_checks', array(
	'path'        => '/server/file-integrity-checks',
	'controller'  => 'AdminBundle:Server:fileChecks',
));

$collection->create('admin_server_file_checks_do', array(
	'path'        => '/server/file-integrity-checks/do/{batch}',
	'controller'  => 'AdminBundle:Server:fileChecksDo',
	'defaults'    => array('batch' => '0'),
));

$collection->create('admin_server_phpinfo', array(
	'path'        => '/server/phpinfo',
	'controller'  => 'AdminBundle:Server:phpinfo',
));

$collection->create('admin_server_phpinfo_download', array(
	'path'        => '/server/phpinfo/download',
	'controller'  => 'AdminBundle:Server:phpinfoDownload',
));

$collection->create('admin_server_mysqlinfo', array(
	'path'        => '/server/mysqlinfo',
	'controller'  => 'AdminBundle:Server:mysqlinfo',
));

$collection->create('admin_server_mysqlstatus', array(
	'path'        => '/server/mysqlstatus',
	'controller'  => 'AdminBundle:Server:mysqlstatus',
));

$collection->create('admin_server_mysql_sorting', array(
	'path'        => '/server/mysql-sorting',
	'controller'  => 'AdminBundle:Server:mysqlSorting',
));

$collection->create('admin_server_mysql_sorting_save', array(
	'path'        => '/server/mysql-sorting/save',
	'controller'  => 'AdminBundle:Server:mysqlSortingSave',
	'methods'     => array('POST'),
));

$collection->create('admin_server_mysql_sorting_status', array(
	'path'        => '/server/mysql-sorting/status',
	'controller'  => 'AdminBundle:Server:mysqlSortingStatus',
));

$collection->create('admin_server_downloadschema', array(
	'path'        => '/server/database-schema.sql',
	'controller'  => 'AdminBundle:Server:downloadDatabaseSchema',
));

$collection->create('admin_server_testemail', array(
	'path'        => '/server/test-email',
	'controller'  => 'AdminBundle:Server:testEmail',
));

$collection->create('admin_server_error_logs', array(
	'path'        => '/server/error-logs',
	'controller'  => 'AdminBundle:Server:errorLogs',
));

$collection->create('admin_server_error_logs_clear', array(
	'path'        => '/server/error-logs/clear-all',
	'controller'  => 'AdminBundle:Server:errorLogsClearAll',
));

$collection->create('admin_server_error_logs_view', array(
	'path'        => '/server/error-logs/{log_id}',
	'controller'  => 'AdminBundle:Server:viewErrorLog',
));

$collection->create('admin_server_attach', array(
	'path'        => '/server/attachments',
	'controller'  => 'AdminBundle:Server:attachments',
));

$collection->create('admin_server_attach_switch', array(
	'path'        => '/server/attachments/switch',
	'controller'  => 'AdminBundle:Server:attachmentsSwitch',
));

$collection->create('admin_server_task_queue_logs', array(
	'path'        => '/server/task-queue/logs',
	'controller'  => 'AdminBundle:TaskQueue:logs',
));

$collection->create('admin_emailgateway_errors', array(
	'path'        => '/email/gateway-errors/{object_type}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:index',
	'defaults'    => array('type' => 'errors', 'object_type' => 'ticket'),
));

$collection->create('admin_emailgateway_all', array(
	'path'        => '/email/list-sources/{object_type}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:index',
	'defaults'    => array('type' => 'all', 'object_type' => 'ticket'),
));

$collection->create('admin_emailgateway_rejections', array(
	'path'        => '/email/gateway-rejections/{object_type}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:index',
	'defaults'    => array('type' => 'rejections', 'object_type' => 'ticket'),
));

$collection->create('admin_emailgateway_errors_clear', array(
	'path'        => '/email/gateway-errors/clear/{security_token}/{object_type}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:clear',
	'defaults'    => array('type' => 'errors', 'object_type' => 'ticket'),
));

$collection->create('admin_emailgateway_rejections_clear', array(
	'path'        => '/email/gateway-rejections/clear/{security_token}/{object_type}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:clear',
	'defaults'    => array('type' => 'rejections', 'object_type' => 'ticket'),
));

$collection->create('admin_emailgateway_errors_view', array(
	'path'        => '/email/gateway-sources/{id}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:view',
));

$collection->create('admin_emailgateway_errors_delete', array(
	'path'        => '/email/gateway-sources/{id}/delete/{security_token}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:delete',
));

$collection->create('admin_emailgateway_reprocess', array(
	'path'        => '/email/gateway-sources/{id}/reprocess/{security_token}',
	'controller'  => 'AdminBundle:EmailGatewayErrors:reprocess',
));

$collection->create('admin_sendmail_queue_index', array(
	'path'        => '/email/sendmail-queue',
	'controller'  => 'AdminBundle:SendmailQueue:index',
));

$collection->create('admin_sendmail_queue_massactions', array(
	'path'        => '/email/sendmail-queue/mass-actions',
	'controller'  => 'AdminBundle:SendmailQueue:massActions',
	'methods'     => array('POST'),
));

$collection->create('admin_sendmail_queue_view', array(
	'path'          => '/email/sendmail-queue/{id}',
	'controller'    => 'AdminBundle:SendmailQueue:view',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_widgets', array(
	'path'        => '/widgets',
	'controller'  => 'AdminBundle:Widgets:index',
));

$collection->create('admin_widgets_new', array(
	'path'        => '/widgets/new',
	'controller'  => 'AdminBundle:Widgets:edit',
	'defaults'    => array('widget_id' => 0),
));

$collection->create('admin_widgets_edit', array(
	'path'        => '/widgets/{widget_id}/edit',
	'controller'  => 'AdminBundle:Widgets:edit',
));

$collection->create('admin_widgets_delete', array(
	'path'        => '/widgets/{widget_id}/delete',
	'controller'  => 'AdminBundle:Widgets:delete',
));

$collection->create('admin_widgets_toggle', array(
	'path'        => '/widgets/toggle',
	'controller'  => 'AdminBundle:Widgets:toggle',
));

$collection->create('admin_webhooks', array(
	'path'        => '/web-hooks',
	'controller'  => 'AdminBundle:WebHook:index',
));

$collection->create('admin_webhooks_new', array(
	'path'        => '/web-hooks/new',
	'controller'  => 'AdminBundle:WebHook:edit',
	'defaults'    => array('webhook_id' => 0),
));

$collection->create('admin_webhooks_edit', array(
	'path'        => '/web-hooks/{webhook_id}/edit',
	'controller'  => 'AdminBundle:WebHook:edit',
));

$collection->create('admin_webhooks_delete', array(
	'path'        => '/web-hooks/{webhook_id}/delete/{security_token}',
	'controller'  => 'AdminBundle:WebHook:delete',
));

$collection->create('admin_webhooks_test', array(
	'path'        => '/web-hooks/{webhook_id}/test/{security_token}',
	'controller'  => 'AdminBundle:WebHook:test',
));

$collection->create('admin_kb_gateways', array(
	'path'        => '/kb/gateways',
	'controller'  => 'AdminBundle:Kb:gateways',
));

$collection->create('admin_kb_gateways_set_category', array(
	'path'        => '/kb/gateways/set-category.json',
	'controller'  => 'AdminBundle:Kb:setGatewayCategory',
	'methods'     => array('POST'),
));

$collection->create('admin_kb_gateways_new', array(
	'path'        => '/kb/gateways/new',
	'controller'  => 'AdminBundle:Kb:editGateway',
	'defaults'    => array('id' => 0),
));

$collection->create('admin_kb_gateways_edit', array(
	'path'          => '/kb/gateways/{id}/edit',
	'controller'    => 'AdminBundle:Kb:editGateway',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_kb_gateways_quicktoggle', array(
	'path'          => '/kb/gateways/{id}/quick-toggle.json',
	'controller'    => 'AdminBundle:Kb:quickToggleGateway',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_kb_gateways_del', array(
	'path'          => '/kb/gateways/{id}/delete/{security_token}',
	'controller'    => 'AdminBundle:Kb:deleteGateway',
	'requirements'  => array('id' => '\\d+'),
));

$collection->create('admin_customdefchat', array(
	'path'        => '/chat-fields',
	'controller'  => 'AdminBundle:CustomDefChat:index',
));

$collection->create('admin_customdefchat_new_choosetype', array(
	'path'        => '/chat-fields/new-choose-type',
	'controller'  => 'AdminBundle:CustomDefChat:newChooseType',
	'defaults'    => array('field_id' => 0),
));

$collection->create('admin_customdefchat_edit', array(
	'path'          => '/chat-fields/{field_id}/edit',
	'controller'    => 'AdminBundle:CustomDefChat:edit',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefchat_delete', array(
	'path'          => '/chat-fields/{field_id}/delete/{security_token}',
	'controller'    => 'AdminBundle:CustomDefChat:delete',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_customdefchat_setenabled', array(
	'path'          => '/chat-fields/{field_id}/set-enabled',
	'controller'    => 'AdminBundle:CustomDefChat:setEnabled',
	'requirements'  => array('field_id' => '\\d+'),
));

$collection->create('admin_chat_editor_reset', array(
	'path'        => '/chat/editor/reset-all/{security_token}',
	'controller'  => 'AdminBundle:Chat:resetEditor',
));

$collection->create('admin_chat_editor', array(
	'path'          => '/chat/editor/{department_id}/{section}',
	'controller'    => 'AdminBundle:Chat:editor',
	'defaults'      => array('department_id' => 0, 		'section' => 'create'),
	'requirements'  => array('department_id' => '\\d+'),
));

$collection->create('admin_chat_editor_toggleper', array(
	'path'        => '/chat/editor/toggle-per-department',
	'controller'  => 'AdminBundle:Chat:togglePerDepartment',
));

$collection->create('admin_chat_editor_dep_init', array(
	'path'        => '/chat/editor/{department_id}/{section}/init',
	'controller'  => 'AdminBundle:Chat:initEditor',
	'defaults'    => array('section' => 'create'),
));

$collection->create('admin_chat_editor_dep_revert', array(
	'path'        => '/chat/editor/{department_id}/{section}/revert',
	'controller'  => 'AdminBundle:Chat:revertEditor',
	'defaults'    => array('section' => 'create'),
));

$collection->create('admin_chat_editor_dep', array(
	'path'        => '/chat/editor/{department_id}/{section}',
	'controller'  => 'AdminBundle:Chat:editor',
	'defaults'    => array('section' => 'create'),
));

$collection->create('admin_chat_editor_dep_save', array(
	'path'        => '/chat/editor/{department_id}/{section}/save',
	'controller'  => 'AdminBundle:Chat:saveEditor',
	'methods'     => array('POST'),
));

return $collection;
