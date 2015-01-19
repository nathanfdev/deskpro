define ['DeskPRO/Util/Arrays',], (Arrays) -> [
  '$scope', '$modalInstance', 'DashboardWidgetService', 'report', 'widget',
  ($scope, $modalInstance, DashboardWidgetService, report, widget) ->

    $scope.widget = widget
    $scope.report = report
    $scope.state = 'stats'
    $scope.searchText = ''
    $scope.reports = []
    $scope.labels = []
    $scope.selectedLabels = 0
    $scope.groupParams = DashboardWidgetService.groupParams


    DashboardWidgetService.getReports().then (result) ->
      $scope.reports = result.reports.filter (report)->
        return true for display_type in report.display_types when display_type == $scope.widget.type
      $scope.labels.push {title: label, active: false} for label in result.labels
      $scope.filterByLabels()

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

    $scope.changeWidgetParams = (params) ->
      console.log params
      $scope.widget.variables = params


    # Mmmm... super script to toggle labels by it's title or label itself
    $scope.toggleLabel = (label) ->
      if typeof label == 'string'
        index = -1
        index = Arrays.findIndex $scope.labels,
          (v) ->
            return true if v.title == label
        if index >= 0
          label = $scope.labels[index]
      if label?
        currentLabel = $scope.labels[$scope.labels.indexOf(label)]
        if currentLabel.active == true
          $scope.selectedLabels -= 1
        else
          $scope.selectedLabels += 1
        currentLabel.active = !currentLabel.active
        $scope.filterByLabels()

    $scope.filterByLabels = () ->
      if $scope.selectedLabels != 0
        $scope.prefilteredReports = $scope.reports.filter (report) ->
          return true for label in report.labels when $scope.isActiveLabel(label)
      else
        $scope.prefilteredReports = $scope.reports


    $scope.isActiveLabel = (label) ->
      index = -1
      index = Arrays.findIndex $scope.labels,
        (v) ->
          return true if v.title == label and v.active == true
      if index >= 0
        return true
      else
        return false

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