<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('admin', new Route(
	'/',
	array('_controller' => 'AdminBundle:Main:index'),
	array(),
	array()
));

$collection->add('admin_test', new Route(
	'/test',
	array('_controller' => 'AdminBundle:Test:index'),
	array(),
	array()
));

$collection->add('admin_license', new Route(
	'/license',
	array('_controller' => 'AdminBundle:License:index'),
	array(),
	array()
));

$collection->add('admin_license_reqdemo', new Route(
	'/license/generate-demo',
	array('_controller' => 'AdminBundle:License:requestDemo'),
	array(),
	array()
));

$collection->add('admin_license_input', new Route(
	'/license/input',
	array('_controller' => 'AdminBundle:License:input'),
	array(),
	array()
));

$collection->add('admin_license_input_save', new Route(
	'/license/input/save',
	array('_controller' => 'AdminBundle:License:saveNewLicense'),
	array(),
	array()
));

$collection->add('admin_tickets_fields', new Route(
	'/tickets/fields',
	array('_controller' => 'AdminBundle:TicketProperties:list'),
	array(),
	array()
));

$collection->add('admin_tickets_editor', new Route(
	'/tickets/editor',
	array('_controller' => 'AdminBundle:TicketProperties:editor', 'department_id' => 0, 'section' => 'create'),
	array(),
	array()
));

$collection->add('admin_tickets_editor_dep_copydefault', new Route(
	'/tickets/editor/{department_id}/copy-default',
	array('_controller' => 'AdminBundle:TicketProperties:copyDefaultEditor'),
	array(),
	array()
));

$collection->add('admin_tickets_editor_dep_revert', new Route(
	'/tickets/editor/{department_id}/revert',
	array('_controller' => 'AdminBundle:TicketProperties:revertEditor'),
	array(),
	array()
));

$collection->add('admin_tickets_editor_form_embed', new Route(
	'/tickets/editor/{department_id}/website-widget',
	array('_controller' => 'AdminBundle:TicketProperties:formEmbed'),
	array(),
	array()
));

$collection->add('admin_tickets_editor_dep', new Route(
	'/tickets/editor/{department_id}/{section}',
	array('_controller' => 'AdminBundle:TicketProperties:editor', 'section' => 'create'),
	array(),
	array()
));

$collection->add('admin_tickets_editor_dep_save', new Route(
	'/tickets/editor/{department_id}/{section}/save',
	array('_controller' => 'AdminBundle:TicketProperties:saveEditor'),
	array(),
	array()
));

$collection->add('admin_email_properties', new Route(
	'/email-settings',
	array('_controller' => 'AdminBundle:EmailProperties:list'),
	array(),
	array()
));

$collection->add('admin_accept_upload', new Route(
	'/misc/accept-upload',
	array('_controller' => 'AdminBundle:Main:acceptTempUpload'),
	array(),
	array()
));

$collection->add('admin_portal', new Route(
	'/portal',
	array('_controller' => 'AdminBundle:Portal:index'),
	array(),
	array()
));

$collection->add('admin_portal_get_editor', new Route(
	'/portal/get-editor/{type}',
	array('_controller' => 'AdminBundle:Portal:getEditor'),
	array(),
	array()
));

$collection->add('admin_portal_save_editor', new Route(
	'/portal/save-editor/{type}',
	array('_controller' => 'AdminBundle:Portal:saveEditor'),
	array(),
	array()
));

$collection->add('admin_portal_settings', new Route(
	'/portal/settings',
	array('_controller' => 'AdminBundle:Portal:settings'),
	array(),
	array()
));

$collection->add('admin_portal_uploadfavicon', new Route(
	'/portal/upload-favicon',
	array('_controller' => 'AdminBundle:Portal:uploadFavicon'),
	array(),
	array()
));

$collection->add('admin_login', new Route(
	'/login',
	array('_controller' => 'AdminBundle:Login:index'),
	array(),
	array()
));

$collection->add('admin_login_authenticate_local', new Route(
	'/login/authenticate-password',
	array('_controller' => 'AdminBundle:Login:authenticateLocal', 'usersource_id' => 0),
	array(),
	array()
));

$collection->add('admin_ideas_settings', new Route(
	'/portal/ideas/settings',
	array('_controller' => 'AdminBundle:Ideas:ideaSettings'),
	array(),
	array()
));

