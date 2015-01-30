define -> (States) ->
  #----------------------------------------
  # MAIN
  #----------------------------------------

  States.add('app.reports')
    .setUrl('reports')
    .setTpl('ReportsInterfaceBundle:Interface:main.html')
    .setCtrl(['$state', ($state) -> $state.go('app.reports.dashboards.index') ])

  #----------------------------------------
  # DASHBOARDS
  #----------------------------------------

  States.add('app.reports.dashboards')
    .setUrl('/dashboards')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboards.html')
    .setAbstract()

  States.add('app.reports.dashboards.index')
      .setCtrl(['$state', 'DashboardService', ($state, DashboardService) ->
        console.log(DashboardService)
        DashboardService.getDashboards().then((dbs) ->
          $state.go('app.reports.dashboards.view.index', { dashboard_id: dbs[0].id })
        )
      ])

  States.add('app.reports.dashboards.view')
    .setUrl('/{dashboard_id:[0-9]+}')
    .setCtrl('Reports.App.DashboardView')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-view.html')
    .setAbstract()

  States.add('app.reports.dashboards.view.index')
    .setCtrl(['$state', '$stateParams', 'DashboardService', ($state, $stateParams, DashboardService) ->
      DashboardService.getDashboardById($stateParams.dashboard_id).then((db) ->
        if not db?.reports?.length
          $state.go('app.reports.dashboards.view.empty')
        else
          $state.go('app.reports.dashboards.view.report', { dashboard_id: $stateParams.dashboard_id, report_id: db.reports[0].id })
      , ->
        $state.go('app.reports')
      )
    ])

  States.add('app.reports.dashboards.view.empty')
    .setUrl('/empty-dashboard')

  States.add('app.reports.dashboards.view.report')
    .setUrl('/{report_id:[0-9]+}')
    .setCtrl('Reports.App.DashboardReport')
    .setTpl('ReportsInterfaceBundle:Dashboard:dashboard-report.html')
