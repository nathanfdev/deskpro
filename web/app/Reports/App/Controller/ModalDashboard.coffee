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

#    $scope.newWidget =
#      title: ''
#      sizeX: 0
#      sizeY: 1
#
#    $scope.wtype = ''
#    $scope.selectedSource = {}
#    $scope.selectedSource.name = 'Select source'
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
#
#    DashboardWidgetService
#      .getReports()
#      .then \
#        (response) =>
#          $scope.dataSources = response.data.reports
#          console.log($scope.dataSources)
#        ,(reason) ->
#            alert('Unable to load data from file. ' + reason.statusText)
#            console.error('wow, take it easy, laddie')
#
#    $scope.createWidget = () ->
#      widget =
#        title: $scope.newWidget.title,
#        wtype: $scope.wtype,
#        dsName: $scope.selectedSource.name,
#        x: $scope.newWidget.sizeX,
#        y: $scope.newWidget.sizeY
#
#      $modalInstance.close(widget);
#
#    $scope.chooseWtype = (wtype) ->
#      $scope.wtype = wtype;
#
    $scope.cancel = ->
      $modalInstance.dismiss('cancel')
#
#    $scope.setSource: (dataSource) ->
#      DashboardWidgetService.selectedSource.id = dataSource.id
#
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