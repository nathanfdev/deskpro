define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$state', '$stateParams', '$q', '$modal', 'DashboardsInfo', 'DashboardService'
  ($scope, $state, $stateParams, $q, $modal, DashboardsInfo) ->
    $scope.loaded = false
    $scope.dashboard = null

    DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( (db) ->
      $scope.dashboard = db
      $scope.loaded = true
    )
  ]