$collection->add('admin_ideas_statuses', new Route(
	'/portal/ideas/statuses',
	array('_controller' => 'AdminBundle:Ideas:statuses'),
	array(),
	array()
));

$collection->add('admin_ideas_statuses_ajaxadd', new Route(
	'/portal/ideas/statuses/new',
	array('_controller' => 'AdminBundle:Ideas:ajaxNewStatus'),
	array(),
	array()
));

$collection->add('admin_ideas_statuses_edit', new Route(
	'/portal/ideas/statuses/{category_id}/edit',
	array('_controller' => 'AdminBundle:Ideas:editStatus'),
	array(),
	array()
));

$collection->add('admin_ideas_statuses_del', new Route(
	'/portal/ideas/statuses/{category_id}/delete',
	array('_controller' => 'AdminBundle:Ideas:deleteStatus'),
	array(),
	array()
));

$collection->add('admin_ideas_status_updateorders', new Route(
	'/portal/ideas/statuses/update-orders',
	array('_controller' => 'AdminBundle:Ideas:updateStatusOrders'),
	array(),
	array()
));

$collection->add('admin_ideas_cats', new Route(
	'/portal/ideas/categories',
	array('_controller' => 'AdminBundle:Ideas:categories'),
	array(),
	array()
));

$collection->add('admin_ideas_cats_edit', new Route(
	'/portal/ideas/categories/{category_id}/edit',
	array('_controller' => 'AdminBundle:Ideas:editCategory'),
	array(),
	array()
));

$collection->add('admin_ideas_cats_del', new Route(
	'/portal/ideas/categories/{category_id}/delete',
	array('_controller' => 'AdminBundle:Ideas:deleteCategory'),
	array(),
	array()
));

$collection->add('admin_ideas_cats_updateorders', new Route(
	'/portal/ideas/categories/update-orders',
	array('_controller' => 'AdminBundle:Ideas:updateCategoryOrders'),
	array(),
	array()
));

$collection->add('admin_settings', new Route(
	'/settings',
	array('_controller' => 'AdminBundle:Settings:settings'),
	array(),
	array()
));

$collection->add('admin_settings_set', new Route(
	'/settings/save-setting/{setting_name}/{security_token}',
	array('_controller' => 'AdminBundle:Settings:saveSingleSetting'),
	array(),
	array()
));

$collection->add('admin_settings_adv', new Route(
	'/settings/advanced',
	array('_controller' => 'AdminBundle:Settings:advanced'),
	array(),
	array()
));

$collection->add('admin_settings_adv_set', new Route(
	'/settings/advanced-set/{name}',
	array('_controller' => 'AdminBundle:Settings:advancedSet'),
	array(),
	array()
));

$collection->add('admin_labels', new Route(
	'/settings/labels/{label_type}',
	array('_controller' => 'AdminBundle:Settings:labels'),
	array('label_type' => '[a-z]+'),
	array()
));

$collection->add('admin_labels_rename', new Route(
	'/settings/labels/{label_type}/rename.json',
	array('_controller' => 'AdminBundle:Settings:renameLabel'),
	array('label_type' => '[a-z]+'),
	array()
));

$collection->add('admin_labels_new', new Route(
	'/settings/labels/new.json',
	array('_controller' => 'AdminBundle:Settings:labelsAjaxNew'),
	array('label_type' => '[a-z]+'),
	array()
));

$collection->add('admin_labels_del', new Route(
	'/settings/labels/{label_type}/delete.json',
	array('_controller' => 'AdminBundle:Settings:labelsAjaxDelete'),
	array('label_type' => '[a-z]+'),
	array()
));

$collection->add('admin_banning_emails', new Route(
	'/banning/emails',
	array('_controller' => 'AdminBundle:Banning:listEmails'),
	array(),
	array()
));

$collection->add('admin_banning_ips', new Route(
	'/banning/ips',
	array('_controller' => 'AdminBundle:Banning:listIps'),
	array(),
	array()
));

$collection->add('admin_banning_newip', new Route(
	'/banning/ips/new',
	array('_controller' => 'AdminBundle:Banning:newIpBan'),
	array(),
	array()
));

$collection->add('admin_banning_newemail', new Route(
	'/banning/emails/new',
	array('_controller' => 'AdminBundle:Banning:newEmailBan'),
	array(),
	array()
));

