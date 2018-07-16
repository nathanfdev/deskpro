define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope',
  '$state',
  '$stateParams',
  '$q',
  '$http',
  '$modal',
  'Api2',
  'DashboardWidgetService',
  ($scope,
    $state,
    $stateParams
    $q,
    $http,
    $modal,
    Api2,
    DashboardWidgetService
  ) ->
    $scope.widgets = []
    $scope.groupParams = {}
    $scope.gridsterOptions =
      margins: [13, 13],
      width: 10000,
      columns: 150,
      colWidth: 50,
      pushing: false,
      floating: false,
      swapping: true,
      draggable:
        enabled: false
        handle: '.box-header'
      resizable:
        enabled: false
        handles: ['n', 'e', 's', 'w', 'se', 'sw']

    $scope.loadReport = () ->
      $scope.loaded = false
      getWidgets = () ->
        deferred = $q.defer()
        DashboardWidgetService.widgetsResults = {}

        Api2
          .sendGet "/dashboard_view/#{window.DP_AUTH_CODE}/reports/#{$scope.report.id}/widgets"
          .then (resp) =>
            widgets = resp.data.data;

            for widget in widgets
              widget.sizeX = widget.size_x
              widget.sizeY = widget.size_y

              DashboardWidgetService.widgetsResults[widget.id] = $q.defer()

            # load widget rendered results in batches
            widgetIds = widgets.map((widget) => widget.id)
            idBatches = (widgetIds.splice(0, 10) while widgetIds.length)
            for idBatch in idBatches
              Api2
                .sendGet "/dashboard_view/#{window.DP_AUTH_CODE}/reports/#{$scope.report.id}/widgets?include=rendered_result&inline_sideloads=1&ids=#{idBatch}"
                .then (batchResp) =>
                  batchWidgets = batchResp.data.data;
                  for widget in batchWidgets
                    DashboardWidgetService.widgetsResults[widget.id].resolve(widget.rendered_result)

            deferred.resolve(widgets)

        return deferred.promise

      load_promises = []
      load_promises.push getWidgets().then((widgets) ->
        $scope.widgets = widgets
      )

      $q.all(load_promises).then(->
        $scope.loaded = true
      )

    for report in $scope.dashboard.reports
      if $scope.report_id == report.id
        $scope.report = report

    $scope.loadReport()

    $scope.changeReport = (report) ->
      $scope.report = report
      $scope.loadReport()

    $scope.refreshDashboardReport = () ->
      $scope.loadReport()

    $scope.toggleAutoRefreshReport = () ->
      $scope.autoRefresh = !$scope.autoRefresh
      newVal = if $scope.autoRefresh then 1 else 0
      localStorage.setItem('dp.dashboard.autoRefresh.'+$scope.report_id, newVal)

      if $scope.autoRefresh
        $scope.refreshInterval = setInterval(=>
          $scope.refreshDashboardReport()
        , 10*60*1000)
      else
        clearInterval($scope.refreshInterval)

]
