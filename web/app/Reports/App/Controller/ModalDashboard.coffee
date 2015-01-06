define -> [
  '$scope',
  '$modalInstance',
  'DashboardService',
  'DashboardPermissionsService',
  'DashboardWidgetService',
  'dashboard',
  'currentReport',
  'permissions',
  'state',
  ($scope,
   $modalInstance,
   DashboardService,
   DashboardPermissionsService,
   DashboardWidgetService,
   dashboard,
   currentReport,
   permissions,
   state) ->

    DashboardService.setWidgetService(DashboardWidgetService)
    $scope.permissions = permissions
    $scope.state = state
    if dashboard?
      $scope.dashboard = dashboard
      $scope.dashboard.permissions = permissions
    else
      $scope.dashboard =
        title: '',
        reports: [],
        loaded: false,
        default: false,
        permissions: []

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveDashboard = ->
      $modalInstance.close($scope.dashboard)

    $scope.removeReport = (report) ->
      index = DashboardService.findReportIndex report, $scope.dashboard.reports
      if index >= 0
        $scope.dashboard.reports[index].deleted = true

    $scope.addNewReport = () ->
      $scope.dashboard.reports.push {widgets:[], title: '', columns: 10, loaded: false, deleted: false}

    $scope.cloneNewReport = () ->
      report =
        widgets:[]
        title: ''
        columns: 10
        loaded: false
        deleted: false
      $scope.dashboard.reports.push report
      report

    $scope.setPermissions = (agent, permission) ->

      if(permission == 1 && agent.permissions > 0)
        agent.permissions = 0
      else if(permission == 2 && agent.permissions < 2)
        agent.permissions = 2
      else if(permission == 2 && agent.permissions == 2)
        agent.permissions = 1
      DashboardPermissionsService.savePermissions(agent, $scope.dashboard)
]