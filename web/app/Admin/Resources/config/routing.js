define(function() {
	'use strict';
	var routes = [];

	//##################################################################################################################
	// Main Nav
	//##################################################################################################################

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
	// Ticket Departments
	//##################################################################################################################

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
		id: 'tickets.ticket_deps.create',
		url: '/create',
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'tickets.ticket_deps.edit',
		url: '/{id}',
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	return routes;
})