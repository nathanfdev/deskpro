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
		templateName: 'Billing/list.html',
		controller: 'Reports_Billing_Ctrl_List'
	});

	routes.push({
		id: 'billing.view',
		url: '/{id:.*}/params_{params:.*}',
		templateName: 'Builder/view.html',
		controller: 'Reports_Billing_Ctrl_View'
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
		templateName: 'AgentHours/index.html',
		controller: 'Reports_AgentHours_Ctrl_AgentHours'
	});

	//##################################################################################################################
	// Ticket Satisfaction
	//##################################################################################################################

	routes.push({
		id: 'ticket_satisfaction',
		url: '/ticket_satisfaction',
		templateName: 'TicketSatisfaction/index.html',
		controller: 'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction'
	});

	return routes;
});