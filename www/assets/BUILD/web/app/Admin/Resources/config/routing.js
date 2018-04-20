define(function() {
  'use strict';
  var routes = [];

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

  routes.push({
    id: 'license_go',
    url: '/go-license',
    template: '',
    controller: ['$state', function ($state) { $state.go('license'); }]
  });

  //##################################################################################################################
  // Main Nav
  //##################################################################################################################

  routes.push({
    id: 'setup',
    url: '/setup',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'agents',
    url: '/agents',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'tickets',
    url: '/tickets',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'crm',
    url: '/crm',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'portal',
    url: '/portal/{brandId:[0-9]+|new}',
    templateName: 'Layout/app.html',
		controller: 'Admin_Portal_Ctrl_Nav'
	});

  routes.push({
    id: 'chat',
    url: '/chat',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'twitter',
    url: '/twitter',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'apps',
    url: '/apps',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'tasks',
    url: '/tasks',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id: 'server',
    url: '/server',
    templateName: 'Layout/app.html',
    controller: 'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id:           'voice-channel',
    url:          '/voice_channel',
    templateName: 'Layout/app.html',
    controller:   'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id:           'emails',
    url:          '/emails',
    templateName: 'Layout/app.html',
    controller:   'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id:           'features',
    url:          '/features',
    templateName: 'Layout/app.html',
    controller:   'Admin_Main_Ctrl_Nav'
  });

  routes.push({
    id:           'dev',
    url:          '/dev',
    templateName: 'Layout/app.html',
    controller:   'Admin_Main_Ctrl_Nav'
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
  //# Automatic Upgrader
  //###
  routes.push({
    id: 'setup.updater',
    url: '/updater',
    templateName: 'Settings/updater.html',
    controller: 'Admin_Settings_Ctrl_UpdaterSettings'
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
    url: '/{id:[a-z_]+}',
    templateName: 'Languages/edit.html',
    controller: 'Admin_Languages_Ctrl_Edit'
  });

  routes.push({
    id: 'setup.languages.install',
    url: '/{id:install\\-[a-z_]+}',
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
    url: '/{id:phrases\\-[a-z_]+}',
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

  //###
  //# Reset Demo
  //###
  routes.push({
    id: 'setup.reset_demo',
    url: '/reset-helpdesk',
    templateName: 'Settings/reset-helpdesk.html',
    controller: 'Admin_Settings_Ctrl_ResetHelpdesk'
  });

	//###
	//# Anti-Abuse
	//###
	routes.push({
		id: 'setup.rate_limiting',
		url: '/rate_limiting',
		templateName: 'AntiAbuse/portal_rate_limiting.html',
		controller: 'Admin_AntiAbuse_Ctrl_PortalRateLimiting'
	});

	routes.push({
		id: 'setup.email_rate_limiting',
		url: '/email_rate_limiting',
		templateName: 'AntiAbuse/email_rate_limiting.html',
		controller: 'Admin_AntiAbuse_Ctrl_EmailRateLimiting'
	});

	routes.push({
		id: 'setup.captcha_settings',
		url: '/captcha_settings',
		templateName: 'AntiAbuse/captcha_settings.html',
		controller: 'Admin_AntiAbuse_Ctrl_CaptchaSettings'
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
    url: '/{id:[0-9]+}?created_agent',
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
    id: 'agents.usersources.sync',
    url: '/sync/{id:[\\d\\w]+}',
    templateName: 'Usersources/sync-information.html',
    controller: 'Admin_Usersources_Ctrl_SyncInformation'
  });

  routes.push({
    id: 'agents.usersources.deskpro',
    url: '/deskpro-{id:[\\d\\w]+}',
    templateName: 'Usersources/edit-instance.html',
    controller: 'Admin_Usersources_Ctrl_EditDeskproInstance'
  });

  routes.push({
    id: 'agents.usersources.id',
    url: '/{id:[\\d\\w]+}',
    templateName: 'Usersources/edit-instance.html',
    controller: 'Admin_Usersources_Ctrl_EditInstance'
  });

  routes.push({
    id: 'agents.usersources.install',
    url: '/install/{name:\\w+}',
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

  //###
  //# AuditLogs
  //###
  routes.push({
    id: 'agents.audit_logs',
    url: '/audit_logs',
    templateName: 'Agents/audit_logs.html',
    controller: 'Admin_AgentAuditLogs_Ctrl_AuditLogs'
  });

  routes.push({
    id: 'agents.audit_logs.view',
    url: '/view/{id}',
    templateName: 'Agents/audit_logs-view.html',
    controller: 'Admin_AgentAuditLogs_Ctrl_AuditLogsView',
    target: "appbody@agents"
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
    id: 'tickets.statuses.archived',
    url: '/statuses/archived',
    templateName: 'TicketStatuses/status-archived.html',
    controller: 'Admin_TicketStatuses_Ctrl_EditArchived'
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
    url: '/triggers/{type:(?:newticket|newreply|update|webhook)}',
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
    id: 'tickets.triggers.editsatisfaction',
    url: '/{id:satisfaction\-[0-2]}',
    templateName: 'TicketTriggers/edit-satisfaction.html',
    controller: 'Admin_TicketTriggers_Ctrl_EditSatisfactionTrigger',
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
  //# Webhooks
  //###

  routes.push({
    id: 'tickets.webhooks',
    url: '/webhooks',
    templateName: 'TicketWebhooks/list.html',
    controller: 'Admin_TicketWebhooks_Ctrl_List'
  });

  routes.push({
    id: 'tickets.webhooks.edit',
    url: '/{id:[0-9]+}',
    templateName: 'TicketWebhooks/edit.html',
    controller: 'Admin_TicketWebhooks_Ctrl_Edit',
    data: { stateMarkId: "tickets.webhooks" }
  });

  routes.push({
    id: 'tickets.webhooks.create',
    url: '/',
    templateName: 'TicketWebhooks/edit.html',
    controller: 'Admin_TicketWebhooks_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.webhooks.trigger-edit',
    url: '/{webhookId:[0-9]+}/trigger/{id:[0-9]+}',
    templateName: 'TicketTriggers/edit.html',
    controller: 'Admin_TicketWebhooks_Ctrl_TriggerEdit',
    data: { stateMarkId: "tickets.triggers" }
  });

  routes.push({
    id: 'tickets.webhooks.trigger-edit-redirect',
    url: '/trigger-create',
    template: '',
    controller: ['$state', '$stateParams', function ($state, $stateParams) {
      alert('sdadasdas')
      $state.go('tickets.webhooks.trigger-edit', $stateParams);
    }]
  });

  routes.push({
    id: 'tickets.webhooks.trigger-create',
    url: '{webhookId:[0-9]+}/trigger',
    templateName: 'TicketTriggers/edit.html',
    controller: 'Admin_TicketWebhooks_Ctrl_TriggerEdit'
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

	routes.push({
		id: 'tickets.ticket_filters.edit.single_filter',
		url: '/filter/{filter_id:[0-9]+}',
		templateName: 'TicketFilters/edit_single.html',
		controller: 'Admin_TicketFilters_Ctrl_EditSingle'
	});

  routes.push({
    id: 'tickets.ticket_filters.edit_view',
    url: '/view/{id:[0-9]+}',
    templateName: 'TicketFilterViews/edit.html',
    controller: 'Admin_TicketFilters_Ctrl_EditView'
  })

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

  routes.push({
    id: 'tickets.ticket_escalations.editsatisfaction',
    url: '/satisfaction',
    templateName: 'TicketEscalations/edit-satisfaction.html',
    controller: 'Admin_TicketEscalations_Ctrl_EditSatisfaction'
  });
  routes.push({
    id: 'tickets.ticket_escalations.editstatuses',
    url: '/statuses/{id:[0-9]+}',
    templateName: 'TicketEscalations/edit-statuses.html',
    controller: 'Admin_TicketEscalations_Ctrl_EditStatuses'
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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'tickets'}
  });

  routes.push({
    id: 'tickets.labels.create',
    url: '/create',
    templateName: 'Labels/Ticket/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'tickets'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'tickets'}
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
    id: 'tickets.timelog_billing_settings',
    url: '/timelog_billing/settings',
    templateName: 'TicketBilling/settings.html',
    controller: 'Admin_TicketSettings_Ctrl_TicketSettings'
  });

  routes.push({
    id: 'tickets.timelog_billing_fields',
    url: '/timelog_billing/fields',
    templateName: 'TicketBilling/fields.html',
    controller: 'Admin_TicketBilling_Ctrl_Fields'
  });

  routes.push({
    id: 'tickets.timelog_billing_fields.gocreate',
    url: '/go-create',
    template: '',
    controller: ['$state', '$stateParams', function ($state, $stateParams) {
      $state.go('tickets.timelog_billing_fields.create', $stateParams);
    }]
  });

  routes.push({
    id: 'tickets.timelog_billing_fields.create',
    url: '/create',
    templateName: 'CustomFields/Billing/edit.html',
    controller: 'Admin_CustomFields_Billing_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.timelog_billing_fields.edit',
    url: '/{id:[0-9]+}',
    templateName: 'CustomFields/Billing/edit.html',
    controller: 'Admin_CustomFields_Billing_Ctrl_Edit'
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
  //# Sms Channel
  //###
  routes.push({
    id: 'tickets.channel_sms',
    url: '/channel_sms',
    templateName: 'ChannelSms/list.html',
    controller: 'Admin_ChannelSms_Ctrl_List'
  });

  routes.push({
    id: 'tickets.channel_sms.outgoing_log',
    url: '/channel_sms',
    templateName: 'ChannelSms/create.html',
    controller: 'Admin_ChannelSms_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.channel_sms.incoming_log',
    url: '/channel_sms',
    templateName: 'ChannelSms/create.html',
    controller: 'Admin_ChannelSms_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.channel_sms.create',
    url: '/create',
    templateName: 'ChannelSms/edit.html',
    controller: 'Admin_ChannelSms_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.channel_sms.edit',
    url: '/{id:[0-9]+}',
    templateName: 'ChannelSms/edit.html',
    controller: 'Admin_ChannelSms_Ctrl_Edit'
  });


  //###
  //# Facebook Channel
  //###
  routes.push({
    id: 'tickets.channel_facebook',
    url: '/channel_facebook',
    templateName: 'ChannelFacebook/list.html',
    controller: 'Admin_ChannelFacebook_Ctrl_List'
  });

  routes.push({
    id: 'tickets.channel_facebook.outgoing_log',
    url: '/channel_facebook',
    templateName: 'ChannelFacebook/create.html',
    controller: 'Admin_ChannelFacebook_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.channel_facebook.incoming_log',
    url: '/channel_facebook',
    templateName: 'ChannelFacebook/create.html',
    controller: 'Admin_ChannelFacebook_Ctrl_Edit'
  });

  routes.push({
    id: 'tickets.channel_facebook.create',
    url: '/create',
    templateName: 'ChannelFacebook/create.html',
    controller: 'Admin_ChannelFacebook_Ctrl_Create'
  });

  routes.push({
    id: 'tickets.channel_facebook.edit',
    url: '/{id:[0-9]+}',
    templateName: 'ChannelFacebook/edit.html',
    controller: 'Admin_ChannelFacebook_Ctrl_Edit'
  });


  // Problems
  routes.push({
    id: 'tickets.problems',
    url: '/problems',
    templateName: 'TicketProblems/settings.html',
    controller: 'Admin_TicketProblems_Ctrl_Settings'
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
    id: 'crm.usersources.sync',
    url: '/sync/{id:[\\d\\w]+}',
    templateName: 'Usersources/sync-information.html',
    controller: 'Admin_Usersources_Ctrl_SyncInformation'
  });

  routes.push({
    id: 'crm.usersources.deskpro',
    url: '/deskpro-{id:[\\d\\w]+}',
    templateName: 'Usersources/edit-instance.html',
    controller: 'Admin_Usersources_Ctrl_EditDeskproInstance'
  });

  routes.push({
    id: 'crm.usersources.id',
    url: '/{id:[\\d\\w]+}',
    templateName: 'Usersources/edit-instance.html',
    controller: 'Admin_Usersources_Ctrl_EditInstance'
  });

  routes.push({
    id: 'crm.usersources.install_deskpro',
    url: '/install/deskpro',
    templateName: 'Usersources/add-instance-deskpro.html',
    controller: 'Admin_Usersources_Ctrl_AddDeskproInstance'
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
    data: {owner: 'ticket', context: 'person'},
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

  routes.push({
    id: 'crm.user_fields.specific',
    url: '/specific',
    abstract: true
  });

  routes.push({
    id: 'crm.user_fields.specific.gocreate',
    url: '/go-create',
    template: '',
    controller: ['$state', '$stateParams', function ($state, $stateParams) {
      $state.go('crm.user_fields.specific.create', $stateParams);
    }]
  });

  routes.push({
    id: 'crm.user_fields.specific.create',
    url: '/create',
    data: {owner: 'ticket', context: 'person'},
    templateName: 'CustomFields/edit.html',
    controller: 'Admin_CustomFields_Ctrl_Edit'
  });

  routes.push({
    id: 'crm.user_fields.specific.edit',
    url: '/{id:[0-9]+}',
    data: {owner: 'ticket', context: 'person'},
    templateName: 'CustomFields/edit.html',
    controller: 'Admin_CustomFields_Ctrl_Edit'
  });

  //###
  //# Fields::Orgs
  //###
  routes.push({
    id: 'crm.org_fields',
    url: '/org_fields',
    data: {owner: 'ticket', context: 'organization'},
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

  routes.push({
    id: 'crm.org_fields.specific',
    url: '/specific',
    abstract: true
  });

  routes.push({
    id: 'crm.org_fields.specific.gocreate',
    url: '/go-create',
    template: '',
    controller: ['$state', '$stateParams', function ($state, $stateParams) {
      $state.go('crm.org_fields.specific.create', $stateParams);
    }]
  });

  routes.push({
    id: 'crm.org_fields.specific.create',
    url: '/create',
    data: {owner: 'ticket', context: 'organization'},
    templateName: 'CustomFields/edit.html',
    controller: 'Admin_CustomFields_Ctrl_Edit'
  });

  routes.push({
    id: 'crm.org_fields.specific.edit',
    url: '/{id:[0-9]+}',
    data: {owner: 'ticket', context: 'organization'},
    templateName: 'CustomFields/edit.html',
    controller: 'Admin_CustomFields_Ctrl_Edit'
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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'people'}
  });

  routes.push({
    id: 'crm.user_labels.create',
    url: '/create',
    templateName: 'Labels/Person/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'people'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'people'}
  });

  //###
  //# Labels::Orgs
  //###

  routes.push({
    id: 'crm.org_labels',
    url: '/org_labels',
    templateName: 'Labels/Org/list.html',
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'organizations'}
  });

  routes.push({
    id: 'crm.org_labels.create',
    url: '/create',
    templateName: 'Labels/Org/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'organizations'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'organizations'}
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

  //###
  //# Export
  //###
  routes.push({
    id: 'crm.export',
    url: '/export',
    templateName: 'ExportCsv/export-csv.html',
    controller: 'Admin_ExportCsv_Ctrl_ExportCsv'
  });

  //##################################################################################################################
  // Portal
  //##################################################################################################################

  //###
  //# Portal Setup
  //###
  routes.push({
    id: 'portal.setup',
    url: '/setup',
    templateName: 'Portal/setup.html',
    controller: 'Admin_Portal_Ctrl_Setup'
  });

  //###
  //# Portal Editor
  //###
  routes.push({
    id: 'portal.portal_editor',
    url: '/portal_editor',
    templateName: 'Portal/portal-editor.html',
    controller: 'AdminPortalCtrlPortalEditor'
  });

  //###
  //# Portal Editor Disabled
  //###
  routes.push({
    id: 'portal.portal_editor_disabled',
    url: '/portal_editor_disabled',
    templateName: 'Portal/portal-editor-disabled.html',
    controller: 'Admin_Main_Ctrl_Bare'
  });

	//###
	//# Portal Widget Editor
	//###
	routes.push({
		id: 'portal.widget_editor',
		url: '/widget_editor',
		templateName: 'Portal/widget-editor.html',
		controller: 'AdminPortalCtrlWidgetEditor'
	});

	//###
	//# Ticket Form Widget
	//###
	routes.push({
		id: 'portal.ticket_form_widget',
		url: '/ticket_form_widget',
		templateName: 'Portal/ticket-form-widget.html',
		controller: 'Admin_Portal_Ctrl_TicketFormWidget'
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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'articles'}
  });

  routes.push({
    id: 'portal.kb_labels.create',
    url: '/create/',
    templateName: 'Labels/Kb/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'articles'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'articles'}
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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'downloads'}
  });

  routes.push({
    id: 'portal.downloads_labels.create',
    url: '/create/',
    templateName: 'Labels/Downloads/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'downloads'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'downloads'}
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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'news'}
  });

  routes.push({
    id: 'portal.news_labels.create',
    url: '/create/',
    templateName: 'Labels/News/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'news'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'news'}
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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'feedback'}
  });

  routes.push({
    id: 'portal.feedback_labels.create',
    url: '/create/',
    templateName: 'Labels/Feedback/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'feedback'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'feedback'}
  });

  //###
  //# Guides::Settings
  //###
  routes.push({
    id: 'portal.guides_settings',
    url: '/guides/settings',
    templateName: 'GuidesSettings/guides-settings.html',
    controller: 'Admin_GuidesSettings_Ctrl_GuidesSettings'
  });

  //##################################################################################################################
  // Chat
  //##################################################################################################################

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
    controller: 'Admin_Labels_Ctrl_List',
    data: {type: 'chat'}
  });

  routes.push({
    id: 'chat.labels.create',
    url: '/create/',
    templateName: 'Labels/Chat/edit.html',
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'chat'}
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
    controller: 'Admin_Labels_Ctrl_Edit',
    data: {type: 'chat'}
  });

  //###
  //# Round Robin
  //###
  routes.push({
    id: 'chat.roundrobin',
    url: '/roundrobin',
    templateName: 'ChatRoundRobin/list.html',
    controller: 'Admin_ChatRoundRobin_Ctrl_List'
  });

  routes.push({
    id: 'chat.roundrobin.edit',
    url: '/{id:.*}',
    templateName: 'ChatRoundRobin/edit.html',
    controller: 'Admin_ChatRoundRobin_Ctrl_Edit'
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


  //###
  //# Email Accounts
  //###
  routes.push({
    id: 'emails.ticket_accounts',
    url: '/ticket_accounts',
    templateName: 'TicketAccounts/list.html',
    controller: 'Admin_TicketAccounts_Ctrl_List'
  });

  routes.push({
    id: 'emails.ticket_accounts.gocreate',
    url: '/go-create',
    template: '',
    controller: ['$state', function ($state) { $state.go('emails.ticket_accounts.create'); }]
  });

  routes.push({
    id: 'emails.ticket_accounts.create',
    url: '/create',
    templateName: 'TicketAccounts/edit.html',
    controller: 'Admin_TicketAccounts_Ctrl_Edit'
  });

  routes.push({
    id: 'emails.ticket_accounts.edit',
    url: '/{id:[0-9]+}',
    templateName: 'TicketAccounts/edit.html',
    controller: 'Admin_TicketAccounts_Ctrl_Edit'
  });

  routes.push({
    id: 'emails.ticket_accounts.goemailsourcesview',
    url: '/go-incoming-email/{id:[0-9]+}',
    template: '',
    controller: ['$state', '$stateParams', function ($state, $stateParams) {
      $state.go('emails.ticket_accounts.emailsourcesview', $stateParams);
    }]
  });

  routes.push({
    id: 'emails.ticket_accounts.emailsourcesview',
    url: '/incoming-email/{id:[0-9]+}',
    templateName: 'EmailStatus/emailsource-view.html',
    controller: 'Admin_EmailStatus_Ctrl_ViewSource',
    target: "appbody@emails"
  });

  routes.push({
    id: 'emails.ticket_accounts.gosendmailview',
    url: '/go-outgoing-email/{id:[0-9]+}',
    template: '',
    controller: ['$state', '$stateParams', function ($state, $stateParams) {
      $state.go('emails.ticket_accounts.sendmailqueueview', $stateParams);
    }]
  });

  routes.push({
    id: 'emails.ticket_accounts.sendmailqueueview',
    url: '/outgoing-email/{id:[0-9]+}',
    templateName: 'EmailStatus/sendmail-view.html',
    controller: 'Admin_EmailStatus_Ctrl_ViewSend',
    target: "appbody@emails"
  });

  routes.push({
    id: 'emails.ticket_accounts.emailsources',
    url: '/incoming-email',
    templateName: 'EmailStatus/emailsource-list.html',
    controller: 'Admin_EmailStatus_Ctrl_SourceList',
    target: "appbody@emails"
  });

  routes.push({
    id: 'emails.ticket_accounts.sendmailqueue',
    url: '/outgoing-email',
    templateName: 'EmailStatus/sendmail-list.html',
    controller: 'Admin_EmailStatus_Ctrl_SendmailList',
    target: "appbody@emails"
  });

  routes.push({
    id: 'emails.ticket_accounts.advancedsettings',
    url: '/advanced-settings',
    templateName: 'TicketAccounts/advanced-settings.html',
    controller: 'Admin_TicketAccounts_Ctrl_Settings',
    target: "appbody@emails"
  });

  //##################################################################################################################
  // Email templates
  //##################################################################################################################

  routes.push({
    id:           'emails.templates_editor',
    url:          '/templates_editor',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'emails.templates_editor.edit',
    url:          '/{name}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  //###
  //# Email Templates
  //###
  routes.push({
    id: 'emails.email_templates',
    url: '/email_templates',
    templateName: 'Templates/email-groups.html',
    controller: 'Admin_Templates_Ctrl_EmailGroupListOld'
  });

  //###
  //# Temporary Email Templates
  //###
  routes.push({
    id: 'emails.email_templates_legacy',
    url: '/email_templates_legacy',
    templateName: 'Templates/email-groups-legacy.html',
    controller: 'Admin_Templates_Ctrl_EmailGroupList'
  });

  routes.push({
    id: 'emails.email_templates.list',
    url: '/{groupName:.*?}',
    templateName: 'Templates/email-listing.html',
    controller: 'Admin_Templates_Ctrl_EmailList'
  });

  //##################################################################################################################
  // Voice channel
  //##################################################################################################################

  routes.push({
    id:           'voice-channel.accounts',
    url:          '/accounts',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.numbers',
    url:          '/numbers',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.numbers_search_available',
    url:          '/numbers/available',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.numbers_search_existing',
    url:          '/numbers/existing',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.numbers_add',
    url:          '/numbers/new',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.numbers_edit',
    url:          '/numbers/{id:\\d+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.extensions',
    url:          '/extensions',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.extensions_new',
    url:          '/extensions/new',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.extensions_edit',
    url:          '/extensions/{id:\\d+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.queues',
    url:          '/queues',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.queues_new',
    url:          '/queues/new',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.queues_edit',
    url:          '/queues/{id:\\d+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.auto_attendants',
    url:          '/auto_attendants',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.auto_attendants_new',
    url:          '/auto_attendants/new',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.auto_attendants_edit',
    url:          '/auto_attendants/{id:\\d+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.agents',
    url:          '/agents',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.music_and_greetings',
    url:          '/music_and_greetings',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.call_logs',
    url:          '/call_logs',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'voice-channel.call_logs_view',
    url:          '/call_logs/{id:\\d+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  //##################################################################################################################
  // Dev
  //##################################################################################################################

  routes.push({
    id:           'dev.notifications',
    url:          '/notifications',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
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
    url: '/{name:go\\-apps\\-(?:.*?)}',
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

  routes.push({
    id: 'apps.apps.edit-v2',
    url: '/v2/{instanceId:\\d+}',
    templateName: 'Apps/instance_v2.html',
    controller: 'Admin_Apps_Ctrl_EditInstanceV2'
  });

  // this route allows reloading of the apps.apps.installer-v2 route
  routes.push({
    id: 'apps.apps.install-v2-reload',
    url: '/app-install-reload/{appName:[a-zA-Z0-9@%\\/\\-_\\.]+}',
    templateName: 'Index/blank.html',
    controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('apps.apps.install-v2', { appName: $stateParams.appName }); }]
  });

  routes.push({
    id: 'apps.apps.install-v2',
    url: '/app-install/{appName:[a-zA-Z0-9@%\\/\\-_\\.]+}',
    templateName: 'Apps/install-app-v2.html',
    controller: 'Admin_Apps_Ctrl_InstallAppV2'
  });

  routes.push({
    id: 'apps.apps.update-v2-reload',
    url: '/app-update-reload/{instanceId:\\d+}',
    templateName: 'Index/blank.html',
    controller: ['$state', '$stateParams', function ($state, $stateParams) { $state.go('apps.apps.update-v2', { instanceId: $stateParams.instanceId }); }]
  });

  routes.push({
    id: 'apps.apps.update-v2',
    url: '/app-update/{instanceId:\\d+}',
    templateName: 'Apps/install-app-v2.html',
    controller: 'Admin_Apps_Ctrl_UpdateAppV2'
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

	//###
	//# Api Logs
	//###

  routes.push({
    id: 'apps.api_keys.logs',
    url: '/api_logs',
    templateName: 'ApiLogs/list.html',
    controller: 'Admin_ApiKeys_Ctrl_Logs'
  });

	routes.push({
		id: 'apps.api_keys.logs_view',
		url: '/api_logs/{id:[0-9]+}',
		templateName: 'ApiLogs/view.html',
		controller: 'Admin_ApiKeys_Ctrl_LogsView'
	});

  //###
  //# OAuth
  //###

  routes.push({
    id:           'apps.oauth_clients',
    url:          '/oauth_clients',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'apps.oauth_client_new',
    url:          '/oauth_clients/new',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'apps.oauth_client_edit',
    url:          '/oauth_clients/{id:[0-9]+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  //###
  //# Importer
  //###

  routes.push({
    id:           'apps.importer',
    url:          '/importer',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'apps.importer_source',
    url:          '/importer/source/{id:[a-zA-Z0-9\\._\\-]+}',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
  });

  routes.push({
    id:           'apps.importer_status',
    url:          '/importer/status',
    templateName: 'ReactRoutes/react_component.html',
    controller:   'Admin_ReactRoutes_Ctrl_ReactComponent'
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
  // Features
  //##################################################################################################################

  routes.push({
    id: 'features.enable',
    url: '/enable/{id:[a-zA-Z0-9\\._\\-]+}',
    templateName: 'Features/enable.html',
    controller: 'Admin_Main_Ctrl_Features'
  });

  routes.push({
    id: 'features.disable',
    url: '/disable/{id:[a-zA-Z0-9\\._\\-]+}',
    templateName: 'Features/disable.html',
    controller: 'Admin_Main_Ctrl_Features'
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
  //# Encryption
  //###
  routes.push({
    id: 'server.enc',
    url: '/encryption',
    templateName: 'Server/encryption.html',
    controller: 'Admin_Server_Ctrl_ServerEnc'
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
  //# Pusher app
  //###
  routes.push({
    id: 'server.notifications',
    url: '/settings/notifications',
    templateName: 'Notifications/list.html',
    controller: 'Admin_Settings_Ctrl_Notifications'
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
	//# Incidents
	//###
	routes.push({
		id: 'server.incidents',
		url: '/incidents',
		templateName: 'Server/server-incidents.html',
		controller: 'Admin_ServerIncidents_Ctrl_ServerIncidents'
	});

	routes.push({
		id: 'server.incidents.view',
		url: '/view/{id}',
		templateName: 'Server/server-incidents-view.html',
		controller: 'Admin_ServerIncidents_Ctrl_View',
		target: "appbody@server"
	});

	routes.push({
		id: 'server.incidents.event',
		url: '/event/{id}',
		templateName: 'Server/server-incidents-event.html',
		controller: 'Admin_ServerIncidents_Ctrl_Event',
		target: "appbody@server"
	});

  // routes.push({
  // 	id: 'server.incidents.view',
  // 	url: '/view/{id}',
  // 	templateName: 'Server/server-incidents-view.html',
  // 	controller: 'Admin_ServerIncidents_Ctrl_View',
  // 	target: "appbody@server"
  // });

  //###
  //# Jobs
  //###
  routes.push({
    id: 'server.jobs',
    url: '/jobs',
    templateName: 'Server/server-jobs-list.html',
    controller: 'Admin_ServerJobs_Ctrl_List',
    target: "appbody@server"
  });

  routes.push({
    id: 'server.jobs.view',
    url: '/{id}',
    templateName: 'Server/server-jobs-view.html',
    controller: 'Admin_ServerJobs_Ctrl_View',
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
