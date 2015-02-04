define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope',
  '$stateParams',
  '$q',
  '$modal',
  'DashboardsInfo',
  'DashboardWidgetService',
  ($scope,
   $stateParams
   $q,
   $modal,
   DashboardsInfo,
   DashboardWidgetService,
  ) ->
    $scope.loaded = false

    report_id = parseInt($stateParams.report_id)

    $scope.gridsterOptions =
      margins: [20, 20],
      columns: 10,
      draggable:
        enabled: false
        handle: 'h3'
        stop: (event, $element, $widget) ->
          DashboardWidgetService.saveWidget($widget)
      resizable:
        enabled: false
        handles: ['n', 'e', 's', 'w', 'se', 'sw']
        stop: (event, $element, $widget) ->
          DashboardWidgetService.saveWidget($widget)

    DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
      $scope.report = loadedReport

      DashboardsInfo.getDashboardList().then((dbs) ->
        $scope.dashboard = Arrays.find(dbs, (x) -> x.id == loadedReport.dashboard_id)
        $scope.loaded = true
      )
    )

    $scope.toggleLayoutEdit = () ->
      $scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled
      $scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled
      $scope.layoutEditing = !$scope.layoutEditing
  ]