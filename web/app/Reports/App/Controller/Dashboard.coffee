define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope',
  '$document',
  '$q',
  '$modal',
  '$rootScope',
  'DashboardService',
  'ReportsOverviewService',
  'DashboardWidgetService',
  ($scope,
   $document,
   $q,
   $modal,
   $rootScope,
   DashboardService,
   ReportsOverviewService,
   DashboardWidgetService,
   ) ->

    DashboardService.setWidgetService(DashboardWidgetService)

    ###
    # intialize
    ###
    ###body = $document.find 'body'
    ###

    #just to simplify at the first time, but who knows :)
    $scope.reports = DashboardService.storage.reports
    $scope.expandedDashboard = []
    $scope.dashboards = []
    $scope.layoutEditing = false;
    $scope.newReport =
      title: ''
      loaded: false
      id: 0
      widgets: []
      columns: 10

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

    DashboardService.getDashboards().then (dbs) ->
      $scope.dashboards = dbs
      $scope.dashboard = null
      $scope.currentReport = null
      if $scope.dashboards.length > 0
        $scope.changeDashboard($scope.dashboards[0])

    ###
    # Operations about dashboards
    ###
    $scope.changeDashboard = (newDb) ->
      deferred = $q.defer()
      if newDb.loaded is false
        DashboardService
        .getDashboard newDb
        .then (db) =>
          $scope.dashboard = db
          $scope.changeReport $scope.dashboard.reports[0]
          deferred.resolve $scope.dashboard
          return deferred.promise
      else
        $scope.dashboard = newDb
        $scope.changeReport $scope.dashboard.reports[0]
        deferred.resolve $scope.dashboard
        return deferred.promise

    $scope.deleteDashboard = (dashboard) ->
      DashboardService.deleteDashboard dashboard
      .then () =>
        Arrays.removeValue $scope.dashboards, dashboard, 1
        $scope.changeDashboard $scope.dashboards[$scope.dashboards.length - 1]

    ###
    # ui staff
    ###

    $scope.isEmptyData = (data) ->
      if !data or data.length < 1
        return true
      else
        return false

    $scope.toggleLayoutEdit = () ->
      if !$scope.dashboard.default
        $scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled
        $scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled
        $scope.layoutEditing = !$scope.layoutEditing

    $scope.expandDashboard = (dashboard) ->
      if dashboard.loaded is false
        DashboardService.getDashboard(dashboard)
      $scope.expandedDashboard[dashboard.id] = true

    $scope.collide = (dashboard) ->
      $scope.expandedDashboard[dashboard.id] = false

    $scope.isExpanded = (dashboard) ->
      $scope.expandedDashboard[dashboard.id]? and $scope.expandedDashboard[dashboard.id] is true

    $scope.permissionsFilter = (value) ->
      if value.permissions > 0
        return true
      else
        return false

    ###
    # Dashboard modal instasnces
    # TODO mb refactor to modal service?
    # TODO think about state and modals (have to spend some time with experiments)
    ###

    ###
    # Creates modal instance and resolves dashboard as null, so ModalInstance controller will have to create new object
    ###
    $scope.newDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:new_dashboard.html',
        controller: "Reports.App.ModalDashboard"
        resolve:
          dashboard: () ->
            return null
          dashboards: () ->
            return $scope.dashboards
          currentReport: () ->
            return $scope.currentReport
          state: () ->
            return 'info'
      }

      modalInstance.result.then (dashboard) ->
        DashboardService.saveDashboard(dashboard).then () ->
          $scope
          .changeDashboard $scope.dashboards[$scope.dashboards.length - 1]
          .then ->
            $scope.editDashboardModal()



    ###
    # Just as previous one, but the dashboard object resolves to current dashboard
    ###
    $scope.editDashboardModal = (state) ->
      reportsLength = $scope.dashboard.reports.length
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalDashboard"
        resolve:
          dashboard: () ->
            return $scope.dashboard
          dashboards: () ->
            return $scope.dashboards
          currentReport: () ->
            return $scope.currentReport
          state: () ->
            if state? then state else 'info'
      }

      modalInstance.result.then (dashboard) ->
        DashboardService
          .saveDashboard dashboard
          .then (saved) ->
            $scope.dashboard = saved
            if $scope.currentReport.deleted
              $scope.changeReport $scope.dashboard.reports[0]
            else if reportsLength < $scope.dashboard.reports.length
              $scope.changeReport $scope.dashboard.reports[$scope.dashboard.reports.length - 1]



    ###
    # Compilation from previous tow methods - get current dashboard and crate new with it parameters, then
    # open editDashboardModal with newly created dashboard.
    ###
    $scope.cloneDashboardModal = () ->
      DashboardService
      .cloneDashboard $scope.dashboard
      .then ->
        clonedOne = $scope.dashboards[$scope.dashboards.length - 1]
        $scope.changeDashboard(clonedOne).then (dashboard)->
          modalInstance = $modal.open {
            templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
            controller: "Reports.App.ModalDashboard"
            resolve:
              dashboard: () ->
                db = JSON.parse(JSON.stringify(dashboard))
                return db
          }
          modalInstance.result.then (dashboard) =>
            DashboardService.saveDashboard(dashboard).then () =>
              $scope.dashboards.push dashboard
              $scope
              .changeDashboard $scope.dashboards[$scope.dashboards.length - 1]
              .then ->
                $scope.editDashboardModal()

    ###
      # Operations about reports
    ###
    $scope.changeReport = (report) ->
      if report?
        DashboardService
          .getReport report
          .then (loadedReport) ->
            $scope.currentReport = loadedReport
      else
        $scope.currentReport = {widgets:[]}

    $scope.addReport = () ->
      if $scope.reports.length < 1
        DashboardService
        .getReports

    $scope.createReport = () ->
      $scope.newReport.dashboard_id = $scope.dashboard.id
      DashboardService
      .createReport $scope.newReport
      .then (report) ->
        $scope.dashboard.reports.push report
        $scope.changeReport report

    $scope.cloneReport = (report) ->
      if report? and report.id?
        DashboardService
          .cloneReport report, $scope.dashboard.id
          .then (clonedReport) ->
            $scope.dashboard.reports.push clonedReport
            $scope.changeReport clonedReport

    $scope.removeReport = (report) ->
      DashboardService
      .removeReport(report)
      .then (reports) ->
        $scope.dashboard.reports = reports

    $scope.editReportModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_report.html',
        controller: "Reports.App.ModalReport"
        resolve:
          dashboard: () ->
            return $scope.dashboard
          report: () ->
            return $scope.currentReport
      }

      modalInstance.result.then (report) ->
        DashboardService
        .saveReport report

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
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard:widget_type_choice.html",
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
        templateUrl: "ReportsInterfaceBundle:Dashboard:add_widget.html",
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
        templateUrl: "ReportsInterfaceBundle:Dashboard:edit_widget.html",
        controller: "Reports.App.ModalWidgetEdit"
        resolve:
          widget: () ->
            widget
      }
      modalInstance.result.then (result) ->
        DashboardWidgetService.saveWidget result
]