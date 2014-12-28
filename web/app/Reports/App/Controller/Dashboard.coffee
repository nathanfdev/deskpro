define -> [
  '$scope',
  '$q',
  '$modal',
  '$rootScope',
  'DashboardService',
  'DashboardWidgetService',
  'DashboardPermissionsService',
  ($scope, $q, $modal, $rootScope, DashboardService, DashboardWidgetService, DashboardPermissionsService) ->

    DashboardService.setWidgetService(DashboardWidgetService)

    ###
    # intialize
    ###

    #just to simplify at the first time, but who knows :)
    $scope.reports = DashboardService.storage.reports
    $scope.expandedDashboard = []
    $scope.dashboards = []
    $scope.permissions = {}
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
      DashboardPermissionsService.getPermissions(newDb).then (permissions) ->
        $scope.permissions = permissions
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


    ###
    # ui staff
    ###

    $scope.deleteDashboard = (dashboard) ->
      DashboardService.deleteDashboard dashboard
      .then () =>
        $scope.changeDashboard Arrays.last $scope.dashboards

    $scope.expandDashboard = (dashboard) ->
      if dashboard.loaded is false
        DashboardService.getDashboard(dashboard)
      $scope.expandedDashboard[dashboard.id] = true

    $scope.collide = (dashboard) ->
      $scope.expandedDashboard[dashboard.id] = false

    $scope.isExpanded = (dashboard) ->
      $scope.expandedDashboard[dashboard.id]? and $scope.expandedDashboard[dashboard.id] is true

    ###
    # Dashboard modal instasnces
    ###

    ###
    # Creates modal instance and resolves dashboard as null, so ModalInstance controller will have to create new object
    ###
    $scope.newDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalDashboard"
        resolve:
          dashboard: () ->
            return null
          currentReport: () ->
            return $scope.currentReport
          permissions: () ->
            return []
          state: () ->
            return 'info'
      }

      modalInstance.result.then (dashboard) =>
        DashboardService.saveDashboard(dashboard).then () =>
          $scope
          .changeDashboard $scope.dashboards[$scope.dashboards.length - 1]
          .then ->
            $scope.editDashboardModal()



    ###
    # Just as previous one, but the dashboard object resolves to current dashboard
    ###
    $scope.editDashboardModal = (state) ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalDashboard"
        resolve:
          dashboard: () ->
            return $scope.dashboard
          currentReport: () ->
            return $scope.currentReport
          permissions: () ->
            return $scope.permissions
          state: () ->
            if state? then state else 'info'
      }

      modalInstance.result.then (dashboard) =>
        DashboardService
          .saveDashboard dashboard
          .then (saved) ->
            $scope.dashboard = saved
            console.log(saved)
            if $scope.currentReport.deleted
              $scope.changeReport $scope.dashboard.reports[0]


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


    ###
    # ui staff for reports
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
      $scope.addingReport = true
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

    $scope.removeWidget = (widget) ->
      index = DashboardWidgetService.getIndexById $scope.currentReport.widgets, widget.id
      DashboardWidgetService
        .removeWidget(widget)
        .then () ->
          $scope.currentReport.widgets.splice(index, 1)


    $scope.toggleLayoutEdit = () ->
      if !$scope.dashboard.default
        $scope.gridsterOptions.draggable.enabled = !$scope.gridsterOptions.draggable.enabled
        $scope.gridsterOptions.resizable.enabled = !$scope.gridsterOptions.resizable.enabled
        $scope.layoutEditing = !$scope.layoutEditing


#    $scope.removeWidget = (widget) ->
#      DashboardWidgetService
#      .removeWidget(widget) \
#        Arrays.findAndRemove \
#          $scope.dashboard.widgets
#        , (v, i) ->
#          if v.id is widget.id then true else false
#        , 1
#
#    $scope.NewWidgetModal = ->
#      modalInstance = $modal.open {
##        templateUrl: getTemplatePath('Dashboard/new_widget.html'),
#        controller: "Reports.App.ModalInstance"
#        resolve:
#          db: () =>
#            return $scope.dashboard
#      }
#
#      modalInstance.result.then (widgetInfo) =>
#        $scope.addWidget(widgetInfo)
#
#    $scope.addWidget = (widgetInfo) ->
#      widget =
#        name: widgetInfo.title,
#        col: 0,
#        row: 0,
#        sizeY: widgetInfo.y,
#        sizeX: widgetInfo.x,
]