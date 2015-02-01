define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope',
  '$modalInstance',
  'DashboardService',
  'DashboardPermissionsService',
  'DashboardWidgetService',
  'dashboard',
  'dashboards',
  'currentReport',
  'state',
  ($scope,
   $modalInstance,
   DashboardService,
   DashboardPermissionsService,
   DashboardWidgetService,
   dashboard,
   dashboards,
   currentReport,
   state) ->

    DashboardService.setWidgetService(DashboardWidgetService)
    $scope.state = state
    $scope.expandedDashboard = {}
    $scope.dashboards = dashboards
    if dashboard?
      $scope.dashboard = dashboard
    else
      $scope.dashboard =
        title: '',
        reports: [],
        loaded: false,
        is_default: false,
        permissions: []

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveDashboard = ->
      $modalInstance.close($scope.dashboard)

    $scope.removeReport = (report) ->
      index = DashboardService.findReportIndex report, $scope.dashboard.reports
      if index >= 0
        if report.id
          $scope.dashboard.reports[index].deleted = true
        else
          Arrays.removeValue $scope.dashboard.reports, report, 1


    $scope.addNewReport = () ->
      $scope.dashboard.reports.push {widgets:[], title: '', columns: 10, loaded: false, deleted: false}

    $scope.addClonedReport = () ->
      report =
        widgets:[]
        title: ''
        columns: 10
        loaded: false
        deleted: false
        cloning: true
      $scope.dashboard.reports.push report
      report

    $scope.cloneReport = (report, prototype) ->
      report.prototype_id = prototype.id
      report.title = prototype.title

    $scope.expandDashboard = (reportIndex, dashboard) ->
      if dashboard.loaded is false
        DashboardService.getDashboard(dashboard)
      if !$scope.expandedDashboard[reportIndex]
        $scope.expandedDashboard[reportIndex] = []
      $scope.expandedDashboard[reportIndex][dashboard.id] = true

    $scope.collide = (reportIndex, dashboard) ->
      $scope.expandedDashboard[reportIndex][dashboard.id] = false

    $scope.isExpanded = (reportIndex, dashboard) ->
      $scope.expandedDashboard[reportIndex]? and $scope.expandedDashboard[reportIndex][dashboard.id]? and $scope.expandedDashboard[reportIndex][dashboard.id] is true

    $scope.setPermissions = (agent, permission) ->
      index = DashboardPermissionsService.getIndexById($scope.dashboard.permissions, agent.id)

      if(permission == 1 && agent.permissions == 0)
        $scope.dashboard.permissions[index].permissions = 1
      else if(permission == 1 && agent.permissions > 0)
        $scope.dashboard.permissions[index].permissions = 0
      else if(permission == 2 && agent.permissions < 2)
        $scope.dashboard.permissions[index].permissions = 2
      else if(permission == 2 && agent.permissions == 2)
        $scope.dashboard.permissions[index].permissions = 1
]