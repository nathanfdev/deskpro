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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
	//# Triggers
	//###
	routes.push({
		id: 'tickets.triggers',
		url: '/triggers',
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
	//# Filters
	//###
	routes.push({
		id: 'tickets.filters',
		url: '/filters',
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
	//# Feedback
	//###
	routes.push({
		id: 'tickets.feedback',
		url: '/feedback',
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
	//# Escalations
	//###
	routes.push({
		id: 'tickets.escalations',
		url: '/escalations',
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
	//# Escalations
	//###
	routes.push({
		id: 'tickets.slas',
		url: '/slas',
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
		id: 'tickets.labels',
		url: '/labels',
		with_list_view: true,
		list: {
			templateName: 'TicketLabels/list.html',
			controller: 'Admin_TicketLabels_Ctrl_List'
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
 			templateName: 'TicketLabels/edit.html',
 			controller: 'Admin_TicketLabels_Ctrl_Edit'
 		}
 	});

  routes.push({
 		id: 'tickets.labels.gocreate',
 		url: '/go-create',
 		page: {
 			templateName: 'TicketLabels/edit.html',
      controller: ['$state', function ($state) { $state.go('tickets.labels.create'); }]
 		}
 	});

 	routes.push({
 		id: 'tickets.labels.edit',
 		url: '/{label:.*}',
 		page: {
 			templateName: 'TicketLabels/edit.html',
 			controller: 'Admin_TicketLabels_Ctrl_Edit'
 		}
 	});

	//###
	//# Billing
	//###
	routes.push({
		id: 'tickets.billing',
		url: '/billing',
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
	//# Message Presets
	//###
	routes.push({
		id: 'tickets.message_presets',
		url: '/message_presets',
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_BareList'
		},
		page: {
			templateName: 'Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
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