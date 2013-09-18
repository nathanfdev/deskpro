define(function() {
	'use strict';
	var routes = [];

	//##################################################################################################################
	// Main Nav
	//##################################################################################################################

	routes.push({
		id: 'home',
		url: '/',
		with_list_view: false,
		with_nav_view: false,
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/home.html',
			controller: 'Admin_Main_Ctrl_NavSetup'
		}
	});

	routes.push({
		id: 'setup',
		url: '/setup',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-setup.html',
			controller: 'Admin_Main_Ctrl_NavSetup'
		}
	});

	routes.push({
		id: 'agents',
		url: '/agents',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-agents.html',
			controller: 'Admin_Main_Ctrl_NavAgents'
		}
	});

	routes.push({
		id: 'tickets',
		url: '/tickets',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-tickets.html',
			controller: 'Admin_Main_Ctrl_NavTickets'
		}
	});

	routes.push({
		id: 'crm',
		url: '/crm',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-crm.html',
			controller: 'Admin_Main_Ctrl_NavCrm'
		}
	});

	routes.push({
		id: 'portal',
		url: '/portal',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-portal.html',
			controller: 'Admin_Main_Ctrl_NavPortal'
		}
	});

	routes.push({
		id: 'chat',
		url: '/chat',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-chat.html',
			controller: 'Admin_Main_Ctrl_NavChat'
		}
	});

	routes.push({
		id: 'twitter',
		url: '/twitter',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-twitter.html',
			controller: 'Admin_Main_Ctrl_NavTwitter'
		}
	});

	routes.push({
		id: 'apps',
		url: '/apps',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-apps.html',
			controller: 'Admin_Main_Ctrl_NavApps'
		}
	});

	routes.push({
		id: 'server',
		url: '/server',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-nav-server.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/back-to-agent.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/list.html',
			controller: 'Admin_TicketDeps_Ctrl_List'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/help-page.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.ticket_deps.edit',
		url: '/{id:[0-9]+}',
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Fields
	//###
	routes.push({
		id: 'crm.fields',
		url: '/fields',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Theme
	//###
	routes.push({
		id: 'crm.theme',
		url: '/theme',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Settings
	//###
	routes.push({
		id: 'crm.settings',
		url: '/theme',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Kb
	//###
	routes.push({
		id: 'crm.kb',
		url: '/kb',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Downloads
	//###
	routes.push({
		id: 'crm.downloads',
		url: '/downloads',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# News
	//###
	routes.push({
		id: 'crm.news',
		url: '/news',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	//###
	//# Feedback
	//###
	routes.push({
		id: 'crm.feedback',
		url: '/feedback',
		with_list_view: true,
		list: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		},
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
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
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/blank.html',
			controller: 'Admin_Main_Ctrl_Bare'
		}
	});

	return routes;
});