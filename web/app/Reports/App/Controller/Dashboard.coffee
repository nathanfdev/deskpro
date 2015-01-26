define -> [
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
        $scope.changeDashboard Arrays.last $scope.dashboards

    ###
    # ui staff
    ###

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
        wdata =
          "type": "serial",
          "theme": "none",
          "dataProvider": [
            {
              "country": "USA",
              "visits": 2025
            },
            {
              "country": "China",
              "visits": 1882
            },
            {
              "country": "Japan",
              "visits": 1809
            },
            {
              "country": "Germany",
              "visits": 1322
            },
            {
              "country": "UK",
              "visits": 1122
            },
            {
              "country": "France",
              "visits": 1114
            },
            {
              "country": "India",
              "visits": 984
            },
            {
              "country": "Spain",
              "visits": 711
            },
            {
              "country": "Netherlands",
              "visits": 665
            },
            {
              "country": "Russia",
              "visits": 580
            },
            {
              "country": "South Korea",
              "visits": 443
            },
            {
              "country": "Canada",
              "visits": 441
            },
            {
              "country": "Brazil",
              "visits": 395
            }
          ],
          "valueAxes": [{
            "gridColor":"#FFFFFF",
            "gridAlpha": 0.2,
            "dashLength": 0
          }],
          "gridAboveGraphs": true,
          "startDuration": 1,
          "graphs": [{
            "balloonText": "[[category]]: <b>[[value]]</b>",
            "fillAlphas": 0.8,
            "lineAlpha": 0.2,
            "type": "column",
            "valueField": "visits"
          }],
          "chartCursor": {
            "categoryBalloonEnabled": false,
            "cursorAlpha": 0,
            "zoomable": false
          },
          "categoryField": "country",
          "categoryAxis": {
            "gridPosition": "start",
            "gridAlpha": 0,
            "tickPosition":"start",
            "tickLength":20
          },
          "exportConfig":{
            "menuTop": 0,
            "menuItems": [{
              "icon": '/lib/3/images/export.png',
              "format": 'png'
            }]
          }
        DashboardService
          .getReport report
          .then (loadedReport) ->
            widget.data = wdata for widget in loadedReport.widgets
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
              name: "new widget"
              sizeX: "5"
              sizeY: "2"
              type: null
              widget_id: 0
              widget_variables: null
              changeType: false
            }
      }
      modalInstance.result.then (result) ->
        $scope.editWidgetModal result.report, result.widget,

    $scope.editWidgetModal = (report, widget) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard:widget_edit.html",
        controller: "Reports.App.ModalWidgetEdit"
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
]