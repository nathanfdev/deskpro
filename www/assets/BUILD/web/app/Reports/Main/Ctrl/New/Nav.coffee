define [], () -> [
  '$scope',
  '$state',
  'DashboardsInfo',
  (
    $scope,
    $state,
    DashboardsInfo,
  ) ->
    console.log('1')

    DashboardsInfo.getDashboardList().then((dbs) ->
      $scope.dashboards = dbs
#      db = dbs[0]
#
#      if db.reports?.length
#        $state.go('reports.dashboards.view.report', { dashboard_id: db.id, report_id: db.reports[0].id })
#      else
#        $state.go('reports.dashboards.view.index', { dashboard_id: db[0].id })
    )
]