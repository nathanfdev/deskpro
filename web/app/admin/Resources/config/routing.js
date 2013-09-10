define(function() {
	'use strict';
	var routes = [];

	//##################################################################################################################
	// Main Nav
	//##################################################################################################################

	routes.push({
		id: 'settings',
		url: '/settings',
		nav: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/Index/app-main-settings.html',
			controller: 'Admin_Main_Ctrl_SettingsNav'
		}
	});

	//##################################################################################################################
	// Ticket Departments
	//##################################################################################################################

	routes.push({
		id: 'settings.ticket_deps',
		url: '/ticket_deps',
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
		id: 'settings.ticket_deps.create',
		url: '/create',
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	routes.push({
		id: 'settings.ticket_deps.edit',
		url: '/{id}',
		page: {
			templateUrl: DP_BASE_ADMIN_URL+'/load-view/TicketDeps/edit.html',
			controller: 'Admin_TicketDeps_Ctrl_Edit'
		}
	});

	return routes;
})