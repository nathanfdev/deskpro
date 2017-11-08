define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], () -> [
  '$scope', '$q', '$modalInstance', 'report', 'widget', 'reportWidget', 'DashboardsInfo', 'DashboardService', 'DashboardWidgetService', '$anchorScroll', '$location',
  ($scope, $q, $modalInstance, report, widget, reportWidget, DashboardsInfo, DashboardService, DashboardWidgetService, $anchorScroll, $location) ->

    ####################################################################################################################
    # LOADING
    ####################################################################################################################
    DashboardWidgetService.setDashboardService DashboardService
    $scope.widget = widget

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.cancel = -> $modalInstance.dismiss 'cancel'

    $scope.saveChoice = ->
      DashboardWidgetService.addWidget report, $scope.widget
      $modalInstance.close()

    $scope.makeChoice = (displayType) ->
      $scope.widget.type = displayType

    $scope.displayTypeAvailable = (displayType) ->
      available = false
      for reportDisplayType in reportWidget.display_types when reportDisplayType == displayType then available = true
      return available
]
