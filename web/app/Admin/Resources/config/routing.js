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
	// Main Nav
	//##################################################################################################################

	routes.push({
		id: 'home',
		url: '/',
		templateName: 'Index/home.html',
		controller: 'Admin_Main_Ctrl_NavSetup'
	});

	routes.push({
		id: 'setup',
		url: '/setup',
		templateName: 'Index/app-nav-setup.html',
		controller: 'Admin_Main_Ctrl_NavSetup'
	});

	routes.push({
		id: 'agents',
		url: '/agents',
		templateName: 'Index/app-nav-agents.html',
		controller: 'Admin_Main_Ctrl_NavAgents'
	});

	routes.push({
		id: 'tickets',
		url: '/tickets',
		templateName: 'Index/app-nav-tickets.html',
		controller: 'Admin_Main_Ctrl_NavTickets'
	});

	routes.push({
		id: 'crm',
		url: '/crm',
		templateName: 'Index/app-nav-crm.html',
		controller: 'Admin_Main_Ctrl_NavCrm'
	});

	routes.push({
		id: 'portal',
		url: '/portal',
		templateName: 'Index/app-nav-portal.html',
		controller: 'Admin_Main_Ctrl_NavPortal'
	});

	routes.push({
		id: 'chat',
		url: '/chat',
		templateName: 'Index/app-nav-chat.html',
		controller: 'Admin_Main_Ctrl_NavChat'
	});

	routes.push({
		id: 'twitter',
		url: '/twitter',
		templateName: 'Index/app-nav-twitter.html',
		controller: 'Admin_Main_Ctrl_NavTwitter'
	});

	routes.push({
		id: 'apps',
		url: '/apps',
		templateName: 'Index/app-nav-apps.html',
		controller: 'Admin_Main_Ctrl_NavApps'
	});

	routes.push({
		id: 'server',
		url: '/server',
		templateName: 'Index/app-nav-server.html',
		controller: 'Admin_Main_Ctrl_NavServer'
	});

	//##################################################################################################################
	// Back to agent
	//##################################################################################################################

	routes.push({
		id: 'back_to_agent',
		url: '/back_to_agent',
		templateName: 'Index/back-to-agent.html',
		controller: 'Admin_Main_Ctrl_BackToAgent'
	});

	//##################################################################################################################
	// Setup
	//##################################################################################################################

	//###
	//# Setup
	//###
	routes.push({
		id: 'setup.settings',
		url: '/settings',
		with_list_view: false,
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	//###
	//# Languages
	//###
	routes.push({
		id: 'setup.languages',
		url: '/languages',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Teams
	//###
	routes.push({
		id: 'agents.teams',
		url: '/teams',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Permission Groups
	//###
	routes.push({
		id: 'agents.groups',
		url: '/groups',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		id: 'tickets.triggers.edit',
		url: '/{id:[0-9]+}',
		templateName: 'TicketTriggers/edit.html',
		controller: 'Admin_TicketTriggers_Ctrl_Edit'
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
		page: {
			template: '',
			controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.macros.create', $stateParams); }]
		}
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
		with_list_view: false,
		templateName: 'TicketSettings/satisfaction-settings.html',
		controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
	});

	//###
	//# Escalations
	//###
	routes.push({
		id: 'tickets.ticket_escalations',
		url: '/ticket_escalations',
		with_list_view: true,
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		url: '/{id:[0-9]+}',
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

	//##################################################################################################################
	// CRM
	//##################################################################################################################

	//###
	//# Registration
	//###
	routes.push({
		id: 'crm.reg',
		url: '/reg',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	//###
	//# Usersources
	//###
	routes.push({
		id: 'crm.usersources',
		url: '/usersources',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# User Groups
	//###
	routes.push({
		id: 'crm.groups',
		url: '/groups',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Fields::Users
	//###
	routes.push({
		id: 'crm.user_fields',
		url: '/user_fields',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Fields::Orgs
	//###
	routes.push({
		id: 'crm.org_fields',
		url: '/org_fields',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Rules
	//###
	routes.push({
		id: 'crm.rules',
		url: '/rules',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		with_list_view: true,
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Import
	//###
	routes.push({
		id: 'crm.import',
		url: '/import',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_Bare'
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	//###
	//# Embed
	//###
	routes.push({
		id: 'portal.embed',
		url: '/embed',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	//###
	//# Theme
	//###
	routes.push({
		id: 'portal.theme',
		url: '/theme',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Settings
	//###
	routes.push({
		id: 'portal.settings',
		url: '/theme',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Kb::Settings
	//###
	routes.push({
		id: 'portal.kb_settings',
		url: '/kb/settings',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Kb::Labels
	//###
	routes.push({
		id: 'portal.kb_labels',
		url: '/kb/labels',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Downloads::Settings
	//###
	routes.push({
		id: 'portal.downloads_settings',
		url: '/downloads/settings',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Downloads::Labels
	//###
	routes.push({
		id: 'portal.downloads_labels',
		url: '/downloads/labels',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# News::Settings
	//###
	routes.push({
		id: 'portal.news_settings',
		url: '/news/settings',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# News::Labels
	//###
	routes.push({
		id: 'portal.news_labels',
		url: '/news/labels',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Feedback::Settings
	//###
	routes.push({
		id: 'portal.feedback_settings',
		url: '/feedback/settings',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		with_list_view: true,
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_Bare'
	});

	//###
	//# Departments
	//###
	routes.push({
		id: 'chat.chat_deps',
		url: '/chat_deps',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Fields
	//###
	routes.push({
		id: 'chat.fields',
		url: '/fields',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Labels
	//###
	routes.push({
		id: 'chat.labels',
		url: '/labels',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# API Keys
	//###
	routes.push({
		id: 'apps.api_keys',
		url: '/api_keys',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//##################################################################################################################
	// Server
	//##################################################################################################################

	//###
	//# Server Requirements
	//###
	routes.push({
		id: 'server.server_reqs',
		url: '/server_reqs',
		templateName: 'ServerReqs/server-reqs.html',
		controller: 'Admin_ServerReqs_Ctrl_ServerReqs'
	});

	//###
	//# Check File Integrity
	//###
	routes.push({
		id: 'server.file_check',
		url: '/file_check',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Test File Uploads
	//###
	routes.push({
		id: 'server.test_file_ups',
		url: '/test_file_ups',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Cron
	//###
	routes.push({
		id: 'server.cron',
		url: '/cron',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# PHP Info
	//###
	routes.push({
		id: 'server.php_info',
		url: '/php_info',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# MySQL Info
	//###
	routes.push({
		id: 'server.mysql_info',
		url: '/mysql_info',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# MySQL Status
	//###
	routes.push({
		id: 'server.mysql_status',
		url: '/mysql_status',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	//###
	//# Error Logs
	//###
	routes.push({
		id: 'server.error_logs',
		url: '/error_logs',
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
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
		templateName: 'Index/blank.html',
		controller: 'Admin_Main_Ctrl_BareList'
	});

	return routes;
});