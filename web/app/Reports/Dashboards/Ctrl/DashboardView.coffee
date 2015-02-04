define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$state', '$stateParams', '$q', '$modal', 'DashboardsInfo', 'DashboardService'
  ($scope, $state, $stateParams, $q, $modal, DashboardsInfo) ->

    dashboard_id = parseInt($stateParams.dashboard_id)

    $scope.loaded = false
    $scope.dashboard = null

    DashboardsInfo.getDashboardList().then((dbs) ->
      $scope.dashboards = dbs
      $scope.dashboard = Arrays.find(dbs, (x) -> x.id == dashboard_id)

      if $scope.dashboard.reports[0]?
        $state.go('reports.dashboards.view.report', { report_id: $scope.dashboard.reports[0].id})
      else
        $state.go('reports.dashboards.view.empty')

      $scope.loaded = true
    )

    # fetches perm info
    DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( (db) ->
      $scope.dashboard = db
    )
  ]