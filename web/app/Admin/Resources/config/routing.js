define(function() {
	'use strict';
	var routes = [];

	//##################################################################################################################
	// Dev Nav
	//##################################################################################################################

	routes.push({
		id: 'dev_ui',
		url: '/dev_ui',
		with_list_view: false,
		with_nav_view: false,
		page: {
			templateName: 'Index/dev-ui.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	routes.push({
		id: 'dev_ui.table',
		url: '/table',
		with_list_view: false,
		with_nav_view: false,
		page: {
			templateName: 'Index/dev-ui-table.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//##################################################################################################################
	// Main Nav
	//##################################################################################################################

	routes.push({
		id: 'home',
		url: '/',
		with_list_view: false,
		with_nav_view: false,
		page: {
			templateName: 'Index/home.html',
			controller: 'Admin_Main_Ctrl_NavSetup'
		}
	});

	routes.push({
		id: 'setup',
		url: '/setup',
		nav: {
			templateName: 'Index/app-nav-setup.html',
			controller: 'Admin_Main_Ctrl_NavSetup'
		}
	});

	routes.push({
		id: 'agents',
		url: '/agents',
		nav: {
			templateName: 'Index/app-nav-agents.html',
			controller: 'Admin_Main_Ctrl_NavAgents'
		}
	});

	routes.push({
		id: 'tickets',
		url: '/tickets',
		nav: {
			templateName: 'Index/app-nav-tickets.html',
			controller: 'Admin_Main_Ctrl_NavTickets'
		}
	});

	routes.push({
		id: 'crm',
		url: '/crm',
		nav: {
			templateName: 'Index/app-nav-crm.html',
			controller: 'Admin_Main_Ctrl_NavCrm'
		}
	});

	routes.push({
		id: 'portal',
		url: '/portal',
		nav: {
			templateName: 'Index/app-nav-portal.html',
			controller: 'Admin_Main_Ctrl_NavPortal'
		}
	});

	routes.push({
		id: 'chat',
		url: '/chat',
		nav: {
			templateName: 'Index/app-nav-chat.html',
			controller: 'Admin_Main_Ctrl_NavChat'
		}
	});

	routes.push({
		id: 'twitter',
		url: '/twitter',
		nav: {
			templateName: 'Index/app-nav-twitter.html',
			controller: 'Admin_Main_Ctrl_NavTwitter'
		}
	});

	routes.push({
		id: 'apps',
		url: '/apps',
		nav: {
			templateName: 'Index/app-nav-apps.html',
			controller: 'Admin_Main_Ctrl_NavApps'
		}
	});

	routes.push({
		id: 'server',
		url: '/server',
		nav: {
			templateName: 'Index/app-nav-server.html',
			controller: 'Admin_Main_Ctrl_NavServer'
		}
	});

	//##################################################################################################################
	// Back to agent
	//##################################################################################################################

	routes.push({
		id: 'back_to_agent',
		url: '/back_to_agent',
		nav: {
			templateName: 'Index/back-to-agent.html',
			controller: 'Admin_Main_Ctrl_BackToAgent'
		}
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
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Languages
	//###
	routes.push({
		id: 'setup.languages',
		url: '/languages',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Outgoing Email
	//###
	routes.push({
		id: 'setup.setup',
		url: '/setup',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
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
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Teams
	//###
	routes.push({
		id: 'agents.teams',
		url: '/teams',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Permission Groups
	//###
	routes.push({
		id: 'agents.groups',
		url: '/groups',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//##################################################################################################################
	// Tickets
	//##################################################################################################################

	//###
	//# Email Accounts
	//###
	routes.push({
		id: 'tickets.email_accounts',
		url: '/email_accounts',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Statuses
	//###
	routes.push({
		id: 'tickets.statuses',
		url: '/statuses',
		with_list_view: true,
		list: {
			templateName: 'TicketStatuses/list.html',
			controller: 'Admin_TicketStatuses_Ctrl_List'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	routes.push({
		id: 'tickets.statuses.awaiting_agent',
		url: '/statuses/awaiting_agent',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-awaiting-agent.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditAwaitingAgent'
		}
	});

	routes.push({
		id: 'tickets.statuses.awaiting_user',
		url: '/statuses/awaiting_user',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-awaiting-user.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditAwaitingUser'
		}
	});

	routes.push({
		id: 'tickets.statuses.resolved',
		url: '/statuses/resolved',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-resolved.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditResolved'
		}
	});

	routes.push({
		id: 'tickets.statuses.closed',
		url: '/statuses/closed',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-closed.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditClosed'
		}
	});

	routes.push({
		id: 'tickets.statuses.hidden_validating',
		url: '/statuses/validating',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-hidden-validating.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditHiddenValidating'
		}
	});

	routes.push({
		id: 'tickets.statuses.hidden_deleted',
		url: '/statuses/deleted',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-hidden-deleted.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditHiddenDeleted'
		}
	});

	routes.push({
		id: 'tickets.statuses.hidden_spam',
		url: '/statuses/spam',
		with_list_view: true,
		page: {
			templateName: 'TicketStatuses/status-hidden-spam.html',
			controller: 'Admin_TicketStatuses_Ctrl_EditHiddenSpam'
		}
	});

	//###
	//# Urgency
	//###
	routes.push({
		id: 'tickets.urgency',
		url: '/urgency',
		with_list_view: true,
		list: {
			templateName: 'TicketUrgencies/list.html',
			controller: 'Admin_TicketUrgencies_Ctrl_List'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Triggers
	//###
	routes.push({
		id: 'tickets.triggers',
		url: '/triggers/{type:(?:newticket|newreply|update)}',
		with_list_view: true,
		list: {
			templateName: 'TicketTriggers/list.html',
			controller: 'Admin_TicketTriggers_Ctrl_List'
		}
	});

	routes.push({
		id: 'tickets.triggers.gocreate',
		url: '/go-create',
		page: {
			template: '',
			controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.triggers.create', $stateParams); }]
		}
	});

	routes.push({
		id: 'tickets.triggers.create',
		url: '/create',
		page: {
			templateName: 'TicketTriggers/edit.html',
			controller: 'Admin_TicketTriggers_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.triggers.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketTriggers/edit.html',
			controller: 'Admin_TicketTriggers_Ctrl_Edit'
		}
	});


	//###
	//# Snippets
	//###
	routes.push({
		id: 'tickets.snippets',
		url: '/snippets',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Macros
	//###
	routes.push({
		id: 'tickets.macros',
		url: '/macros',
		with_list_view: true,
		list: {
			templateName: 'TicketMacros/list.html',
			controller: 'Admin_TicketMacros_Ctrl_List'
		}
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
		page: {
			templateName: 'TicketMacros/edit.html',
			controller: 'Admin_TicketMacros_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.macros.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketMacros/edit.html',
			controller: 'Admin_TicketMacros_Ctrl_Edit'
		}
	});

	//###
	//# Filters
	//###
	routes.push({
		id: 'tickets.filters',
		url: '/filters',
		with_list_view: true,
		list: {
			templateName: 'TicketFilters/list.html',
			controller: 'Admin_TicketFilters_Ctrl_List'
		}
	});

	routes.push({
		id: 'tickets.filters.gocreate',
		url: '/go-create',
		page: {
			template: '',
			controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.filters.create', $stateParams); }]
		}
	});

	routes.push({
		id: 'tickets.filters.create',
		url: '/create',
		page: {
			templateName: 'TicketFilters/edit.html',
			controller: 'Admin_TicketFilters_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.filters.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketFilters/edit.html',
			controller: 'Admin_TicketFilters_Ctrl_Edit'
		}
	});

	//###
	//# Satisfaction
	//###
	routes.push({
		id: 'tickets.satisfaction',
		url: '/satisfaction',
		with_list_view: false,
		page: {
			templateName: 'TicketSettings/satisfaction-settings.html',
			controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
		}
	});

	//###
	//# Escalations
	//###
	routes.push({
		id: 'tickets.escalations',
		url: '/escalations',
		with_list_view: true,
		list: {
			templateName: 'TicketEscalations/list.html',
			controller: 'Admin_TicketEscalations_Ctrl_List'
		}
	});

	routes.push({
		id: 'tickets.escalations.gocreate',
		url: '/go-create',
		page: {
			template: '',
			controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.escalations.create', $stateParams); }]
		}
	});

	routes.push({
		id: 'tickets.escalations.create',
		url: '/create',
		page: {
			templateName: 'TicketEscalations/edit.html',
			controller: 'Admin_TicketEscalations_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.escalations.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketEscalations/edit.html',
			controller: 'Admin_TicketEscalations_Ctrl_Edit'
		}
	});

	//###
	//# SLAs
	//###
	routes.push({
		id: 'tickets.slas',
		url: '/slas',
		with_list_view: true,
		list: {
			templateName: 'TicketSlas/list.html',
			controller: 'Admin_TicketSlas_Ctrl_List'
		}
	});

	routes.push({
		id: 'tickets.slas.gocreate',
		url: '/go-create',
		page: {
			template: '',
			controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('tickets.slas.create', $stateParams); }]
		}
	});

	routes.push({
		id: 'tickets.slas.create',
		url: '/create',
		page: {
			templateName: 'TicketSlas/edit.html',
			controller: 'Admin_TicketSlas_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.slas.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketSlas/edit.html',
			controller: 'Admin_TicketSlas_Ctrl_Edit'
		}
	});

	//###
	//# Labels
	//###
	routes.push({
		id: 'tickets.labels',
		url: '/labels',
		with_list_view: true,
		list: {
			templateName: 'Labels/Ticket/list.html',
			controller: 'Admin_Labels_Ticket_Ctrl_List'
		},
		page: {
		  templateName: 'Index/blank.html',
		  controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	routes.push({
		id: 'tickets.labels.create',
		url: '/create',
		page: {
			templateName: 'Labels/Ticket/edit.html',
			controller: 'Admin_Labels_Ticket_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.labels.gocreate',
		url: '/go-create',
		page: {
			templateName: 'Labels/Ticket/edit.html',
			controller: ['$state', function ($state) { $state.go('tickets.labels.create'); }]
		}
	});

	routes.push({
		id: 'tickets.labels.edit',
		url: '/{label:.*}',
		page: {
			templateName: 'Labels/Ticket/edit.html',
			controller: 'Admin_Labels_Ticket_Ctrl_Edit'
		}
	});

	//###
	//# Billing
	//###
	routes.push({
		id: 'tickets.timelog_billing',
		url: '/timelog_billing',
		page: {
			templateName: 'TicketSettings/timelog-billing-settings.html',
			controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
		}
	});

	//###
	//# Email Templates
	//###
	routes.push({
		id: 'tickets.email_templates',
		url: '/email_templates',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Settings
	//###
	routes.push({
		id: 'tickets.settings',
		url: '/settings',
		with_list_view: false,
		page: {
			templateName: 'TicketSettings/ticket-settings.html',
			controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
		}
	});

	//###
	//# Ticket Departments
	//###
	routes.push({
		id: 'tickets.ticket_deps',
		url: '/ticket_deps',
		with_list_view: true,
		list: {
			templateName: 'TicketDeps/list.html',
			controller: 'Admin_TicketDeps_Ctrl_List'
		},
		page: {
			templateName: 'TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.ticket_deps.gocreate',
		url: '/go-create',
		page: {
			template: '',
			controller: ['$state', function ($state) { $state.go('tickets.ticket_deps.create'); }]
		}
	});

	routes.push({
		id: 'tickets.ticket_deps.create',
		url: '/create',
		page: {
			templateName: 'TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.ticket_deps.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	//###
	//# Fields
	//###
	routes.push({
		id: 'tickets.fields',
		url: '/fields',
		with_list_view: true,
		list: {
			templateName: 'TicketFields/list.html',
			controller: 'Admin_TicketFields_Ctrl_List'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Ticket Categories
	//###
	routes.push({
		id: 'tickets.fields.categories',
		url: '/categories',
		page: {
			templateName: 'TicketFields/Cats/ticket-cats.html',
			controller: 'Admin_TicketFields_Ctrl_EditCategories'
		}
	});

	//###
	//# Ticket Products
	//###
	routes.push({
		id: 'tickets.fields.products',
		url: '/products',
		page: {
			templateName: 'TicketFields/Prods/ticket-products.html',
			controller: 'Admin_TicketFields_Ctrl_EditProducts'
		}
	});

	//###
	//# Ticket Workflows
	//###
	routes.push({
		id: 'tickets.fields.workflows',
		url: '/workflows',
		page: {
			templateName: 'TicketFields/Works/ticket-workflows.html',
			controller: 'Admin_TicketFields_Ctrl_EditWorkflows'
		}
	});

	//###
	//# Ticket Priorities
	//###
	routes.push({
		id: 'tickets.fields.priorities',
		url: '/priorities',
		page: {
			templateName: 'TicketFields/Pris/ticket-priorities.html',
			controller: 'Admin_TicketFields_Ctrl_EditPriorities'
		}
	});

	//###
	//# Ticket Accounts
	//###
	routes.push({
		id: 'tickets.ticket_accounts',
		url: '/ticket_accounts',
		with_list_view: true,
		list: {
			templateName: 'TicketAccounts/list.html',
			controller: 'Admin_TicketAccounts_Ctrl_List'
		},
		page: {
			templateName: 'TicketAccounts/edit.html',
			controller: 'Admin_TicketAccounts_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.ticket_accounts.gocreate',
		url: '/go-create',
		page: {
			template: '',
			controller: ['$state', function ($state) { $state.go('tickets.ticket_accounts.create'); }]
		}
	});

	routes.push({
		id: 'tickets.ticket_accounts.create',
		url: '/create',
		page: {
			templateName: 'TicketAccounts/edit.html',
			controller: 'Admin_TicketAccounts_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.ticket_accounts.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'TicketAccounts/edit.html',
			controller: 'Admin_TicketAccounts_Ctrl_Edit'
		}
	});

	//##################################################################################################################
	// Email Status
	//##################################################################################################################

	routes.push({
		id: 'tickets.emailsources',
		url: '/incoming-email',
		with_list_view: false,
		page: {
			templateName: 'EmailStatus/emailsource-list.html',
			controller: 'Admin_EmailStatus_Ctrl_SourceList'
		}
	});

	routes.push({
		id: 'tickets.sendmailqueue',
		url: '/outgoing-email',
		with_list_view: false,
		page: {
			templateName: 'EmailStatus/sendmail-list.html',
			controller: 'Admin_EmailStatus_Ctrl_SendmailList'
		}
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
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Usersources
	//###
	routes.push({
		id: 'crm.usersources',
		url: '/usersources',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# User Groups
	//###
	routes.push({
		id: 'crm.groups',
		url: '/groups',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Fields::Users
	//###
	routes.push({
		id: 'crm.user_fields',
		url: '/user_fields',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Fields::Orgs
	//###
	routes.push({
		id: 'crm.org_fields',
		url: '/org_fields',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Rules
	//###
	routes.push({
		id: 'crm.rules',
		url: '/rules',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Labels::Users
	//###

	routes.push({
		id: 'crm.user_labels',
		url: '/user_labels',
		with_list_view: true,
		list: {
			templateName: 'Labels/Person/list.html',
			controller: 'Admin_Labels_Person_Ctrl_List'
		},
		page: {
		  templateName: 'Index/blank.html',
		  controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	routes.push({
		id: 'crm.user_labels.create',
		url: '/create',
		page: {
			templateName: 'Labels/Person/edit.html',
			controller: 'Admin_Labels_Person_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'crm.user_labels.gocreate',
		url: '/go-create',
		page: {
			templateName: 'Labels/Person/edit.html',
			controller: ['$state', function ($state) { $state.go('crm.user_labels.create'); }]
		}
	});

	routes.push({
		id: 'crm.user_labels.edit',
		url: '/{label:.*}',
		page: {
			templateName: 'Labels/Person/edit.html',
			controller: 'Admin_Labels_Person_Ctrl_Edit'
		}
	});

	//###
	//# Labels::Orgs
	//###

	routes.push({
		id: 'crm.org_labels',
		url: '/org_labels',
		with_list_view: true,
		list: {
			templateName: 'Labels/Org/list.html',
			controller: 'Admin_Labels_Org_Ctrl_List'
		},
		page: {
		  templateName: 'Index/blank.html',
		  controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	routes.push({
		id: 'crm.org_labels.create',
		url: '/create',
		page: {
			templateName: 'Labels/Org/edit.html',
			controller: 'Admin_Labels_Org_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'crm.org_labels.gocreate',
		url: '/go-create',
		page: {
			templateName: 'Labels/Org/edit.html',
			controller: ['$state', function ($state) { $state.go('crm.org_labels.create'); }]
		}
	});

	routes.push({
		id: 'crm.org_labels.edit',
		url: '/{label:.*}',
		page: {
			templateName: 'Labels/Org/edit.html',
			controller: 'Admin_Labels_Org_Ctrl_Edit'
		}
	});

	//###
	//# Banning
	//###
	routes.push({
		id: 'crm.banning',
		url: '/banning',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Import
	//###
	routes.push({
		id: 'crm.import',
		url: '/import',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
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
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Embed
	//###
	routes.push({
		id: 'portal.embed',
		url: '/embed',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Theme
	//###
	routes.push({
		id: 'portal.theme',
		url: '/theme',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Settings
	//###
	routes.push({
		id: 'portal.settings',
		url: '/theme',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Kb::Settings
	//###
	routes.push({
		id: 'portal.kb_settings',
		url: '/kb/settings',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Kb::Labels
	//###
	routes.push({
		id: 'portal.kb_labels',
		url: '/kb/labels',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Downloads::Settings
	//###
	routes.push({
		id: 'portal.downloads_settings',
		url: '/downloads/settings',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Downloads::Labels
	//###
	routes.push({
		id: 'portal.downloads_labels',
		url: '/downloads/labels',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# News::Settings
	//###
	routes.push({
		id: 'portal.news_settings',
		url: '/news/settings',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# News::Labels
	//###
	routes.push({
		id: 'portal.news_labels',
		url: '/news/labels',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Feedback::Settings
	//###
	routes.push({
		id: 'portal.feedback_settings',
		url: '/feedback/settings',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Feedback::Statuses
	//###
	routes.push({
		id: 'portal.feedback_statuses',
		url: '/feedback/statuses',
		with_list_view: true,
		list: {
            templateName: 'FeedbackStatuses/list.html',
            controller: 'Admin_FeedbackStatuses_Ctrl_List'
		}
	});

	routes.push({
		id: 'portal.feedback_statuses.gocreate',
		url: '/go-create/{type:(?:active|closed)}',
		page: {
			template: '',
			controller: ['$state', '$stateParams', function ($state, $stateParams) {
				$state.go('portal.feedback_statuses.create', {type: $stateParams.type});
			}]
		}
	});

	routes.push({
		id: 'portal.feedback_statuses.create',
		url: '/create/{type:(?:active|closed)}',
		page: {
			templateName: 'FeedbackStatuses/edit.html',
			controller: 'Admin_FeedbackStatuses_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'portal.feedback_statuses.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'FeedbackStatuses/edit.html',
			controller: 'Admin_FeedbackStatuses_Ctrl_Edit'
		}
	});

	//###
	//# Feedback::Types
	//###
	routes.push({
		id: 'portal.feedback_types',
		url: '/feedback/types',
		with_list_view: true,
		list: {
			templateName: 'FeedbackTypes/list.html',
			controller: 'Admin_FeedbackTypes_Ctrl_List'
		}
	});

	routes.push({
		id: 'portal.feedback_types.gocreate',
		url: '/go-create/',
		page: {
			template: '',
			controller: ['$state', function ($state) {
				$state.go('portal.feedback_types.create');
			}]
		}
	});

	routes.push({
		id: 'portal.feedback_types.create',
		url: '/create/',
		page: {
			templateName: 'FeedbackTypes/edit.html',
			controller: 'Admin_FeedbackTypes_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'portal.feedback_types.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'FeedbackTypes/edit.html',
			controller: 'Admin_FeedbackTypes_Ctrl_Edit'
		}
	});

	//###
	//# Feedback::Categories
	//###
	routes.push({
		id: 'portal.feedback_categories',
		url: '/feedback/categories',
		with_list_view: true,
		list: {
			templateName: 'FeedbackCategories/list.html',
			controller: 'Admin_FeedbackCategories_Ctrl_List'
		}
	});

	routes.push({
		id: 'portal.feedback_categories.gocreate',
		url: '/go-create/',
		page: {
			template: '',
			controller: ['$state', function ($state) {
				$state.go('portal.feedback_categories.create');
			}]
		}
	});

	routes.push({
		id: 'portal.feedback_categories.create',
		url: '/create/',
		page: {
			templateName: 'FeedbackCategories/edit.html',
			controller: 'Admin_FeedbackCategories_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'portal.feedback_categories.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateName: 'FeedbackCategories/edit.html',
			controller: 'Admin_FeedbackCategories_Ctrl_Edit'
		}
	});

	//###
	//# Feedback::Labels
	//###

	routes.push({
		id: 'portal.feedback_labels',
		url: '/feedback/labels',
		with_list_view: true,
		list: {
			templateName: 'Labels/Feedback/list.html',
			controller: 'Admin_Labels_Feedback_Ctrl_List'
		}
	});

	routes.push({
		id: 'portal.feedback_labels.create',
		url: '/create/',
		page: {
			templateName: 'Labels/Feedback/edit.html',
			controller: 'Admin_Labels_Feedback_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'portal.feedback_labels.gocreate',
		url: '/go-create/',
		page: {
			templateName: 'Labels/Feedback/edit.html',
			controller: ['$state', function ($state) {
				$state.go('portal.feedback_labels.create');
			}]
		}
	});

	routes.push({
		id: 'portal.feedback_labels.edit',
		url: '/{label:.*}/',
		page: {
			templateName: 'Labels/Feedback/edit.html',
			controller: 'Admin_Labels_Feedback_Ctrl_Edit'
		}
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
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Departments
	//###
	routes.push({
		id: 'chat.chat_deps',
		url: '/chat_deps',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Fields
	//###
	routes.push({
		id: 'chat.fields',
		url: '/fields',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Labels
	//###
	routes.push({
		id: 'chat.labels',
		url: '/labels',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
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
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Accounts
	//###
	routes.push({
		id: 'twitter.accounts',
		url: '/accounts',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
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
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# API Keys
	//###
	routes.push({
		id: 'apps.api_keys',
		url: '/api_keys',
		with_list_view: true,
		list: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
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
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Check File Integrity
	//###
	routes.push({
		id: 'server.file_check',
		url: '/file_check',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Test File Uploads
	//###
	routes.push({
		id: 'server.test_file_ups',
		url: '/test_file_ups',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Cron
	//###
	routes.push({
		id: 'server.cron',
		url: '/cron',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# PHP Info
	//###
	routes.push({
		id: 'server.php_info',
		url: '/php_info',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# MySQL Info
	//###
	routes.push({
		id: 'server.mysql_info',
		url: '/mysql_info',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# MySQL Status
	//###
	routes.push({
		id: 'server.mysql_status',
		url: '/mysql_status',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Test Email
	//###
	routes.push({
		id: 'server.test_email',
		url: '/test_email',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Update MySQL Sort Order
	//###
	routes.push({
		id: 'server.mysql_sort_order',
		url: '/mysql_sort_order',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Error Logs
	//###
	routes.push({
		id: 'server.error_logs',
		url: '/error_logs',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Sendmail Queue
	//###
	routes.push({
		id: 'server.sendmail_queue',
		url: '/sendmail_queue',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Task Queue Logs
	//###
	routes.push({
		id: 'server.task_queue',
		url: '/task_queue',
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	return routes;
});