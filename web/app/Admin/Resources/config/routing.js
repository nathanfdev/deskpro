define(function() {
	'use strict';
	var routes = [];

	//##################################################################################################################
	// Dev Nav
	//##################################################################################################################

	routes.push({
		id: 'dev_ui',
		url: '/dev_ui',
		templateName: 'Index/dev-ui.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	routes.push({
		id: 'dev_ui_table',
		url: '/dev_ui_table',
		templateName: 'Index/dev-ui-table.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	//##################################################################################################################
	// Home
	//##################################################################################################################

	routes.push({
		id: 'home',
		url: '/',
		templateName: 'Index/home.html',
		controller: 'Admin_Main_Ctrl_Home'
	});

	routes.push({
		id: 'license',
		url: '/license',
		templateName: 'License/license.html',
		controller: 'Admin_License_Ctrl_License'
	});

	//##################################################################################################################
	// Main Nav
	//##################################################################################################################

	routes.push({
		id: 'setup',
		url: '/setup',
		templateName: 'Index/app-nav-setup.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'agents',
		url: '/agents',
		templateName: 'Index/app-nav-agents.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'tickets',
		url: '/tickets',
		templateName: 'Index/app-nav-tickets.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'crm',
		url: '/crm',
		templateName: 'Index/app-nav-crm.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'portal',
		url: '/portal',
		templateName: 'Index/app-nav-portal.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'chat',
		url: '/chat',
		templateName: 'Index/app-nav-chat.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'twitter',
		url: '/twitter',
		templateName: 'Index/app-nav-twitter.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'apps',
		url: '/apps',
		templateName: 'Index/app-nav-apps.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'tasks',
		url: '/tasks',
		templateName: 'Index/app-nav-tasks.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	routes.push({
		id: 'server',
		url: '/server',
		templateName: 'Index/app-nav-server.html',
		controller: 'Admin_Main_Ctrl_Nav'
	});

	//##################################################################################################################
	// Interface Nav
	//##################################################################################################################

	routes.push({
		id: 'go_to_agent',
		url: '/go_to_agent',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BackToAgent'
	});

	routes.push({
		id: 'go_to_reports',
		url: '/go_to_reports',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_GoToReports'
	});

	routes.push({
		id: 'go_to_user',
		url: '/go_to_user',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_GoToUser'
	});

	//##################################################################################################################
	// Setup
	//##################################################################################################################

	//###
	//# Settings
	//###
	routes.push({
		id: 'setup.settings',
		url: '/settings',
		templateName: 'Settings/general-settings.html',
		controller: 'Admin_Settings_Ctrl_GeneralSettings'
	});

	//###
	//# Advanced Settings
	//###
	routes.push({
		id: 'setup.settings_advanced',
		url: '/settings_advanced',
		templateName: 'Settings/adv-settings.html',
		controller: 'Admin_Settings_Ctrl_AdvancedSettings'
	});

	//###
	//# Languages
	//###
	routes.push({
		id: 'setup.languages',
		url: '/languages',
		templateName: 'Languages/list.html',
		controller: 'Admin_Languages_Ctrl_List'
	});

	routes.push({
		id: 'setup.languages.newlang',
		url: '/languages/new-lang',
		templateName: 'Languages/new-lang.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	routes.push({
		id: 'setup.languages.settings',
		url: '/settings',
		templateName: 'Languages/settings.html',
		controller: 'Admin_Languages_Ctrl_Settings',
		target: "appbody@setup"
	});

	routes.push({
		id: 'setup.languages.edit',
		url: '/{id:[a-z]+}',
		templateName: 'Languages/edit.html',
		controller: 'Admin_Languages_Ctrl_Edit'
	});

	routes.push({
		id: 'setup.languages.install',
		url: '/{id:install\\-[a-z]+}',
		templateName: 'Languages/install.html',
		controller: 'Admin_Languages_Ctrl_Install'
	});

	//###
	//# Phrases
	//###
	routes.push({
		id: 'setup.phrases_go_viewgroup',
		url: '/{path:phrases\\-go\\-[a-zA-Z0-9\\._]+\\-[a-zA-Z0-9\\._]+}',
		templateName: 'Index/blank.html',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			var m = $stateParams.path.match(/^phrases\-go\-(.*?)\-(.*?)$/)
			$state.go('setup.phrases.viewgroup', {id: 'phrases-' + m[1], groupId: m[2]});
		}]
	});

	routes.push({
		id: 'setup.phrases',
		url: '/{id:phrases\\-[a-z]+}',
		templateName: 'Languages/phrases-list.html',
		controller: 'Admin_Languages_Ctrl_PhraseList'
	});

	routes.push({
		id: 'setup.phrases.viewresgroup',
		url: '/{groupId:res\\-[a-zA-Z0-9\\._]+}',
		templateName: 'Languages/phrases-viewresgroup.html',
		controller: 'Admin_Languages_Ctrl_PhraseResGroup'
	});

	routes.push({
		id: 'setup.phrases.viewgroup',
		url: '/{groupId:[a-zA-Z0-9\\._]+}',
		templateName: 'Languages/phrases-viewgroup.html',
		controller: 'Admin_Languages_Ctrl_PhraseGroup'
	});

	//###
	//# Outgoing Email
	//###
	routes.push({
		id: 'setup.setup',
		url: '/setup',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//##################################################################################################################
	// Agents
	//##################################################################################################################

	//###
	//# Agents
	//###
	routes.push({
		id: 'agents.agents',
		url: '/agents',
		templateName: 'Agents/list.html',
		controller: 'Admin_Agents_Ctrl_List'
	});

	routes.push({
		id: 'agents.agents.create',
		url: '/create',
		templateName: 'Agents/edit.html',
		controller: 'Admin_Agents_Ctrl_Edit'
	});

	routes.push({
		id: 'agents.agents.edit',
		url: '/{id:[0-9]+}',
		templateName: 'Agents/edit.html',
		controller: 'Admin_Agents_Ctrl_Edit'
	});

	routes.push({
		id: 'agents.agents_deleted',
		url: '/deleted',
		templateName: 'Agents/deleted-list.html',
		controller: 'Admin_Agents_Ctrl_DeletedList'
	});

	routes.push({
		id: 'agents.agents_deleted.restore',
		url: '/{id:[0-9]+}',
		templateName: 'Agents/deleted-restore.html',
		controller: 'Admin_Agents_Ctrl_DeletedRestore'
	});

	routes.push({
		id: 'agents.settings',
		url: '/settings',
		templateName: 'Agents/settings.html',
		controller: 'Admin_Settings_Ctrl_PasswordSettings'
	});

	//###
	//# User Sources
	//###
	routes.push({
		id: 'agents.usersources',
		url: '/usersources',
		templateName: 'Usersources/list.html',
		controller: 'Admin_Usersources_Ctrl_UsersourcesList'
	});

	routes.push({
		id: 'agents.usersources.new',
		url: '/new',
		templateName: 'Usersources/new.html',
		controller: 'Admin_Usersources_Ctrl_New'
	});

	routes.push({
		id: 'agents.usersources.id',
		url: '/{id:[\\d\\w]+}',
		templateName: 'Usersources/edit-instance.html',
		controller: 'Admin_Usersources_Ctrl_EditInstance'
	});

	routes.push({
		id: 'agents.usersources.install',
		url: '/install/{name:\\w+}'
		,
		templateName: 'Apps/package-install.html',
		controller: 'Admin_Apps_Ctrl_PackageInstall'
	});


	//###
	//# Agent Login Log
	//###
	routes.push({
		id: 'agents.login_log',
		url: '/login_log',
		templateName: 'Agents/logs.html',
		controller: 'Admin_Agents_Ctrl_Logs'
	});

	//###
	//# Teams
	//###
	routes.push({
		id: 'agents.teams',
		url: '/teams',
		templateName: 'AgentTeams/list.html',
		controller: 'Admin_AgentTeams_Ctrl_List'
	});

	routes.push({
		id: 'agents.teams.create',
		url: '/create',
		templateName: 'AgentTeams/edit.html',
		controller: 'Admin_AgentTeams_Ctrl_Edit'
	});

	routes.push({
		id: 'agents.teams.edit',
		url: '/{id:[0-9]+}',
		templateName: 'AgentTeams/edit.html',
		controller: 'Admin_AgentTeams_Ctrl_Edit'
	});

	//###
	//# Permission Groups
	//###
	routes.push({
		id: 'agents.groups',
		url: '/groups',
		templateName: 'AgentGroups/list.html',
		controller: 'Admin_AgentGroups_Ctrl_List'
	});

	routes.push({
		id: 'agents.groups.create',
		url: '/create',
		templateName: 'AgentGroups/edit.html',
		controller: 'Admin_AgentGroups_Ctrl_Edit'
	});

	routes.push({
		id: 'agents.groups.edit',
		url: '/{id:[0-9]+}',
		templateName: 'AgentGroups/edit.html',
		controller: 'Admin_AgentGroups_Ctrl_Edit'
	});

	//##################################################################################################################
	// Admin Log
	//##################################################################################################################

	routes.push({
		id: 'agents.audit_log',
		url: '/audit_log',
		templateName: 'AuditLog/list.html',
		controller: 'Admin_AuditLog_Ctrl_List'
	});

	routes.push({
		id: 'agents.audit_log.view',
		url: '/{id:[0-9]+}',
		templateName: 'AuditLog/view.html',
		controller: 'Admin_AuditLog_Ctrl_View'
	});

	//##################################################################################################################
	// Tickets
	//##################################################################################################################

	//###
	//# Statuses
	//###
	routes.push({
		id: 'tickets.statuses',
		url: '/statuses',
		templateName: 'TicketStatuses/list.html',
		controller: 'Admin_TicketStatuses_Ctrl_List'
	});

	routes.push({
		id: 'tickets.statuses.awaiting_agent',
		url: '/statuses/awaiting_agent',
		templateName: 'TicketStatuses/status-awaiting-agent.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditAwaitingAgent'
	});

	routes.push({
		id: 'tickets.statuses.awaiting_user',
		url: '/statuses/awaiting_user',
		templateName: 'TicketStatuses/status-awaiting-user.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditAwaitingUser'
	});

	routes.push({
		id: 'tickets.statuses.resolved',
		url: '/statuses/resolved',
		templateName: 'TicketStatuses/status-resolved.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditResolved'
	});

	routes.push({
		id: 'tickets.statuses.closed',
		url: '/statuses/closed',
		templateName: 'TicketStatuses/status-closed.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditClosed'
	});

	routes.push({
		id: 'tickets.statuses.hidden_validating',
		url: '/statuses/validating',
		templateName: 'TicketStatuses/status-hidden-validating.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditHiddenValidating'
	});

	routes.push({
		id: 'tickets.statuses.hidden_deleted',
		url: '/statuses/deleted',
		templateName: 'TicketStatuses/status-hidden-deleted.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditHiddenDeleted'
	});

	routes.push({
		id: 'tickets.statuses.hidden_spam',
		url: '/statuses/spam',
		templateName: 'TicketStatuses/status-hidden-spam.html',
		controller: 'Admin_TicketStatuses_Ctrl_EditHiddenSpam'
	});

	//###
	//# Urgency
	//###
	routes.push({
		id: 'tickets.urgency',
		url: '/urgency',
		templateName: 'TicketUrgencies/list.html',
		controller: 'Admin_TicketUrgencies_Ctrl_List'
	});

	//###
	//# Triggers
	//###
	routes.push({
		id: 'tickets.triggers',
		url: '/triggers/{type:(?:newticket|newreply|update)}',
		templateName: 'TicketTriggers/list.html',
		controller: 'Admin_TicketTriggers_Ctrl_List'
	});

	routes.push({
		id: 'tickets.triggers.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.triggers.create', $stateParams); }]
	});

	routes.push({
		id: 'tickets.triggers.create',
		url: '/create',
		templateName: 'TicketTriggers/edit.html',
		controller: 'Admin_TicketTriggers_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.triggers.editdep',
		url: '/{id:department\-[0-9]+}',
		templateName: 'TicketTriggers/edit-dep.html',
		controller: 'Admin_TicketTriggers_Ctrl_EditDepartmentTrigger',
		data: { stateMarkId: "tickets.triggers" }
	});

	routes.push({
		id: 'tickets.triggers.editdepchanged',
		url: '/{id:department\-changed\-[0-9]+}',
		templateName: 'TicketTriggers/edit-dep.html',
		controller: 'Admin_TicketTriggers_Ctrl_EditDepartmentTrigger',
		data: { stateMarkId: "tickets.triggers" }
	});

	routes.push({
		id: 'tickets.triggers.editemailacc',
		url: '/{id:emailaccount\-[0-9]+}',
		templateName: 'TicketTriggers/edit-emailacc.html',
		controller: 'Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger',
		data: { stateMarkId: "tickets.triggers" }
	});

	routes.push({
		id: 'tickets.triggers.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketTriggers/edit.html',
		controller: 'Admin_TicketTriggers_Ctrl_Edit',
		data: { stateMarkId: "tickets.triggers" }
	});


	//###
	//# Snippets
	//###
	routes.push({
		id: 'tickets.snippets',
		url: '/snippets',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Macros
	//###
	routes.push({
		id: 'tickets.macros',
		url: '/macros',
		templateName: 'TicketMacros/list.html',
		controller: 'Admin_TicketMacros_Ctrl_List'
	});

	routes.push({
		id: 'tickets.macros.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.macros.create', $stateParams); }]
	});

	routes.push({
		id: 'tickets.macros.create',
		url: '/create',
		templateName: 'TicketMacros/edit.html',
		controller: 'Admin_TicketMacros_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.macros.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketMacros/edit.html',
		controller: 'Admin_TicketMacros_Ctrl_Edit'
	});

	//###
	//# Filters
	//###
	routes.push({
		id: 'tickets.ticket_filters',
		url: '/ticket_filters',
		templateName: 'TicketFilters/list.html',
		controller: 'Admin_TicketFilters_Ctrl_List'
	});

	routes.push({
		id: 'tickets.ticket_filters.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.ticket_filters.create', $stateParams); }]
	});

	routes.push({
		id: 'tickets.ticket_filters.create',
		url: '/create',
		templateName: 'TicketFilters/edit.html',
		controller: 'Admin_TicketFilters_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.ticket_filters.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketFilters/edit.html',
		controller: 'Admin_TicketFilters_Ctrl_Edit'
	});

	//###
	//# Satisfaction
	//###
	routes.push({
		id: 'tickets.satisfaction',
		url: '/satisfaction',
		templateName: 'TicketSettings/satisfaction-settings.html',
		controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
	});

	//###
	//# Escalations
	//###
	routes.push({
		id: 'tickets.ticket_escalations',
		url: '/ticket_escalations',
		templateName: 'TicketEscalations/list.html',
		controller: 'Admin_TicketEscalations_Ctrl_List'
	});

	routes.push({
		id: 'tickets.ticket_escalations.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.ticket_escalations.create', $stateParams); }]
	});

	routes.push({
		id: 'tickets.ticket_escalations.create',
		url: '/create',
		templateName: 'TicketEscalations/edit.html',
		controller: 'Admin_TicketEscalations_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.ticket_escalations.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketEscalations/edit.html',
		controller: 'Admin_TicketEscalations_Ctrl_Edit'
	});

	//###
	//# SLAs
	//###
	routes.push({
		id: 'tickets.slas',
		url: '/slas',
		templateName: 'TicketSlas/list.html',
		controller: 'Admin_TicketSlas_Ctrl_List'
	});

	routes.push({
		id: 'tickets.slas.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.slas.create', $stateParams); }]
	});

	routes.push({
		id: 'tickets.slas.create',
		url: '/create',
		templateName: 'TicketSlas/edit.html',
		controller: 'Admin_TicketSlas_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.slas.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketSlas/edit.html',
		controller: 'Admin_TicketSlas_Ctrl_Edit'
	});

	//###
	//# Labels
	//###
	routes.push({
		id: 'tickets.labels',
		url: '/labels',
		templateName: 'Labels/Ticket/list.html',
		controller: 'Admin_Labels_Ticket_Ctrl_List'
	});

	routes.push({
		id: 'tickets.labels.create',
		url: '/create',
		templateName: 'Labels/Ticket/edit.html',
		controller: 'Admin_Labels_Ticket_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.labels.gocreate',
		url: '/go-create',
		templateName: 'Labels/Ticket/edit.html',
		controller: ['$state', function ($state) { $state.go('tickets.labels.create'); }]
	});

	routes.push({
		id: 'tickets.labels.edit',
		url: '/{label:.*}',
		templateName: 'Labels/Ticket/edit.html',
		controller: 'Admin_Labels_Ticket_Ctrl_Edit'
	});

	//###
	//# Round Robin
	//###
	routes.push({
		id: 'tickets.roundrobin',
		url: '/roundrobin',
		templateName: 'RoundRobin/list.html',
		controller: 'Admin_RoundRobin_Ctrl_List'
	});

	routes.push({
		id: 'tickets.roundrobin.edit',
		url: '/{id:.*}',
		templateName: 'RoundRobin/edit.html',
		controller: 'Admin_RoundRobin_Ctrl_Edit'
	});

	//###
	//# Billing
	//###
	routes.push({
		id: 'tickets.timelog_billing',
		url: '/timelog_billing',
		templateName: 'TicketSettings/timelog-billing-settings.html',
		controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
	});

	//###
	//# Email Templates
	//###
	routes.push({
		id: 'tickets.email_templates',
		url: '/email_templates',
		templateName: 'Templates/email-groups.html',
		controller: 'Admin_Templates_Ctrl_EmailGroupList'
	});

	routes.push({
		id: 'tickets.email_templates.list',
		url: '/{groupName:.*?}',
		templateName: 'Templates/email-listing.html',
		controller: 'Admin_Templates_Ctrl_EmailList'
	});

	//###
	//# Settings
	//###
	routes.push({
		id: 'tickets.settings',
		url: '/settings',
		templateName: 'TicketSettings/ticket-settings.html',
		controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
	});

	routes.push({
		id: 'tickets.fwd_settings',
		url: '/fwd-settings',
		templateName: 'TicketSettings/fwd-settings.html',
		controller: 'Admin_TicketSettings_Ctrl_FwdSettings'
	});

	//###
	//# Ticket Departments
	//###
	routes.push({
		id: 'tickets.ticket_deps',
		url: '/ticket_deps',
		templateName: 'TicketDeps/list.html',
		controller: 'Admin_TicketDeps_Ctrl_List'
	});

	routes.push({
		id: 'tickets.ticket_deps.settings',
		url: '/settings',
		templateName: 'TicketDeps/settings.html',
		controller: 'Admin_Main_Ctrl_BareList',
		target: "appbody@tickets"
	});

	routes.push({
		id: 'tickets.ticket_deps.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', function ($state) { $state.go('tickets.ticket_deps.create'); }]
	});

	routes.push({
		id: 'tickets.ticket_deps.create',
		url: '/create',
		templateName: 'TicketDeps/edit.html',
		controller: 'Admin_TicketDeps_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.ticket_deps.edit',
		url: '/{id:[0-9]+}?tab',
		templateName: 'TicketDeps/edit.html',
		controller: 'Admin_TicketDeps_Ctrl_Edit'
	});

	//###
	//# Fields
	//###
	routes.push({
		id: 'tickets.fields',
		url: '/fields',
		templateName: 'TicketFields/list.html',
		controller: 'Admin_TicketFields_Ctrl_List'
	});

	//###
	//# Ticket Categories
	//###
	routes.push({
		id: 'tickets.fields.categories',
		url: '/categories',
		templateName: 'TicketFields/Cats/ticket-cats.html',
		controller: 'Admin_TicketFields_Ctrl_EditCategories'
	});

	//###
	//# Ticket Products
	//###
	routes.push({
		id: 'tickets.fields.products',
		url: '/products',
		templateName: 'TicketFields/Prods/ticket-products.html',
		controller: 'Admin_TicketFields_Ctrl_EditProducts'
	});

	//###
	//# Ticket Workflows
	//###
	routes.push({
		id: 'tickets.fields.workflows',
		url: '/workflows',
		templateName: 'TicketFields/Works/ticket-workflows.html',
		controller: 'Admin_TicketFields_Ctrl_EditWorkflows'
	});

	//###
	//# Ticket Priorities
	//###
	routes.push({
		id: 'tickets.fields.priorities',
		url: '/priorities',
		templateName: 'TicketFields/Pris/ticket-priorities.html',
		controller: 'Admin_TicketFields_Ctrl_EditPriorities'
	});

	//###
	//# Custom Ticket Fields
	//###
	routes.push({
		id: 'tickets.fields.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.fields.create', $stateParams); }]
	});

	routes.push({
		id: 'tickets.fields.create',
		url: '/create',
		templateName: 'CustomFields/Tickets/edit.html',
		controller: 'Admin_CustomFields_Tickets_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.fields.edit',
		url: '/{id:[0-9]+}',
		templateName: 'CustomFields/Tickets/edit.html',
		controller: 'Admin_CustomFields_Tickets_Ctrl_Edit'
	});

	//###
	//# Ticket Accounts
	//###
	routes.push({
		id: 'tickets.ticket_accounts',
		url: '/ticket_accounts',
		templateName: 'TicketAccounts/list.html',
		controller: 'Admin_TicketAccounts_Ctrl_List'
	});

	routes.push({
		id: 'tickets.ticket_accounts.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', function ($state) { $state.go('tickets.ticket_accounts.create'); }]
	});

	routes.push({
		id: 'tickets.ticket_accounts.create',
		url: '/create',
		templateName: 'TicketAccounts/edit.html',
		controller: 'Admin_TicketAccounts_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.ticket_accounts.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketAccounts/edit.html',
		controller: 'Admin_TicketAccounts_Ctrl_Edit'
	});

	routes.push({
		id: 'tickets.ticket_accounts.goemailsourcesview',
		url: '/go-incoming-email/{id:[0-9]+}',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('tickets.ticket_accounts.emailsourcesview', $stateParams);
		}]
	});

	routes.push({
		id: 'tickets.ticket_accounts.emailsourcesview',
		url: '/incoming-email/{id:[0-9]+}',
		templateName: 'EmailStatus/emailsource-view.html',
		controller: 'Admin_EmailStatus_Ctrl_ViewSource',
		target: "appbody@tickets"
	});

	routes.push({
		id: 'tickets.ticket_accounts.gosendmailview',
		url: '/go-outgoing-email/{id:[0-9]+}',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('tickets.ticket_accounts.sendmailqueueview', $stateParams);
		}]
	});

	routes.push({
		id: 'tickets.ticket_accounts.sendmailqueueview',
		url: '/outgoing-email/{id:[0-9]+}',
		templateName: 'EmailStatus/sendmail-view.html',
		controller: 'Admin_EmailStatus_Ctrl_ViewSend',
		target: "appbody@tickets"
	});

	routes.push({
		id: 'tickets.ticket_accounts.emailsources',
		url: '/incoming-email',
		templateName: 'EmailStatus/emailsource-list.html',
		controller: 'Admin_EmailStatus_Ctrl_SourceList',
		target: "appbody@tickets"
	});

	routes.push({
		id: 'tickets.ticket_accounts.sendmailqueue',
		url: '/outgoing-email',
		templateName: 'EmailStatus/sendmail-list.html',
		controller: 'Admin_EmailStatus_Ctrl_SendmailList',
		target: "appbody@tickets"
	});

	routes.push({
		id: 'tickets.ticket_accounts.advancedsettings',
		url: '/advanced-settings',
		templateName: 'TicketAccounts/advanced-settings.html',
		controller: 'Admin_TicketAccounts_Ctrl_Settings',
		target: "appbody@tickets"
	});

	//##################################################################################################################
	// CRM
	//##################################################################################################################

	//###
	//# Registration
	//###
	routes.push({
		id: 'crm.reg',
		url: '/registration',
		templateName: 'UserReg/settings.html',
		controller: 'Admin_Settings_Ctrl_RegSettings'
	});

	//###
	//# Password Settings
	//###
	routes.push({
		id: 'crm.password_settings',
		url: '/password_settings',
		templateName: 'UserReg/password-settings.html',
		controller: 'Admin_Settings_Ctrl_PasswordSettings'
	});

	//###
	//# User Sources
	//###
	routes.push({
		id: 'crm.usersources',
		url: '/usersources',
		templateName: 'Usersources/list.html',
		controller: 'Admin_Usersources_Ctrl_UsersourcesList'
	});

	routes.push({
		id: 'crm.usersources.new',
		url: '/new',
		templateName: 'Usersources/new.html',
		controller: 'Admin_Usersources_Ctrl_New'
	});

	routes.push({
		id: 'crm.usersources.id',
		url: '/{id:[\\d\\w]+}',
		templateName: 'Usersources/edit-instance.html',
		controller: 'Admin_Usersources_Ctrl_EditInstance'
	});

	routes.push({
		id: 'crm.usersources.install',
		url: '/install/{name:\\w+}',
		templateName: 'Apps/package-install.html',
		controller: 'Admin_Apps_Ctrl_PackageInstall'
	});

	//###
	//# User Groups
	//###
	routes.push({
		id: 'crm.groups',
		url: '/groups',
		templateName: 'UserGroups/list.html',
		controller: 'Admin_UserGroups_Ctrl_List'
	});

	routes.push({
		id: 'crm.groups.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('crm.groups.create', $stateParams);
		}]
	});

	routes.push({
		id: 'crm.groups.create',
		url: '/create',
		templateName: 'UserGroups/edit.html',
		controller: 'Admin_UserGroups_Ctrl_Edit'
	});

	routes.push({
		id: 'crm.groups.edit',
		url: '/{id:[0-9]+}',
		templateName: 'UserGroups/edit.html',
		controller: 'Admin_UserGroups_Ctrl_Edit'
	});

	//###
	//# Fields::Users
	//###
	routes.push({
		id: 'crm.user_fields',
		url: '/user_fields',
		templateName: 'UserFields/list.html',
		controller: 'Admin_UserFields_Ctrl_List'
	});

	routes.push({
		id: 'crm.user_fields.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('crm.user_fields.create', $stateParams);
		}]
	});

	routes.push({
		id: 'crm.user_fields.create',
		url: '/create',
		templateName: 'CustomFields/User/edit.html',
		controller: 'Admin_CustomFields_User_Ctrl_Edit'
	});

	routes.push({
		id: 'crm.user_fields.edit',
		url: '/{id:[0-9]+}',
		templateName: 'CustomFields/User/edit.html',
		controller: 'Admin_CustomFields_User_Ctrl_Edit'
	});

	//###
	//# Fields::Orgs
	//###
	routes.push({
		id: 'crm.org_fields',
		url: '/org_fields',
		templateName: 'OrgFields/list.html',
		controller: 'Admin_OrgFields_Ctrl_List'
	});

	routes.push({
		id: 'crm.org_fields.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('crm.org_fields.create', $stateParams);
		}]
	});

	routes.push({
		id: 'crm.org_fields.create',
		url: '/create',
		templateName: 'CustomFields/Org/edit.html',
		controller: 'Admin_CustomFields_Org_Ctrl_Edit'
	});

	routes.push({
		id: 'crm.org_fields.edit',
		url: '/{id:[0-9]+}',
		templateName: 'CustomFields/Org/edit.html',
		controller: 'Admin_CustomFields_Org_Ctrl_Edit'
	});

	//###
	//# Rules
	//###
	routes.push({
		id: 'crm.rules',
		url: '/rules',
		templateName: 'UserRules/list.html',
		controller: 'Admin_UserRules_Ctrl_List'
	});

	routes.push({
		id: 'crm.rules.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('crm.rules.create', $stateParams);
		}]
	});

	routes.push({
		id: 'crm.rules.create',
		url: '/create',
		templateName: 'UserRules/edit.html',
		controller: 'Admin_UserRules_Ctrl_Edit'
	});

	routes.push({
		id: 'crm.rules.edit',
		url: '/{id:[0-9]+}',
		templateName: 'UserRules/edit.html',
		controller: 'Admin_UserRules_Ctrl_Edit'
	});

	//###
	//# Labels::Users
	//###

	routes.push({
		id: 'crm.user_labels',
		url: '/user_labels',
		templateName: 'Labels/Person/list.html',
		controller: 'Admin_Labels_Person_Ctrl_List'
	});

	routes.push({
		id: 'crm.user_labels.create',
		url: '/create',
		templateName: 'Labels/Person/edit.html',
		controller: 'Admin_Labels_Person_Ctrl_Edit'
	});

	routes.push({
		id: 'crm.user_labels.gocreate',
		url: '/go-create',
		templateName: 'Labels/Person/edit.html',
		controller: ['$state', function ($state) { $state.go('crm.user_labels.create'); }]
	});

	routes.push({
		id: 'crm.user_labels.edit',
		url: '/{label:.*}',
		templateName: 'Labels/Person/edit.html',
		controller: 'Admin_Labels_Person_Ctrl_Edit'
	});

	//###
	//# Labels::Orgs
	//###

	routes.push({
		id: 'crm.org_labels',
		url: '/org_labels',
		templateName: 'Labels/Org/list.html',
		controller: 'Admin_Labels_Org_Ctrl_List'
	});

	routes.push({
		id: 'crm.org_labels.create',
		url: '/create',
		templateName: 'Labels/Org/edit.html',
		controller: 'Admin_Labels_Org_Ctrl_Edit'
	});

	routes.push({
		id: 'crm.org_labels.gocreate',
		url: '/go-create',
		templateName: 'Labels/Org/edit.html',
		controller: ['$state', function ($state) { $state.go('crm.org_labels.create'); }]
	});

	routes.push({
		id: 'crm.org_labels.edit',
		url: '/{label:.*}',
		templateName: 'Labels/Org/edit.html',
		controller: 'Admin_Labels_Org_Ctrl_Edit'
	});

	//###
	//# Banning
	//###
	routes.push({
		id: 'crm.banning',
		url: '/banning',
		templateName: 'Banning/list.html',
		controller: 'Admin_Banning_Ctrl_List'
	});

	routes.push({
		id: 'crm.banning.create_ip',
		url: '/create/ip',
		templateName: 'Banning/edit-ip.html',
		controller: 'Admin_Banning_Ctrl_EditIp'
	});

	routes.push({
		id: 'crm.banning.gocreate_ip',
		url: '/go-create/ip',
		templateName: 'Banning/edit-ip.html',
		controller: ['$state', function ($state) {
			$state.go('crm.banning.create_ip');
		}]
	});

	routes.push({
		id: 'crm.banning.edit_ip',
		url: '/ip/{ban:.*}',
		templateName: 'Banning/edit-ip.html',
		controller: 'Admin_Banning_Ctrl_EditIp'
	});

	routes.push({
		id: 'crm.banning.create_email',
		url: '/create/email',
		templateName: 'Banning/edit-email.html',
		controller: 'Admin_Banning_Ctrl_EditEmail'
	});

	routes.push({
		id: 'crm.banning.gocreate_email',
		url: '/go-create/email',
		templateName: 'Banning/edit-email.html',
		controller: ['$state', function ($state) {
			$state.go('crm.banning.create_email');
		}]
	});

	routes.push({
		id: 'crm.banning.edit_email',
		url: '/email/{ban:.*}',
		templateName: 'Banning/edit-email.html',
		controller: 'Admin_Banning_Ctrl_EditEmail'
	});

	//###
	//# Import
	//###
	routes.push({
		id: 'crm.import',
		url: '/import',
		templateName: 'ImportCsv/import-csv.html',
		controller: 'Admin_ImportCsv_Ctrl_ImportCsv'
	});

	//##################################################################################################################
	// Portal
	//##################################################################################################################

	//###
	//# Portal Editor
	//###
	routes.push({
		id: 'portal.portal_editor',
		url: '/portal_editor',
		templateName: 'PortalEditor/frame.html',
		controller: 'Admin_Portal_Ctrl_PortalEditor'
	});

	//###
	//# Portal Settings
	//###
	routes.push({
		id: 'portal.settings',
		url: '/settings',
		templateName: 'Settings/portal-settings.html',
		controller: 'Admin_Settings_Ctrl_PortalSettings'
	});

	routes.push({
		id: 'portal.portal_editor_go',
		url: '/go-portal-editor',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('portal.portal_editor', {type: $stateParams.type}); }]
	});

	//###
	//# Embed
	//###
	routes.push({
		id: 'portal.embeds',
		url: '/embeds',
		templateName: 'Portal/embeds-list.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	routes.push({
		id: 'portal.embeds.type',
		url: '/{type:tab|chat|form|helpdesk}',
		templateName: 'Portal/embeds.html',
		controller: 'Admin_Portal_Ctrl_Embeds'
	});

	//###
	//# Templates
	//###
	routes.push({
		id: 'portal.templates',
		url: '/templates',
		templateName: 'Templates/groups.html',
		data: { type: 'user' },
		controller: 'Admin_Templates_Ctrl_TemplateGroupList'
	});

	routes.push({
		id: 'portal.templates.list',
		url: '/{groupName:.*?}',
		templateName: 'Templates/listing.html',
		controller: 'Admin_Templates_Ctrl_TemplateList'
	});

	//###
	//# Kb::Settings
	//###
	routes.push({
		id: 'portal.kb_settings',
		url: '/kb/settings',
		templateName: 'KbSettings/kb-settings.html',
		controller: 'Admin_KbSettings_Ctrl_KbSettings'
	});

	//###
	//# Kb::Labels
	//###
	routes.push({
		id: 'portal.kb_labels',
		url: '/kb/labels',
		templateName: 'Labels/Kb/list.html',
		controller: 'Admin_Labels_Kb_Ctrl_List'
	});

	routes.push({
		id: 'portal.kb_labels.create',
		url: '/create/',
		templateName: 'Labels/Kb/edit.html',
		controller: 'Admin_Labels_Kb_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.kb_labels.gocreate',
		url: '/go-create/',
		templateName: 'Labels/Kb/edit.html',
		controller: ['$state', function ($state) {
			$state.go('portal.kb_labels.create');
		}]
	});

	routes.push({
		id: 'portal.kb_labels.edit',
		url: '/{label:.*}/',
		templateName: 'Labels/Kb/edit.html',
		controller: 'Admin_Labels_Kb_Ctrl_Edit'
	});

	//###
	//# Downloads::Settings
	//###
	routes.push({
		id: 'portal.downloads_settings',
		url: '/downloads/settings',
		templateName: 'DownloadsSettings/downloads-settings.html',
		controller: 'Admin_DownloadsSettings_Ctrl_DownloadsSettings'
	});

	//###
	//# Downloads::Labels
	//###
	routes.push({
		id: 'portal.downloads_labels',
		url: '/downloads/labels',
		templateName: 'Labels/Downloads/list.html',
		controller: 'Admin_Labels_Downloads_Ctrl_List'
	});

	routes.push({
		id: 'portal.downloads_labels.create',
		url: '/create/',
		templateName: 'Labels/Downloads/edit.html',
		controller: 'Admin_Labels_Downloads_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.downloads_labels.gocreate',
		url: '/go-create/',
		templateName: 'Labels/Downloads/edit.html',
		controller: ['$state', function ($state) {
			$state.go('portal.downloads_labels.create');
		}]
	});

	routes.push({
		id: 'portal.downloads_labels.edit',
		url: '/{label:.*}/',
		templateName: 'Labels/Downloads/edit.html',
		controller: 'Admin_Labels_Downloads_Ctrl_Edit'
	});


	//###
	//# News::Settings
	//###
	routes.push({
		id: 'portal.news_settings',
		url: '/news/settings',
		templateName: 'NewsSettings/news-settings.html',
		controller: 'Admin_NewsSettings_Ctrl_NewsSettings'
	});

	//###
	//# News::Labels
	//###
	routes.push({
		id: 'portal.news_labels',
		url: '/news/labels',
		templateName: 'Labels/News/list.html',
		controller: 'Admin_Labels_News_Ctrl_List'
	});

	routes.push({
		id: 'portal.news_labels.create',
		url: '/create/',
		templateName: 'Labels/News/edit.html',
		controller: 'Admin_Labels_News_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.news_labels.gocreate',
		url: '/go-create/',
		templateName: 'Labels/News/edit.html',
		controller: ['$state', function ($state) {
			$state.go('portal.news_labels.create');
		}]
	});

	routes.push({
		id: 'portal.news_labels.edit',
		url: '/{label:.*}/',
		templateName: 'Labels/News/edit.html',
		controller: 'Admin_Labels_News_Ctrl_Edit'
	});

	//###
	//# Feedback::Settings
	//###
	routes.push({
		id: 'portal.feedback_settings',
		url: '/feedback/settings',
		templateName: 'FeedbackSettings/feedback-settings.html',
		controller: 'Admin_FeedbackSettings_Ctrl_FeedbackSettings'
	});

	//###
	//# Feedback::Statuses
	//###
	routes.push({
		id: 'portal.feedback_statuses',
		url: '/feedback/statuses',
		templateName: 'FeedbackStatuses/list.html',
		controller: 'Admin_FeedbackStatuses_Ctrl_List'
	});

	routes.push({
		id: 'portal.feedback_statuses.gocreate',
		url: '/go-create/{type:(?:active|closed)}',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('portal.feedback_statuses.create', {type: $stateParams.type}); }]
	});

	routes.push({
		id: 'portal.feedback_statuses.create',
		url: '/create/{type:(?:active|closed)}',
		templateName: 'FeedbackStatuses/edit.html',
		controller: 'Admin_FeedbackStatuses_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.feedback_statuses.edit',
		url: '/{id:[0-9]+}',
		templateName: 'FeedbackStatuses/edit.html',
		controller: 'Admin_FeedbackStatuses_Ctrl_Edit'
	});

	//###
	//# Feedback::Types
	//###
	routes.push({
		id: 'portal.feedback_types',
		url: '/feedback/types',
		templateName: 'FeedbackTypes/list.html',
		controller: 'Admin_FeedbackTypes_Ctrl_List'
	});

	routes.push({
		id: 'portal.feedback_types.gocreate',
		url: '/go-create/',
		template: '',
		controller: ['$state', function ($state) { $state.go('portal.feedback_types.create'); }]
	});

	routes.push({
		id: 'portal.feedback_types.create',
		url: '/create/',
		templateName: 'FeedbackTypes/edit.html',
		controller: 'Admin_FeedbackTypes_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.feedback_types.edit',
		url: '/{id:[0-9]+}',
		templateName: 'FeedbackTypes/edit.html',
		controller: 'Admin_FeedbackTypes_Ctrl_Edit'
	});

	//###
	//# Feedback::Categories
	//###
	routes.push({
		id: 'portal.feedback_categories',
		url: '/feedback/categories',
		templateName: 'FeedbackCategories/list.html',
		controller: 'Admin_FeedbackCategories_Ctrl_List'
	});

	routes.push({
		id: 'portal.feedback_categories.gocreate',
		url: '/go-create/',
		template: '',
		controller: ['$state', function ($state) { $state.go('portal.feedback_categories.create'); }]
	});

	routes.push({
		id: 'portal.feedback_categories.create',
		url: '/create/',
		templateName: 'FeedbackCategories/edit.html',
		controller: 'Admin_FeedbackCategories_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.feedback_categories.edit',
		url: '/{id:[0-9]+}',
		templateName: 'FeedbackCategories/edit.html',
		controller: 'Admin_FeedbackCategories_Ctrl_Edit'
	});

	//###
	//# Feedback::Labels
	//###
	routes.push({
		id: 'portal.feedback_labels',
		url: '/feedback/labels',
		templateName: 'Labels/Feedback/list.html',
		controller: 'Admin_Labels_Feedback_Ctrl_List'
	});

	routes.push({
		id: 'portal.feedback_labels.create',
		url: '/create/',
		templateName: 'Labels/Feedback/edit.html',
		controller: 'Admin_Labels_Feedback_Ctrl_Edit'
	});

	routes.push({
		id: 'portal.feedback_labels.gocreate',
		url: '/go-create/',
		templateName: 'Labels/Feedback/edit.html',
		controller: ['$state', function ($state) { $state.go('portal.feedback_labels.create'); }]
	});

	routes.push({
		id: 'portal.feedback_labels.edit',
		url: '/{label:.*}/',
		templateName: 'Labels/Feedback/edit.html',
		controller: 'Admin_Labels_Feedback_Ctrl_Edit'
	});

	//##################################################################################################################
	// Chat
	//##################################################################################################################

	//###
	//# Setup
	//###
	routes.push({
		id: 'chat.setup',
		url: '/setup',
		templateName: 'ChatSetup/chat-setup.html',
		controller: 'Admin_ChatSetup_Ctrl_ChatSetup'
	});

	//###
	//# Departments
	//###
	routes.push({
		id: 'chat.chat_deps',
		url: '/chat_deps',
		templateName: 'ChatDeps/list.html',
		controller: 'Admin_ChatDeps_Ctrl_List'
	});

	routes.push({
		id: 'chat.chat_deps.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', function ($state) {
			$state.go('chat.chat_deps.create');
		}]
	});

	routes.push({
		id: 'chat.chat_deps.create',
		url: '/create',
		templateName: 'ChatDeps/edit.html',
		controller: 'Admin_ChatDeps_Ctrl_Edit'
	});

	routes.push({
		id: 'chat.chat_deps.edit',
		url: '/{id:[0-9]+}',
		templateName: 'ChatDeps/edit.html',
		controller: 'Admin_ChatDeps_Ctrl_Edit'
	});

	//###
	//# Fields
	//###
	routes.push({
		id: 'chat.fields',
		url: '/fields',
		templateName: 'ChatFields/list.html',
		controller: 'Admin_ChatFields_Ctrl_List'
	});

	routes.push({
		id: 'chat.fields.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('chat.fields.create', $stateParams);
		}]
	});

	routes.push({
		id: 'chat.fields.create',
		url: '/create',
		templateName: 'CustomFields/Chat/edit.html',
		controller: 'Admin_CustomFields_Chat_Ctrl_Edit'
	});

	routes.push({
		id: 'chat.fields.edit',
		url: '/{id:[0-9]+}',
		templateName: 'CustomFields/Chat/edit.html',
		controller: 'Admin_CustomFields_Chat_Ctrl_Edit'
	});

	//###
	//# Chat::Labels
	//###

	routes.push({
		id: 'chat.labels',
		url: '/labels',
		templateName: 'Labels/Chat/list.html',
		controller: 'Admin_Labels_Chat_Ctrl_List'
	});

	routes.push({
		id: 'chat.labels.create',
		url: '/create/',
		templateName: 'Labels/Chat/edit.html',
		controller: 'Admin_Labels_Chat_Ctrl_Edit'
	});

	routes.push({
		id: 'chat.labels.gocreate',
		url: '/go-create/',
		templateName: 'Labels/Chat/edit.html',
		controller: ['$state', function ($state) {
			$state.go('chat.labels.create');
		}]
	});

	routes.push({
		id: 'chat.labels.edit',
		url: '/{label:.*}/',
		templateName: 'Labels/Chat/edit.html',
		controller: 'Admin_Labels_Chat_Ctrl_Edit'
	});


	//##################################################################################################################
	// Twitter
	//##################################################################################################################

	//###
	//# Setup
	//###
	routes.push({
		id: 'twitter.setup',
		url: '/setup',
		templateName: 'TwitterSetup/twitter-setup.html',
		controller: 'Admin_TwitterSetup_Ctrl_TwitterSetup'
	});

	//###
	//# Accounts
	//###

	routes.push({
		id: 'twitter.accounts',
		url: '/accounts',
		templateName: 'TwitterAccounts/list.html',
		controller: 'Admin_TwitterAccounts_Ctrl_List'
	});

	routes.push({
		id: 'twitter.accounts.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('twitter.accounts.create', $stateParams);
		}]
	});

	routes.push({
		id: 'twitter.accounts.create',
		url: '/create',
		templateName: 'TwitterAccounts/edit.html',
		controller: 'Admin_TwitterAccounts_Ctrl_Edit'
	});

	routes.push({
		id: 'twitter.accounts.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TwitterAccounts/edit.html',
		controller: 'Admin_TwitterAccounts_Ctrl_Edit'
	});

	//##################################################################################################################
	// Apps
	//##################################################################################################################

	//###
	//# Apps
	//###
	routes.push({
		id: 'apps.apps',
		url: '/apps',
		templateName: 'Apps/list.html',
		controller: 'Admin_Apps_Ctrl_List'
	});

	routes.push({
		id: 'apps.go_apps',
		url: '/go-apps',
		templateName: 'Index/blank.html',
		controller: ['$state', function ($state) { $state.go('apps.apps'); }]
	});

	routes.push({
		id: 'apps.go_apps_install',
		url: '/{name:go\\-apps\\-(.*?)}',
		templateName: 'Index/blank.html',
		controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('apps.apps.install_package', { name: $stateParams.name.replace(/^go\-apps\-/, '') + '.install' }); }]
	});

	routes.push({
		id: 'apps.resync',
		url: '/resync',
		templateName: 'Apps/apps_resync.html',
		controller: 'Admin_Apps_Ctrl_Resync'
	});

	routes.push({
		id: 'apps.apps.instance',
		url: '/{id:\\d+}',
		templateName: 'Apps/instance.html',
		controller: 'Admin_Apps_Ctrl_EditInstance'
	});

	routes.push({
		id: 'apps.apps.custom_instance',
		url: '/{custom_id:custom_\\d+}',
		templateName: 'Apps/custom-instance.html',
		controller: 'Admin_Apps_Ctrl_EditCustomInstance'
	});

	routes.push({
		id: 'apps.apps.install_package',
		url: '/{name:[a-zA-Z0-9\\-_\\.]+\.install$}',
		templateName: 'Apps/package-install.html',
		controller: 'Admin_Apps_Ctrl_PackageInstall'
	});

	routes.push({
		id: 'apps.apps.package',
		url: '/{name:[a-zA-Z0-9\\-_\\.]+}',
		templateName: 'Apps/package.html',
		controller: 'Admin_Apps_Ctrl_PackageInfo'
	});

	//###
	//# API Keys
	//###
	routes.push({
		id: 'apps.api_keys',
		url: '/api_keys',
		templateName: 'ApiKeys/list.html',
		controller: 'Admin_ApiKeys_Ctrl_List'
	});

	routes.push({
		id: 'apps.api_keys.gocreate',
		url: '/go-create',
		template: '',
		controller: ['$state', '$stateParams', function ($state, $stateParams) {
			$state.go('apps.api_keys.create', $stateParams);
		}]
	});

	routes.push({
		id: 'apps.api_keys.create',
		url: '/create',
		templateName: 'ApiKeys/edit.html',
		controller: 'Admin_ApiKeys_Ctrl_Edit'
	});

	routes.push({
		id: 'apps.api_keys.edit',
		url: '/{id:[0-9]+}',
		templateName: 'ApiKeys/edit.html',
		controller: 'Admin_ApiKeys_Ctrl_Edit'
	});

	//##################################################################################################################
	// Tasks
	//##################################################################################################################
	routes.push({
		id: 'tasks.settings',
		url: '/settings',
		templateName: 'Tasks/settings.html',
		controller: 'Admin_Tasks_Ctrl_Edit'
	});

	//##################################################################################################################
	// Server
	//##################################################################################################################

	//###
	//# Server Settingss
	//###
	routes.push({
		id: 'server.server_settings',
		url: '/settings',
		templateName: 'Settings/server-settings.html',
		controller: 'Admin_Settings_Ctrl_ServerSettings'
	});

	//###
	//# Elastic Search
	//###
	routes.push({
		id: 'server.elastic_search',
		url: '/settings_elastic_search',
		templateName: 'ElasticSearch/setup.html',
		controller: 'Admin_Settings_Ctrl_ElasticSearch'
	});

	//###
	//# Server Requirements
	//###
	routes.push({
		id: 'server.server_reqs',
		url: '/server_reqs',
		templateName: 'Server/server-reqs.html',
		controller: 'Admin_ServerReqs_Ctrl_ServerReqs'
	});

	//###
	//# Check File Integrity
	//###
	routes.push({
		id: 'server.file_check',
		url: '/file_check',
		templateName: 'Server/server-file-check.html',
		controller: 'Admin_ServerFileCheck_Ctrl_ServerFileCheck'
	});

	//###
	//# Test File Uploads
	//###
	routes.push({
		id: 'server.file_uploads',
		url: '/file_uploads',
		templateName: 'Server/server-file-uploads.html',
		controller: 'Admin_ServerFileUploads_Ctrl_ServerFileUploads'
	});

	//###
	//# Cron
	//###
	routes.push({
		id: 'server.cron',
		url: '/cron',
		templateName: 'Server/server-cron-list.html',
		controller: 'Admin_ServerCron_Ctrl_List'
	});

	routes.push({
		id: 'server.cron.logs',
		url: '/logs',
		templateName: 'Server/server-cron-logs.html',
		controller: 'Admin_ServerCron_Ctrl_Logs',
		target: "appbody@server"
	});

	//###
	//# PHP Info
	//###
	routes.push({
		id: 'server.php_info',
		url: '/php_info',
		templateName: 'Server/server-php-info.html',
		controller: 'Admin_ServerPhpInfo_Ctrl_ServerPhpInfo'
	});

	//###
	//# MySQL Info
	//###
	routes.push({
		id: 'server.mysql_info',
		url: '/mysql_info',
		templateName: 'Server/server-mysql-info.html',
		controller: 'Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo'
	});

	//###
	//# MySQL Status
	//###
	routes.push({
		id: 'server.mysql_status',
		url: '/mysql_status',
		templateName: 'Server/server-mysql-status.html',
		controller: 'Admin_ServerMysqlStatus_Ctrl_ServerMysqlStatus'
	});

	//###
	//# Test Email
	//###
	routes.push({
		id: 'server.test_email',
		url: '/test_email',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Update MySQL Sort Order
	//###
	routes.push({
		id: 'server.mysql_sort_order',
		url: '/mysql_sort_order',
		templateName: 'Server/server-mysql-sort-order.html',
		controller: 'Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder'
	});

	//###
	//# Error Logs
	//###
	routes.push({
		id: 'server.error_logs',
		url: '/error_logs',
		templateName: 'Server/server-error-logs.html',
		controller: 'Admin_ServerErrorLogs_Ctrl_ServerErrorLogs'
	});

	routes.push({
		id: 'server.error_logs.view',
		url: '/view/{id}',
		templateName: 'Server/server-error-logs-view.html',
		controller: 'Admin_ServerErrorLogs_Ctrl_View',
		target: "appbody@server"
	});

	//###
	//# Sendmail Queue
	//###
	routes.push({
		id: 'server.sendmail_queue',
		url: '/sendmail_queue',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Task Queue Logs
	//###
	routes.push({
		id: 'server.task_queue',
		url: '/task_queue',
		templateName: 'Server/server-task-queue.html',
		controller: 'Admin_ServerTaskQueue_Ctrl_ServerTaskQueue'
	});

	//###
	//# Report File
	//###
	routes.push({
		id: 'server.report_file',
		url: '/report_file',
		templateName: 'Server/server-report-file.html',
		controller: 'Admin_ServerReportFile_Ctrl_ServerReportFile'
	});

	return routes;
});