define -> [
  '$scope', '$modalInstance', 'report', 'widget',
  ($scope, $modalInstance, report, widget) ->
    $scope.widget = widget
    $scope.report = report

    if $scope.widget? and !$scope.widget.type?
      $scope.state = 'stats'
    else
      $scope.state = switch widget.type
        when 'simple_stat', 'group_stats_table', 'group_stats_list' then 'stats'
        when 'pie', 'bars', 'simple_bars', 'line', 'simple_lines', 'area', 'simple_area' then 'graphs'
        when 'table' then 'table'
        else 'stats'

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveChoice = ->
      $modalInstance.close({widget: $scope.widget, report: $scope.report})

    $scope.makeChoice = (widgetType) ->
      $scope.widget.type = widgetType

    $scope.changeState = (state) ->
      $scope.state = switch state
        when 'stats' then state
        when 'graphs' then state
        when 'tables' then state
        else 'stats'
]