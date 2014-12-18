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
    else
      $scope.dashboard =
        title: '',
        reports: [],
        loaded: false,
        default: false,
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
      DashboardService
        .removeReport(report)
        .then (reports) ->
          $scope.dashboard.reports = reports
          if(report.id == currentReport.id)
            currentReport = $scope.dashboard.reports[0]

    $scope.setPermissions = (agent, permission) ->
      if(permission == 1 && agent.permissions > 0)
        agent.permissions = 0
      else if(permission == 2 && agent.permissions < 1)
        agent.permissions = 2
      else if(permission == agent.permissions == 2)
        agent.permissions = 1
      else if(permission == agent.permissions == 1)
        agent.permissions = 0
      DashboardPermissionsService.savePermissions(agent, $scope.dashboard)
#
#    $scope.createDashboard = () ->
#      dashboard =
#        name: $scope.newDashboard.name,
#        options:
#          margins: [20, 20],
#          columns: parseInt($scope.newDashboard.cols),
#          floating: true,
#          swapping: false,
#          draggable:
#            handle: 'h3'
#          resizable:
#            enabled: true,
#            handles: ['n', 's', 'w', 'ne', 'se', 'sw', 'nw'],
#          #start: function (event, $element, widget) { }, // optional callback fired when resize is started,
#          #resize: function (event, $element, widget) { }, // optional callback fired when item is resized,
#            stop: $scope.updateWidgetSize # optional callback fired when item is finished resizing
#        widgets: []
#
#      $modalInstance.close(dashboard)
#
#    $scope.updateWidgetSize = (event, $element, widget) ->
#      w = widget;
#      k = w.$$hashKey;
#
#      alert 'found' for widget, i in $scope.dashboard.widgets when widget.$$hashKey == k
]