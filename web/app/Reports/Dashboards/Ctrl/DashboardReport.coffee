define -> [
  '$scope',
  '$stateParams',
  '$q',
  '$modal',
  'DashboardService',
  'DashboardWidgetService',
  ($scope,
   $stateParams
   $q,
   $modal,
   DashboardService,
   DashboardWidgetService,
  ) ->
    $scope.is_loading = true
    DashboardService.setWidgetService(DashboardWidgetService)
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

    DashboardService.getReportById(report_id).then((loadedReport) ->
      $scope.is_loading = false
      $scope.currentReport = loadedReport
      $scope.$parent.currentReport = $scope.currentReport
    )

    $scope.toggleLayoutEdit = () ->
      if !$scope.$parent.dashboard.is_default
        $scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled
        $scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled
        $scope.layoutEditing = !$scope.layoutEditing

    ###
    # Operations about widgets
    ###

    $scope.removeWidget = (widget) ->
      if $scope.layoutEditing
        index = DashboardWidgetService.getIndexById $scope.currentReport.widgets, widget.id
        DashboardWidgetService
        .removeWidget(widget)
        .then () ->
          $scope.currentReport.widgets.splice(index, 1)

    $scope.typeWidgetModal = (report, widget) ->
      if !$scope.$parent.dashboard.is_default
        modalInstance = $modal.open {
          templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:widget_type_choice.html",
          controller: "Reports.App.ModalWidgetType"
          resolve:
            report: () ->
              return report
            widget: () ->
              return if widget? then widget else {
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
                changeType: false
              }
        }
        modalInstance.result.then (result) ->
          $scope.addWidgetModal result.report, result.widget,

    $scope.addWidgetModal = (report, widget) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:add_widget.html",
        controller: "Reports.App.ModalWidgetAdd"
        resolve:
          report: () ->
            report
          widget: () ->
            widget
      }
      modalInstance.result.then (result) ->
        if result.widget.changeType? and result.widget.changeType == true
          result.widget.changeType = false
          $scope.typeWidgetModal result.report, result.widget
    ###
    else
      $scope.addWidget result.report, result.widget###

    $scope.addWidget = (report, widget) ->
      DashboardWidgetService.addWidget report, widget

    $scope.editWidgetModal = (widget) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:edit_widget.html",
        controller: "Reports.App.ModalWidgetEdit"
        resolve:
          widget: () ->
            widget
      }
      modalInstance.result.then (result) ->
        DashboardWidgetService.saveWidget result
]