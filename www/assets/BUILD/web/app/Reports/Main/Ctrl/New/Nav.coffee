define [], () -> [
  '$scope',
  '$state',
  'DashboardsInfo',
  '$modal',
  (
    $scope,
    $state,
    DashboardsInfo,
    $modal
  ) ->

    $scope.getDashboardList = (firstLoad = false) ->
      DashboardsInfo.getDashboardList().then((dbs) ->
        $scope.dashboards = dbs

        if(firstLoad && $state.includes('reports.dashboards'))
          db = dbs[0]
          if db.reports?.length
            $state.go('reports.dashboards.view.report', { dashboard_id: db.id, report_id: db.reports[0].id })
          else
            $state.go('reports.dashboards.view.index', { dashboard_id: db[0].id })
      )

    $scope.$watch(
      () -> DashboardsInfo,
      (dbinfo) -> $scope.getDashboardList(),
      true
    )

    $scope.getDashboardList(true)

    $scope.defaultDashboardsFilter = (value) -> value.is_default
    $scope.customDashboardsFilter  = (value) -> !value.is_default

    $scope.openCreate = ->
      $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit-dashboard.html',
        controller: 'Reports.Dashboards.Modals.EditDashboard'
        resolve:
          dashboard_id: -> null
          modal_options: -> {
            activeTab: 'info'
          }
      }
]