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

    $scope.restore = -> $scope.selected = false

    $scope.saveChoice = ->
      DashboardWidgetService.addWidget(report, $scope.widget)
        .then \
          () ->
            $scope.error = false
            $modalInstance.close {add: true}
        , (response) ->
            if (response.errors?.fields?.type?.errors[0])
              $scope.error = 'You have to choose widget type before adding'
            else if (response.errors?.fields?.title?.errors[0])
              $scope.error = 'Name your widget'
            else if (response.errors)
              $scope.error = 'There is an error when was adding a widget, please pick up another widget type or another widget'

    $scope.makeChoice = (displayType) ->
      $scope.widget.type = displayType
      type = switch displayType
        when 'pie', 'simple_area', 'simple_bars', 'simple_lines', 'bubble', 'gauge' then 'graph'
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
            $scope.selected = true
            $scope.widgetPreview                 = response.data.data
            $scope.widgetPreview.rendered_result = $scope.widgetPreview.rendered_result[0]
            $scope.widgetPreview.type            = type
]
