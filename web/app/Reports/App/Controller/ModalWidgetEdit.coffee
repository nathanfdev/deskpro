define -> [
  '$scope', '$modalInstance', 'DashboardWidgetService', 'report', 'widget',
  ($scope, $modalInstance, DashboardWidgetService, report, widget) ->

    $scope.widget = widget
    $scope.report = report
    $scope.state = 'stats'
    $scope.reports = []


    DashboardWidgetService.getReports().then (result) ->
      $scope.reports = result

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveWidget = ->
      $modalInstance.close({widget: $scope.widget, report: $scope.report})

    $scope.makeChoice = (report) ->
      $scope.widget.widget_id = report.id

    $scope.insert = () ->
      $modalInstance.close({widget: $scope.widget, report: $scope.report})

    $scope.changeType = () ->
      $scope.widget.changeType = true
      $modalInstance.close({report: $scope.report, widget: $scope.widget})
]