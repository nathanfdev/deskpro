define -> [
  '$scope', '$modalInstance','DashboardService', 'DashboardPermissionsService', 'dashboard', 'dashboards',
  ($scope, $modalInstance, DashboardService, DashboardPermissionsService, dashboard, dashboards) ->

    $scope.dashboard = dashboard
    $scope.dashboards = dashboards
    $scope.expandedDashboard = []

    $scope.newReport =
      title: ''
      loaded: false
      id: 0
      widgets: []
      columns: 0

    $scope.expandDashboard = (dashboard) ->
      if dashboard.loaded is false
        DashboardService.getDashboard(dashboard)
      $scope.expandedDashboard[dashboard.id] = true

    $scope.collide = (dashboard) ->
      $scope.expandedDashboard[dashboard.id] = false

    $scope.isExpanded = (dashboard) ->
      $scope.expandedDashboard[dashboard.id]? and $scope.expandedDashboard[dashboard.id] is true

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveReport = ->
      $modalInstance.close(report)

    $scope.createReport = ->
      $modalInstance.close $scope.newReport

    $scope.cloneReport = (report) ->
      DashboardService
      .cloneReport report, $scope.dashboard.id
      .then (report) ->
        $modalInstance.close(report)
]