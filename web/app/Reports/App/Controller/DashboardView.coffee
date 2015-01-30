define -> [
  '$scope', '$state', '$stateParams', 'DashboardService',
  ($scope,   $state,   $stateParams,   DashboardService) ->
    DashboardService.getDashboardById($stateParams.dashboard_id).then((db) ->
      $scope.dashboard = db
    )
]