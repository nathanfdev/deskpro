define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope',
  '$state',
  '$stateParams',
  '$q',
  '$modal',
  'DashboardsInfo',
  'DashboardWidgetService',
  ($scope,
   $state,
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

    # just reload info when its been changed
    $scope.$watch('dashboard.reports_version_id', (n, o) ->
      return if not o

      p1 = DashboardsInfo.getDashboardList().then((dbs) ->
        $scope.dashboard = Arrays.find(dbs, (x) -> x.id == loadedReport.dashboard_id)
      )

      p2 = DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
        $scope.report = loadedReport
      )

      $q.all([p1, p2]).then(->
        # all ok
        return
      , ->
        #invalid, maybe removed the dashboard?
        if $scope.dashboard.reports[0]?
          $state.go('reports.dashboards.view.report', { report_id: $scope.dashboard.reports[0].id})
        else
          $state.go('reports.dashboards.view.empty')
      )
    )

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.toggleLayoutEdit = () ->
      $scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled
      $scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled
      $scope.layoutEditing = !$scope.layoutEditing

    ###
    # Staff for removing widget from dashboard. Works if and only if the dashboard.layoutEditing is switched on
    ###
    $scope.removeWidget = (widget) ->
      if $scope.layoutEditing
        index = DashboardWidgetService.getIndexById $scope.report.widgets, widget.id
        DashboardWidgetService
        .removeWidget(widget)
        .then () ->
          $scope.dashboard.reports_version_id++
          $scope.report.widgets.splice(index, 1)

    ####################################################################################################################
    # MODAL HANDLERS
    ####################################################################################################################

    $scope.openWidgetChoose = (widget) ->
      modalInstance = $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:widget-type-choose.html',
        controller: 'Reports.Dashboards.Modals.ChooseWidget'
        resolve:
          report_id: -> report_id
          widget: -> return if widget? then widget else null
      })
      modalInstance.result.then (saved) ->
        $scope.openAddWidget saved

    $scope.openAddWidget = (widget) ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:add-widget.html',
        controller: 'Reports.Dashboards.Modals.AddWidget'
        resolve:
          report_id: -> report_id
          widget: -> widget
      }

      modalInstance.result.then (result) ->
        if result.changeType? and result.changeType == true
          $scope.openWidgetChoose result.widget

    $scope.openEditWidget = (widget) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:edit-widget.html",
        controller: 'Reports.Dashboards.Modals.EditWidget'
        resolve:
          widget: () ->
            widget
      }
      modalInstance.result.then (result) ->
        DashboardWidgetService.saveWidget result
  ]