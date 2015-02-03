define -> (States) ->
  #----------------------------------------
  # MAIN
  #----------------------------------------

  States.add('reports')
    .setUrl('/')
    .setTpl('InterfaceBundle:Interface:main-frame.html')
    .setCtrl(['$state', ($state) -> $state.go('reports.dashboards.index') ])

  #----------------------------------------
  # DASHBOARDS
  #----------------------------------------

  States.add('reports.dashboards')
    .setUrl('dashboards')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboards.html')
    .setAbstract()

  States.add('reports.dashboards.index')
    .setCtrl(['$state', 'DashboardsInfo', ($state, DashboardsInfo) ->
      DashboardsInfo.getDashboardList().then((dbs) ->
        db = dbs[0]

        if db.reports?.length
          $state.go('reports.dashboards.view.report', { dashboard_id: db.id, report_id: db.reports[0].id })
        else
          $state.go('reports.dashboards.view.index', { dashboard_id: db[0].id })
      )
    ])

  States.add('reports.dashboards.view')
    .setUrl('/{dashboard_id:[0-9]+}')
    .setCtrl('Reports.App.DashboardView')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-view.html')
    .setAbstract()

  States.add('reports.dashboards.view.index')
    .setCtrl(['$state', '$stateParams', 'DashboardsInfo', ($state, $stateParams, DashboardsInfo) ->
      DashboardsInfo.getReportsList($stateParams.dashboard_id).then((reports) ->
        if not reports.length
          $state.go('reports.dashboards.view.empty')
        else
          $state.go('reports.dashboards.view.report', { dashboard_id: $stateParams.dashboard_id, report_id: reports[0].id })
      , ->
        $state.go('reports')
      )
    ])

  States.add('reports.dashboards.view.empty')
    .setUrl('/empty-dashboard')

  States.add('reports.dashboards.view.report')
    .setUrl('/{report_id:[0-9]+}')
    .setCtrl('Reports.App.DashboardReport')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-report.html')

  #----------------------------------------
  # Stats
  #----------------------------------------

  States.add('reports.stats')
    .setUrl('stats')
    .setCtrl('Reports.Builder.List')
    .setTpl('ReportsInterfaceBundle:Builder:list.html')

  States.add('reports.stats.create')
    .setUrl('/create/{type:(?:custom)}')
    .setCtrl('Reports.Builder.Edit')
    .setTpl('ReportsInterfaceBundle:Builder:create.html')

  States.add('reports.stats.edit')
    .setUrl('/{id:[0-9]+}/{type:(?:custom|builtIn)}/{params:.*}')
    .setCtrl('Reports.Builder.Edit')
    .setTpl('ReportsInterfaceBundle:Builder:edit.html')

  #----------------------------------------
  # Agent Activity
  #----------------------------------------

  States.add('reports.agent_activity')
    .setUrl('agent_activity')
    .setCtrl('Reports.AgentActivity.AgentActivity')
    .setTpl('ReportsInterfaceBundle:AgentActivity:index.html')

  #----------------------------------------
  # Agent Hours
  #----------------------------------------

  States.add('reports.agent_hours')
    .setUrl('agent_hours')
    .setCtrl('Reports.AgentHours.AgentHours')
    .setTpl('ReportsInterfaceBundle:AgentHours:index.html')

  #----------------------------------------
  # Ticket Satisfaction
  #----------------------------------------

  States.add('reports.ticket_satisfaction')
    .setUrl('ticket_satisfaction')
    .setCtrl('Reports.AgentHours.AgentHours')
    .setTpl('ReportsInterfaceBundle:TicketSatisfaction:index.html')