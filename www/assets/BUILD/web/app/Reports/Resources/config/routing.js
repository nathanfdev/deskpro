define(() => {
  'use strict';

  const routes = [];

  // ##################################################################################################################
  // Back to agent
  // ##################################################################################################################

  routes.push({
    id:           'go_to_agent',
    url:          '/go_to_agent',
    templateName: 'Index/blank.html',
    controller:   'Reports_Main_Ctrl_BackToAgent'
  });

  routes.push({
    id:           'go_to_admin',
    url:          '/go_to_admin',
    templateName: 'Index/blank.html',
    controller:   'Reports_Main_Ctrl_GoToAdmin'
  });

  routes.push({
    id:           'go_to_billing',
    url:          '/go_to_billing',
    templateName: 'Index/blank.html',
    controller:   'Reports_Main_Ctrl_GoToBilling'
  });

  routes.push({
    id:           'go_to_user',
    url:          '/go_to_user',
    templateName: 'Index/blank.html',
    controller:   'Reports_Main_Ctrl_GoToUser'
  });

  // ##################################################################################################################
  // Overview page
  // ##################################################################################################################

  routes.push({
    id:           'home',
    url:          '/',
    templateName: 'Overview/index.html',
    controller:   'Reports_Overview_Ctrl_Overview'
  });

  // ##################################################################################################################
  // Report Builder
  // ##################################################################################################################

  routes.push({
    id:           'builder',
    url:          '/builder',
    templateName: 'Builder/list.html',
    controller:   'Reports_Builder_Ctrl_List'
  });

  routes.push({
    id:           'builder.create',
    url:          '/create/{type:(?:custom)}',
    templateName: 'Builder/create.html',
    controller:   'Reports_Builder_Ctrl_Edit'
  });

  routes.push({
    id:           'builder.edit',
    url:          '/{id:[0-9]+}/{type:(?:custom|builtIn)}/{params:.*}',
    templateName: 'Builder/edit.html',
    controller:   'Reports_Builder_Ctrl_Edit'
  });

  // ##################################################################################################################
  // Billing
  // ##################################################################################################################

  routes.push({
    id:           'billing',
    url:          '/billing',
    templateName: 'Billing/list.html',
    controller:   'Reports_Billing_Ctrl_List'
  });

  routes.push({
    id:           'billing.view',
    url:          '/{id:.*}/params_{params:.*}',
    templateName: 'Billing/view.html',
    controller:   'Reports_Billing_Ctrl_View'
  });

  // ##################################################################################################################
  // Agent Activity
  // ##################################################################################################################

  routes.push({
    id:           'agent_activity',
    url:          '/agent_activity',
    templateName: 'AgentActivity/index.html',
    controller:   'Reports_AgentActivity_Ctrl_AgentActivity'
  });

  // ##################################################################################################################
  // Agent Hours
  // ##################################################################################################################

  routes.push({
    id:           'agent_hours',
    url:          '/agent_hours',
    templateName: 'AgentHours/index.html',
    controller:   'Reports_AgentHours_Ctrl_AgentHours'
  });

  // ##################################################################################################################
  // Ticket Satisfaction
  // ##################################################################################################################

  routes.push({
    id:           'ticket_satisfaction',
    url:          '/ticket_satisfaction',
    templateName: 'TicketSatisfaction/index.html',
    controller:   'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction'
  });

  return routes;
});
