define -> [
  '$scope', '$state', '$stateParams', 'DashboardService',
  ($scope,   $state,   $stateParams,   DashboardService) ->
    DashboardService.getDashboards().then((dbs) ->
      $scope.dashboards = dbs
    )
    DashboardService.getDashboardById($stateParams.dashboard_id).then((db) ->
      $scope.dashboard = db
    )
]