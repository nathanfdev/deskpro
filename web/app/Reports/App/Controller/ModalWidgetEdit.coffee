define -> [
  '$scope', '$modalInstance', 'DashboardWidgetService', 'report', 'widget',
  ($scope, $modalInstance, DashboardWidgetService, report, widget) ->

    $scope.widget = widget
    $scope.report = report
    $scope.state = 'stats'
    $scope.searchText = ''
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

    $scope.filterBySearchText = (value) ->
      if $scope.searchText == ''
        return true
      else
        search = $scope.searchText.toLocaleLowerCase()
        if value.title.toLocaleLowerCase().indexOf(search) >= 0
          return true
        else
          return true for label in value.labels when label.toLocaleLowerCase().indexOf(search) >= 0
          return false
]