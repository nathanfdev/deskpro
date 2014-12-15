define -> [
  '$scope', '$timeout', '$modal', '$rootScope', 'DashboardService', 'DashboardWidgetService',
  ($scope, $timeout, $modal, $rootScope, DashboardService, DashboardWidgetService) ->

    DashboardService.setWidgetService(DashboardWidgetService)

    #just to simplify at the first time, but who knows :)
    $scope.reports = DashboardService.storage.reports
    $scope.expandedDashboard = []

    DashboardService.getDashboards().then (dbs) ->
      $scope.dashboards = dbs
      $scope.dashboard = null
      $scope.currentReport = null
      if $scope.dashboards.length > 0
        $scope.changeDashboard($scope.dashboards[0])

    $scope.changeDashboard = (newDb) ->
      if newDb.loaded is false
        DashboardService
        .getDashboard newDb
        .then (db) =>
          $scope.dashboard = db
          $scope.changeReport $scope.dashboard.reports[0]
      else
        $scope.dashboard = newDb
        $scope.changeReport $scope.dashboard.reports[0]

    $scope.newDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalInstance"
        resolve:
          dashboard: () =>
            return null
      }

      modalInstance.result.then (dashboard) =>
        DashboardService.saveDashboard(dashboard).then () =>
          $scope.dashboards.push dashboard
          $scope.changeDashboard $scope.dashboards[$scope.dashboards.length - 1]
          $scope.editDashboardModal()

    $scope.cloneDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalInstance"
        resolve:
          dashboard: () ->
            db = JSON.parse(JSON.stringify($scope.dashboard))
            delete db.id
            return db
      }

      modalInstance.result.then (dashboard) =>
        DashboardService.saveDashboard(dashboard).then () =>
          $scope.dashboards.push dashboard
          $scope.changeDashboard $scope.dashboards[$scope.dashboards.length - 1]
          $scope.editDashboardModal()


    $scope.editDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalInstance"
        resolve:
          dashboard: () =>
            return $scope.dashboard
      }

      modalInstance.result.then (dashboard) =>
        DashboardService.saveDashboard(dashboard)

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
    # Operations about reports
    ###

    $scope.changeReport = (report) ->
      $scope.currentReport = report

    $scope.addReport = () ->
      $scope.addingReport = true
      if $scope.reports.length < 1
        DashboardService
        .getReports

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