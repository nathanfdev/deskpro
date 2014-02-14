define(function() {
	'use strict';
	var routes = [];

	//##################################################################################################################
	// Overview page
	//##################################################################################################################

	routes.push({
		id: 'home',
		url: '/',
		templateName: 'Overview/index.html',
		controller: 'Reports_Overview_Ctrl_Overview'
	});

	//##################################################################################################################
	// Report Builder
	//##################################################################################################################

	routes.push({
		id: 'builder',
		url: '/builder',
		templateName: 'Builder/list.html',
		controller: 'Reports_Builder_Ctrl_List'
	});

	routes.push({
		id: 'builder.create',
		url: '/create/{type:(?:custom)}',
		templateName: 'Builder/edit.html',
		controller: 'Reports_Builder_Ctrl_Edit'
	});

	routes.push({
		id: 'builder.edit',
		url: '/{id:[0-9]+}/{type:(?:custom|builtIn)}/{params:.*}',
		templateName: 'Builder/edit.html',
		controller: 'Reports_Builder_Ctrl_Edit'
	});

	//##################################################################################################################
	// Billing
	//##################################################################################################################

	routes.push({
		id: 'billing',
		url: '/billing',
		templateName: 'Index/blank.html',
		controller: 'Reports_Main_Ctrl_Bare'
	});

	//##################################################################################################################
	// Agent Activity
	//##################################################################################################################

	routes.push({
		id: 'agent_activity',
		url: '/agent_activity',
		templateName: 'AgentActivity/index.html',
		controller: 'Reports_AgentActivity_Ctrl_AgentActivity'
	});

	//##################################################################################################################
	// Agent Hours
	//##################################################################################################################

	routes.push({
		id: 'agent_hours',
		url: '/agent_hours',
		templateName: 'Index/blank.html',
		controller: 'Reports_Main_Ctrl_Bare'
	});

	//##################################################################################################################
	// Ticket Satisfaction
	//##################################################################################################################

	routes.push({
		id: 'ticket_satisfaction',
		url: '/ticket_satisfaction',
		templateName: 'Index/blank.html',
		controller: 'Reports_Main_Ctrl_Bare'
	});

	return routes;
});