$collection->add('admin_banning_delip', new Route(
	'/banning/ips/remove',
	array('_controller' => 'AdminBundle:Banning:removeIpBan'),
	array(),
	array()
));

$collection->add('admin_banning_delemail', new Route(
	'/banning/emails/remove',
	array('_controller' => 'AdminBundle:Banning:removeEmailBan'),
	array(),
	array()
));

$collection->add('admin_agents', new Route(
	'/agents',
	array('_controller' => 'AdminBundle:Agents:agents'),
	array(),
	array()
));

$collection->add('admin_agents_new', new Route(
	'/agents/new',
	array('_controller' => 'AdminBundle:Agents:newAgent'),
	array(),
	array()
));

$collection->add('admin_agents_edit', new Route(
	'/agents/{person_id}/edit',
	array('_controller' => 'AdminBundle:Agents:editAgent'),
	array('person_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_getperms', new Route(
	'/agents/{person_id}/get-perms.json',
	array('_controller' => 'AdminBundle:Agents:getAgentPermissions'),
	array('person_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_edit_save', new Route(
	'/agents/{person_id}/edit/save',
	array('_controller' => 'AdminBundle:Agents:editAgentSave'),
	array('person_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_teams_edit', new Route(
	'/agents/teams/{team_id}/edit',
	array('_controller' => 'AdminBundle:Agents:editTeam'),
	array('team_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_teams_del', new Route(
	'/agents/teams/{team_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:Agents:deleteTeam'),
	array('team_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_teams_new', new Route(
	'/agents/teams/new',
	array('_controller' => 'AdminBundle:Agents:editTeam', 'team_id' => 0),
	array(),
	array()
));

$collection->add('admin_agents_groups_edit', new Route(
	'/agents/groups/{usergroup_id}/edit',
	array('_controller' => 'AdminBundle:Agents:editGroup'),
	array('usergroup_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_groups_del', new Route(
	'/agents/groups/{usergroup_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:Agents:deleteGroup'),
	array('usergroup_id' => '\\d+'),
	array()
));

$collection->add('admin_agents_groups_new', new Route(
	'/agents/groups/new',
	array('_controller' => 'AdminBundle:Agents:editGroup', 'usergroup_id' => 0),
	array(),
	array()
));

$collection->add('admin_styles', new Route(
	'/styles',
	array('_controller' => 'AdminBundle:Styles:listStyles'),
	array(),
	array()
));

$collection->add('admin_styles_showstyle', new Route(
	'/styles/{style_id}',
	array('_controller' => 'AdminBundle:Styles:showStyle'),
	array('style_id' => '\\d+'),
	array()
));

$collection->add('admin_styles_editstyle', new Route(
	'/styles/{style_id}/edit',
	array('_controller' => 'AdminBundle:Styles:editStyle'),
	array('style_id' => '\\d+'),
	array()
));

$collection->add('admin_styles_templates', new Route(
	'/styles/{style_id}/templates',
	array('_controller' => 'AdminBundle:Styles:styleTemplateList'),
	array('style_id' => '\\d+'),
	array()
));

$collection->add('admin_styles_edittemplate', new Route(
	'/styles/{style_id}/templates/edit-template',
	array('_controller' => 'AdminBundle:Styles:editTemplate'),
	array('style_id' => '\\d+'),
	array()
));

$collection->add('admin_styles_reverttemplate', new Route(
	'/styles/{style_id}/templates/revert-template',
	array('_controller' => 'AdminBundle:Styles:revertTemplate'),
	array('style_id' => '\\d+'),
	array()
));

$collection->add('admin_styles_editorpopup', new Route(
	'/styles/editor-popup',
	array('_controller' => 'AdminBundle:Styles:editorPopup'),
	array(),
	array()
));

$collection->add('admin_langs', new Route(
	'/languages',
	array('_controller' => 'AdminBundle:Languages:index'),
	array(),
	array()
));

$collection->add('admin_langs_editlang', new Route(
	'/languages/{language_id}/edit',
	array('_controller' => 'AdminBundle:Languages:editLanguage'),
	array('language_id' => '\\d+'),
	array()
));

$collection->add('admin_langs_dellang', new Route(
	'/languages/{language_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:Languages:deleteLanguage'),
	array('language_id' => '\\d+'),
	array()
));

$collection->add('admin_langs_newlang', new Route(
	'/languages/new-lang',
	array('_controller' => 'AdminBundle:Languages:newLanguage'),
	array(),
	array()
));

$collection->add('admin_langs_newlang_save', new Route(
	'/languages/new-lang/save',
	array('_controller' => 'AdminBundle:Languages:newLanguageSave'),
	array(),
	array()
));

$collection->add('admin_langs_editphrases', new Route(
	'/languages/{language_id}/phrases/{group}',
	array('_controller' => 'AdminBundle:Languages:editPhrases'),
	array('language_id' => '\\d+', 'group' => '[a-zA-Z0-9\\.\\-_]+'),
	array()
));

$collection->add('admin_langs_editphrases_save', new Route(
	'/languages/{language_id}/phrases-save',
	array('_controller' => 'AdminBundle:Languages:savePhrases'),
	array('language_id' => '\\d+'),
	array()
));

$collection->add('admin_usersources', new Route(
	'/usersources',
	array('_controller' => 'AdminBundle:Usersources:index'),
	array(),
	array()
));

$collection->add('admin_usersources_intro', new Route(
	'/usersources/intro',
	array('_controller' => 'AdminBundle:Usersources:intro'),
	array(),
	array()
));

$collection->add('admin_usersources_info', new Route(
	'/usersources/{usersource_id}',
	array('_controller' => 'AdminBundle:Usersources:info'),
	array('usersource_id' => '\\d+'),
	array()
));

$collection->add('admin_usersources_edit', new Route(
	'/usersources/{usersource_id}/edit',
	array('_controller' => 'AdminBundle:Usersources:edit'),
	array('usersource_id' => '\\d+'),
	array()
));

$collection->add('admin_usersources_new', new Route(
	'/usersources/new-usersource',
	array('_controller' => 'AdminBundle:Usersources:edit', 'usersource_id' => 0),
	array(),
	array()
));

$collection->add('admin_api_keylist', new Route(
	'/api',
	array('_controller' => 'AdminBundle:Api:index'),
	array(),
	array()
));

$collection->add('admin_api_delkey', new Route(
	'/api/key/{id}/del',
	array('_controller' => 'AdminBundle:Api:delKey'),
	array('id' => '\\d+'),
	array()
));

$collection->add('admin_api_editkey', new Route(
	'/api/key/{id}/edit',
	array('_controller' => 'AdminBundle:Api:editKey'),
	array('id' => '\\d+'),
	array()
));

$collection->add('admin_api_newkey', new Route(
	'/api/key/new',
	array('_controller' => 'AdminBundle:Api:editKey', 'id' => 0),
	array(),
	array()
));

$collection->add('admin_customdefpeople', new Route(
	'/people-fields',
	array('_controller' => 'AdminBundle:CustomDefPeople:index'),
	array(),
	array()
));

$collection->add('admin_customdefpeople_new_choosetype', new Route(
	'/people-fields/new-choose-type',
	array('_controller' => 'AdminBundle:CustomDefPeople:newChooseType', 'field_id' => 0),
	array(),
	array()
));

$collection->add('admin_customdefpeople_edit', new Route(
	'/people-fields/{field_id}/edit',
	array('_controller' => 'AdminBundle:CustomDefPeople:edit'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefpeople_setenabled', new Route(
	'/people-fields/{field_id}/set-enabled',
	array('_controller' => 'AdminBundle:CustomDefPeople:setEnabled'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefpeople_test', new Route(
	'/people-fields/{field_id}/test',
	array('_controller' => 'AdminBundle:CustomDefPeople:test'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_features', new Route(
	'/tickets/features',
	array('_controller' => 'AdminBundle:TicketFeatures:index'),
	array(),
	array()
));

$collection->add('admin_customdeftickets', new Route(
	'/ticket-fields',
	array('_controller' => 'AdminBundle:CustomDefTickets:index'),
	array(),
	array()
));

$collection->add('admin_customdeftickets_new_choosetype', new Route(
	'/ticket-fields/new-choose-type',
	array('_controller' => 'AdminBundle:CustomDefTickets:newChooseType', 'field_id' => 0),
	array(),
	array()
));

$collection->add('admin_customdeftickets_edit', new Route(
	'/ticket-fields/{field_id}/edit',
	array('_controller' => 'AdminBundle:CustomDefTickets:edit'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdeftickets_setenabled', new Route(
	'/ticket-fields/{field_id}/set-enabled',
	array('_controller' => 'AdminBundle:CustomDefTickets:setEnabled'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdeftickets_test', new Route(
	'/ticket-fields/{field_id}/test',
	array('_controller' => 'AdminBundle:CustomDefTickets:test'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketwidgets', new Route(
	'/tickets/widgets',
	array('_controller' => 'AdminBundle:TicketWidgets:list'),
	array(),
	array()
));

$collection->add('admin_ticketwidgets_new_choosetype', new Route(
	'/tickets/widgets/new-choose-type',
	array('_controller' => 'AdminBundle:TicketWidgets:newChooseType'),
	array(),
	array()
));

$collection->add('admin_ticketwidgets_edit', new Route(
	'/tickets/widgets/{widget_id}/edit',
	array('_controller' => 'AdminBundle:TicketWidgets:edit'),
	array('widget_id' => '\\d+'),
	array()
));

$collection->add('admin_tickettriggers', new Route(
	'/tickets/business-rules',
	array('_controller' => 'AdminBundle:TicketTriggers:list'),
	array(),
	array()
));

$collection->add('admin_tickettriggers_new_choosetype', new Route(
	'/tickets/business-rules/new-trigger',
	array('_controller' => 'AdminBundle:TicketTriggers:newChooseType', 'trigger_type' => 'trigger'),
	array(),
	array()
));

$collection->add('admin_ticketescalations_new_choosetype', new Route(
	'/tickets/business-rules/new-escalation',
	array('_controller' => 'AdminBundle:TicketTriggers:newChooseType', 'trigger_type' => 'escalation'),
	array(),
	array()
));

$collection->add('admin_tickettriggers_edit', new Route(
	'/tickets/business-rules/{trigger_id}/edit',
	array('_controller' => 'AdminBundle:TicketTriggers:edit'),
	array('trigger_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketurgency_saveoptions', new Route(
	'/tickets/urgency/save-options',
	array('_controller' => 'AdminBundle:TicketTriggers:saveUrgencyOptions'),
	array(),
	array()
));

$collection->add('admin_ticketautoclose_saveoptions', new Route(
	'/tickets/auto-close/save-options',
	array('_controller' => 'AdminBundle:TicketTriggers:saveAutoCloseOptions'),
	array(),
	array()
));

$collection->add('admin_customdeforganizations', new Route(
	'/organization-fields',
	array('_controller' => 'AdminBundle:CustomDefOrganizations:index'),
	array(),
	array()
));

$collection->add('admin_customdeforganizations_new_choosetype', new Route(
	'/organization-fields/new-choose-type',
	array('_controller' => 'AdminBundle:CustomDefOrganizations:newChooseType', 'field_id' => 0),
	array(),
	array()
));

$collection->add('admin_customdeforganizations_edit', new Route(
	'/organization-fields/{field_id}/edit',
	array('_controller' => 'AdminBundle:CustomDefOrganizations:edit'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdeforganizations_setenabled', new Route(
	'/organization-fields/{field_id}/set-enabled',
	array('_controller' => 'AdminBundle:CustomDefOrganizations:setEnabled'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdeforganizations_test', new Route(
	'/organization-fields/{field_id}/test',
	array('_controller' => 'AdminBundle:CustomDefOrganizations:test'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_usergroups', new Route(
	'/usergroups',
	array('_controller' => 'AdminBundle:Usergroups:list'),
	array(),
	array()
));

$collection->add('admin_usergroups_edit', new Route(
	'/usergroups/{usergroup_id}',
	array('_controller' => 'AdminBundle:Usergroups:edit'),
	array('usergroup_id' => '\\d+'),
	array()
));

$collection->add('admin_organizations', new Route(
	'/organizations',
	array('_controller' => 'AdminBundle:Organizations:list'),
	array(),
	array()
));

$collection->add('admin_organizations_edit', new Route(
	'/organizations/{organization_id}',
	array('_controller' => 'AdminBundle:Organizations:edit'),
	array('organization_id' => '\\d+'),
	array()
));

$collection->add('admin_products', new Route(
	'/products',
	array('_controller' => 'AdminBundle:Products:list'),
	array(),
	array()
));

$collection->add('admin_products_edit', new Route(
	'/products/{product_id}',
	array('_controller' => 'AdminBundle:Products:edit'),
	array('product_id' => '\\d+'),
	array()
));

$collection->add('admin_products_updateorders', new Route(
	'/products/update-orders',
	array('_controller' => 'AdminBundle:Products:updateOrders'),
	array(),
	array()
));

$collection->add('admin_products_del', new Route(
	'/products/{product_id}/delete',
	array('_controller' => 'AdminBundle:Products:delete'),
	array('product_id' => '\\d+'),
	array()
));

$collection->add('admin_products_dodel', new Route(
	'/products/{product_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:Products:doDelete'),
	array('product_id' => '\\d+', 'security_token' => '[a-zA-Z0-9\\-]+'),
	array()
));

$collection->add('admin_departments', new Route(
	'/departments',
	array('_controller' => 'AdminBundle:Departments:list'),
	array(),
	array()
));

$collection->add('admin_departments_saveagents', new Route(
	'/departments/{department_id}/save-agents.json',
	array('_controller' => 'AdminBundle:Departments:saveAgents'),
	array('department_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_saveusergroups', new Route(
	'/departments/{department_id}/save-usergroups.json',
	array('_controller' => 'AdminBundle:Departments:saveUsergroups'),
	array('department_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_savefeaturestate', new Route(
	'/departments/{department_id}/save-feature-state.json',
	array('_controller' => 'AdminBundle:Departments:saveFeatureState'),
	array('department_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_edit', new Route(
	'/departments/{department_id}',
	array('_controller' => 'AdminBundle:Departments:edit'),
	array('department_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_del', new Route(
	'/departments/{department_id}/delete',
	array('_controller' => 'AdminBundle:Departments:delete'),
	array('department_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_dodel', new Route(
	'/departments/{department_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:Departments:doDelete'),
	array('department_id' => '\\d+', 'security_token' => '[a-zA-Z0-9\\-]+'),
	array()
));

$collection->add('admin_departments_designer', new Route(
	'/departments/{department_id}/designer',
	array('_controller' => 'AdminBundle:Departments:designer'),
	array('department_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_designer_ajaxfetchfield', new Route(
	'/departments/{department_id}/designer/ajax-fetch-field/{field_id}',
	array('_controller' => 'AdminBundle:Departments:designerAjaxFetchField'),
	array('department_id' => '\\d+', 'field_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_designer_ajaxfetchwidget', new Route(
	'/departments/{department_id}/designer/ajax-fetch-widget/{widget_id}',
	array('_controller' => 'AdminBundle:Departments:designerAjaxFetchWidget'),
	array('department_id' => '\\d+', 'widget_id' => '\\d+'),
	array()
));

$collection->add('admin_departments_updateorders', new Route(
	'/departments/update-orders',
	array('_controller' => 'AdminBundle:Departments:updateOrders'),
	array(),
	array()
));

$collection->add('admin_ticketcats', new Route(
	'/tickets/categories',
	array('_controller' => 'AdminBundle:TicketCategories:list'),
	array(),
	array()
));

$collection->add('admin_ticketcats_edit', new Route(
	'/tickets/categories/{category_id}',
	array('_controller' => 'AdminBundle:TicketCategories:edit'),
	array('category_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketcats_updateorders', new Route(
	'/tickets/categories/update-orders',
	array('_controller' => 'AdminBundle:TicketCategories:updateOrders'),
	array(),
	array()
));

$collection->add('admin_ticketcats_del', new Route(
	'/tickets/categories/{category_id}/delete',
	array('_controller' => 'AdminBundle:TicketCategories:delete'),
	array('category_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketcats_dodel', new Route(
	'/tickets/categories/{category_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:TicketCategories:doDelete'),
	array('category_id' => '\\d+', 'security_token' => '[a-zA-Z0-9\\-]+'),
	array()
));

$collection->add('admin_ticketpris', new Route(
	'/tickets/priorities',
	array('_controller' => 'AdminBundle:TicketPriorities:list'),
	array(),
	array()
));

$collection->add('admin_ticketpris_edit', new Route(
	'/tickets/priorities/{priority_id}',
	array('_controller' => 'AdminBundle:TicketPriorities:edit'),
	array('priority_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketpris_del', new Route(
	'/tickets/priorities/{priority_id}/delete',
	array('_controller' => 'AdminBundle:TicketPriorities:delete'),
	array('priority_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketpris_dodel', new Route(
	'/tickets/priorities/{priority_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:TicketPriorities:doDelete'),
	array('priority_id' => '\\d+', 'security_token' => '[a-zA-Z0-9\\-]+'),
	array()
));

$collection->add('admin_ticketworks', new Route(
	'/tickets/workflows',
	array('_controller' => 'AdminBundle:TicketWorkflows:list'),
	array(),
	array()
));

$collection->add('admin_ticketworks_edit', new Route(
	'/tickets/workflows/{workflow_id}',
	array('_controller' => 'AdminBundle:TicketWorkflows:edit'),
	array('workflow_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketworks_del', new Route(
	'/tickets/workflows/{workflow_id}/delete',
	array('_controller' => 'AdminBundle:TicketWorkflows:delete'),
	array('workflow_id' => '\\d+'),
	array()
));

$collection->add('admin_ticketworks_dodel', new Route(
	'/tickets/workflows/{workflow_id}/delete/{security_token}',
	array('_controller' => 'AdminBundle:TicketWorkflows:doDelete'),
	array('workflow_id' => '\\d+', 'security_token' => '[a-zA-Z0-9\\-]+'),
	array()
));

$collection->add('admin_ticketworks_updateorders', new Route(
	'/tickets/workflows/update-orders',
	array('_controller' => 'AdminBundle:TicketWorkflows:updateOrders'),
	array(),
	array()
));

$collection->add('admin_twitter_accounts', new Route(
	'/twitter/account',
	array('_controller' => 'AdminBundle:TwitterAccount:list'),
	array(),
	array()
));

$collection->add('admin_twitter_accounts_new', new Route(
	'/twitter/account/new',
	array('_controller' => 'AdminBundle:TwitterAccount:new'),
	array(),
	array()
));

$collection->add('admin_twitter_accounts_authorize', new Route(
	'/twitter/account/authorize',
	array('_controller' => 'AdminBundle:TwitterAccount:authorize'),
	array(),
	array()
));

$collection->add('admin_twitter_accounts_edit', new Route(
	'/twitter/account/{account_id}',
	array('_controller' => 'AdminBundle:TwitterAccount:edit'),
	array(),
	array()
));

$collection->add('admin_logs', new Route(
	'/logs',
	array('_controller' => 'AdminBundle:Logs:index'),
	array(),
	array()
));

$collection->add('admin_logs_view', new Route(
	'/logs/{log_id}',
	array('_controller' => 'AdminBundle:Logs:view'),
	array('log_id' => '\\d+'),
	array()
));

$collection->add('admin_logs_view_sn', new Route(
	'/logs/sn/{log_sn}',
	array('_controller' => 'AdminBundle:Logs:viewSn'),
	array(),
	array()
));

$collection->add('admin_logs_errors', new Route(
	'/logs/errors',
	array('_controller' => 'AdminBundle:Logs:errorLogs', 'page' => 1),
	array(),
	array()
));

$collection->add('admin_logs_errors_page', new Route(
	'/logs/errors/{page}',
	array('_controller' => 'AdminBundle:Logs:errorLogs'),
	array('page' => '\\d+'),
	array()
));

$collection->add('admin_logs_errors_clear', new Route(
	'/logs/errors/clear-all',
	array('_controller' => 'AdminBundle:Logs:errorLogsClearAll'),
	array(),
	array()
));

$collection->add('admin_emailgateways', new Route(
	'/email-gateways',
	array('_controller' => 'AdminBundle:EmailGateways:list'),
	array(),
	array()
));

$collection->add('admin_emailgateways_edit', new Route(
	'/email-gateways/{gateway_id}',
	array('_controller' => 'AdminBundle:EmailGateways:edit'),
	array('gateway_id' => '\\d+'),
	array()
));

$collection->add('admin_emailfroms', new Route(
	'/email-from-addresses',
	array('_controller' => 'AdminBundle:EmailFroms:list'),
	array(),
	array()
));

$collection->add('admin_emailfroms_edit', new Route(
	'/email-from-addresses/{email_id}',
	array('_controller' => 'AdminBundle:EmailFroms:edit'),
	array('email_id' => '\\d+'),
	array()
));

$collection->add('admin_emailfroms_new', new Route(
	'/email-from-addresses/new',
	array('_controller' => 'AdminBundle:EmailFroms:new'),
	array(),
	array()
));

$collection->add('admin_plugins', new Route(
	'/plugins',
	array('_controller' => 'AdminBundle:Plugins:list'),
	array(),
	array()
));

$collection->add('admin_plugins_install', new Route(
	'/plugins/{plugin_id}/install',
	array('_controller' => 'AdminBundle:Plugins:install'),
	array(),
	array()
));

$collection->add('admin_plugins_install_step', new Route(
	'/plugins/{plugin_id}/install/{step}',
	array('_controller' => 'AdminBundle:Plugins:install'),
	array(),
	array()
));

$collection->add('admin_plugins_uninstall', new Route(
	'/plugins/{plugin_id}/uninstall',
	array('_controller' => 'AdminBundle:Plugins:uninstall'),
	array(),
	array()
));

$collection->add('admin_tickets_filters', new Route(
	'/tickets/filters',
	array('_controller' => 'AdminBundle:TicketFilters:index'),
	array(),
	array()
));

$collection->add('admin_tickets_filters_getgloballist', new Route(
	'/tickets/filters/get-global-list',
	array('_controller' => 'AdminBundle:TicketFilters:getGlobalList'),
	array(),
	array()
));

$collection->add('admin_tickets_filters_getteamlist', new Route(
	'/tickets/filters/get-team-list',
	array('_controller' => 'AdminBundle:TicketFilters:getTeamList'),
	array(),
	array()
));

$collection->add('admin_tickets_filters_getagentlist', new Route(
	'/tickets/filters/get-agent-list',
	array('_controller' => 'AdminBundle:TicketFilters:getAgentList'),
	array(),
	array()
));

$collection->add('admin_tickets_filters_edit', new Route(
	'/tickets/filters/{filter_id}',
	array('_controller' => 'AdminBundle:TicketFilters:edit'),
	array('filter_id' => '\\d+'),
	array()
));

$collection->add('admin_tickets_filters_new', new Route(
	'/tickets/filters/new',
	array('_controller' => 'AdminBundle:TicketFilters:newChooseType'),
	array(),
	array()
));

$collection->add('admin_customdefarticles', new Route(
	'/article-fields',
	array('_controller' => 'AdminBundle:CustomDefArticles:index'),
	array(),
	array()
));

$collection->add('admin_customdefarticles_new_choosetype', new Route(
	'/article-fields/new-choose-type',
	array('_controller' => 'AdminBundle:CustomDefArticles:newChooseType', 'field_id' => 0),
	array(),
	array()
));

$collection->add('admin_customdefarticles_edit', new Route(
	'/article-fields/{field_id}/edit',
	array('_controller' => 'AdminBundle:CustomDefArticles:edit'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefarticles_setenabled', new Route(
	'/article-fields/{field_id}/set-enabled',
	array('_controller' => 'AdminBundle:CustomDefArticles:setEnabled'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefarticles_test', new Route(
	'/article-fields/{field_id}/test',
	array('_controller' => 'AdminBundle:CustomDefArticles:test'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefideas', new Route(
	'/idea-fields',
	array('_controller' => 'AdminBundle:CustomDefIdeas:index'),
	array(),
	array()
));

$collection->add('admin_customdefideas_new_choosetype', new Route(
	'/idea-fields/new-choose-type',
	array('_controller' => 'AdminBundle:CustomDefIdeas:newChooseType', 'field_id' => 0),
	array(),
	array()
));

$collection->add('admin_customdefideas_edit', new Route(
	'/idea-fields/{field_id}/edit',
	array('_controller' => 'AdminBundle:CustomDefIdeas:edit'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefideas_setenabled', new Route(
	'/idea-fields/{field_id}/set-enabled',
	array('_controller' => 'AdminBundle:CustomDefIdeas:setEnabled'),
	array('field_id' => '\\d+'),
	array()
));

$collection->add('admin_customdefideas_test', new Route(
	'/idea-fields/{field_id}/test',
	array('_controller' => 'AdminBundle:CustomDefIdeas:test'),
	array('field_id' => '\\d+'),
	array()
));

return $collection;
