define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) -> [
  '$scope', '$q', '$modalInstance', 'report_id', 'widget', 'DashboardsInfo', 'DashboardService', 'DashboardWidgetService', '$anchorScroll', '$location',
  ($scope, $q, $modalInstance, report_id, widget, DashboardsInfo, DashboardService, DashboardWidgetService, $anchorScroll, $location) ->
    $scope.loaded = false

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    DashboardWidgetService.setDashboardService DashboardService

    if widget
        $scope.widget = widget
    else
      $scope.widget =
        col: "0"
        row: "0"
        data: []
        id: 0
        title: "new widget"
        sizeX: "5"
        sizeY: "2"
        type: null
        widget_id: 0
        widget_variables: null

    if !$scope.widget.type?
      $scope.state = 'stats'
    else
      $scope.state = switch widget.type
        when 'simple_stat', 'group_stats_table', 'group_stats_list' then 'stats'
        when 'pie', 'bars', 'simple_bars', 'line', 'simple_lines', 'area', 'simple_area' then 'graphs'
        when 'table' then 'table'
        else 'stats'

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.cancel = -> $modalInstance.dismiss('cancel')

    $scope.saveChoice = ->
      $modalInstance.close $scope.widget

    $scope.makeChoice = (widgetType) ->
      $scope.widget.type = widgetType
      $location.hash(widgetType)
      $anchorScroll()

    $scope.changeState = (state) ->
      $scope.state = switch state
        when 'stats' then state
        when 'graphs' then state
        when 'tables' then state
        else 'stats'
]