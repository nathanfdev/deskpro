define -> [
  '$scope', '$q', '$modalInstance', 'report', 'widget', 'reportWidget', 'DashboardsInfo', 'DashboardService', 'DashboardWidgetService',
  ($scope, $q, $modalInstance, report, widget, reportWidget, DashboardsInfo, DashboardService, DashboardWidgetService) ->

    ####################################################################################################################
    # LOADING
    ####################################################################################################################
    DashboardWidgetService.setDashboardService DashboardService
    $scope.widget = widget
    $scope.widgetPreview  = null
    $scope.reportWidget   = reportWidget

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.cancel = -> $modalInstance.dismiss 'cancel'

    $scope.saveChoice = ->
      DashboardWidgetService.addWidget report, $scope.widget
      $modalInstance.close()

    $scope.makeChoice = (displayType) ->
      $scope.widget.type = displayType
      widgetToTest = angular.copy reportWidget
      for widgetVariable, index in widgetToTest.variables
        if $scope.widget.variables[widgetVariable.name]
          widgetToTest.variables[index].value = $scope.widget.variables[widgetVariable.name].value
      widgetToTest.display_types = [displayType]
      widgetToTest.jsonTable = if displayType == 'table' then true else false
      DashboardWidgetService
        .testWidget widgetToTest, $scope.widget
        .then (response) ->
          $scope.widgetPreview                 = response.data
          $scope.widgetPreview.rendered_result = $scope.widgetPreview.rendered_result[0]
          $scope.widgetPreview.type            = switch displayType
            when 'pie', 'simple_area', 'simple_bars', 'simple_lines' then 'graph'
            else 'table'
]
