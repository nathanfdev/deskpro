define ['DeskPRO/Util/Arrays',], (Arrays) -> [
  '$scope', '$q', '$modalInstance', 'DashboardsInfo', 'DashboardWidgetService', 'report_id', 'TemplateManager',
  ($scope, $q, $modalInstance, DashboardsInfo, DashboardWidgetService, report_id, TemplateManager) ->

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    load_promises = []
    $scope.loaded = false

    $scope.widget =
      col: "0"
      row: "0"
      data: []
      id: 0
      title: "new widget"
      sizeX: "8"
      sizeY: "5"
      type: null
      widget_id: 0
      widget_variables: null
      js_code: "// your custom code should return promise object,\n// e.g.:\nvar promise = $.get('http://');"

    $scope.state = 'stats'
    $scope.searchText = ''
    $scope.reports = []
    $scope.labels = []
    $scope.selectedLabels = 0
    $scope.vars = {}

    $scope.groupParams = DashboardWidgetService.groupParams

    load_promises.push DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
      $scope.report = loadedReport
    )

    TemplateManager.queue('ReportsInterfaceBundle:Dashboard/Modal:add-widget-variables.html')

    load_promises.push DashboardWidgetService.getReports().then (result) ->
      $scope.reports = result.reports
      for label in result.labels
        $scope.labels.push {title: label, active: false }
        $scope.toggleLabel(label)

    $q.all(load_promises).then(-> $scope.loaded = true)

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.isActiveLabel = (label) ->
      index = -1
      index = Arrays.findIndex $scope.labels,
        (v) ->
          return true if v.title == label and v.active == true
      if index >= 0
        return true
      else
        return false

    ####################################################################################################################
    # Sort'n'filter
    ####################################################################################################################

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

    ###
    # Filter prefilteredReports by labels
    ###
    $scope.filterByLabels = () ->
      if $scope.selectedLabels != 0
        $scope.prefilteredReports = $scope.reports.filter (report) ->
          return true for label in report.labels when $scope.isActiveLabel(label)
      else
        $scope.prefilteredReports = $scope.reports

    ###
    # Filter prefilteredReports by labels
    # Note that this will filter by label.title too (@see OneNote->DR->ImplementingDesign->(4) Adding Widgets)
    ###
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

    $scope.isAdvancedMatchSearchText = () ->
      if $scope.searchText == ''
        return true
      else
        search = $scope.searchText.toLocaleLowerCase()
        if 'Advanced: Widget from arbitrary Javascript code'.toLocaleLowerCase().indexOf(search) >= 0
          return true

      return false

    ####################################################################################################################
    # SAVE
    ####################################################################################################################

    $scope.makeChoice = (report) ->
      if report == 'advanced'
        $scope.widget.widget_id = 'advanced'
      else
        $scope.reportWidget     = report
        $scope.widget.widget_id = report.id
        $scope.widget.variables = $scope.vars[report.id]

    $scope.chooseType = () ->
      if $scope?.widget?.widget_id
        $scope.widget.variables = $scope.vars[$scope.widget.widget_id]
        $modalInstance.close({reportWidget: $scope.reportWidget, report: $scope.report, widget: $scope.widget})

    $scope.changeWidgetParams = (params, report) ->
      $scope.vars[report.id] = params
]
