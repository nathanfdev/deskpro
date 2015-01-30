define -> [
  '$scope',
  '$modalInstance',
  'DashboardService',
  'DashboardWidgetService',
  'dashboard',
  'report',
  ($scope,
   $modalInstance,
   DashboardService,
   DashboardWidgetService,
   dashboard,
   report) ->

    DashboardService.setWidgetService(DashboardWidgetService)
    $scope.dashboard = dashboard
    $scope.report = report

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveReport = ->
      $modalInstance.close($scope.report)

    $scope.addNewWidget = ->

    $scope.removeWidget = (widget) ->
      widget.deleted = true
]