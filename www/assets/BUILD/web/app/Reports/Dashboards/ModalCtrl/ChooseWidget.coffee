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

    $scope.back = -> $modalInstance.close {back: true}

    $scope.saveChoice = ->
      DashboardWidgetService.addWidget(report, $scope.widget).then () ->
        $modalInstance.close {add: true}

    $scope.makeChoice = (displayType) ->
      $scope.widget.type = displayType
      type = switch displayType
        when 'pie', 'simple_area', 'simple_bars', 'simple_lines', 'bubble' then 'graph'
        when 'simple_stat' then 'simple_stat'
        else 'table'

      if widget.widget_id == 'advanced' and widget.js_code
        try
          eval(widget.js_code)
        catch e
          console.log(e)

        if promise and promise.then
          promise.then (response) ->
            $scope.widgetPreview = {
              rendered_result: response
              type: type
            }
      else
        widgetToTest = angular.copy reportWidget
        for widgetVariable, index in widgetToTest.variables
          if $scope.widget.variables[widgetVariable.name]
            widgetToTest.variables[index].value = $scope.widget.variables[widgetVariable.name].value
        widgetToTest.display_types = [displayType]
        widgetToTest.jsonTable = if displayType == 'table' then true else false
        DashboardWidgetService
          .testWidget widgetToTest, $scope.widget
          .then (response) ->
            $scope.widgetPreview                 = response.data.data
            $scope.widgetPreview.rendered_result = $scope.widgetPreview.rendered_result[0]
            $scope.widgetPreview.type            = type
]
