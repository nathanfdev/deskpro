define -> [
  '$scope', '$q', '$modal', '$rootScope', 'DashboardService', 'DashboardWidgetService', 'DashboardPermissionsService',
  ($scope, $q, $modal, $rootScope, DashboardService, DashboardWidgetService, DashboardPermissionsService) ->

    DashboardService.setWidgetService(DashboardWidgetService)

    ###
    # intialize
    ###

    #just to simplify at the first time, but who knows :)
    $scope.reports = DashboardService.storage.reports

    $scope.dashboards = []
    $scope.permissions = {}

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

    ###
    # Dashboard modal instasnces
    ###

    ###
    # Creates modal instance and resolves dashboard as null, so ModalInstace controller will have to create new object
    ###
    $scope.newDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:edit_dashboard.html',
        controller: "Reports.App.ModalDashboard"
        resolve:
          dashboard: () ->
            return null
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
        DashboardService.saveDashboard(dashboard)


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
      $scope.currentReport = report

    $scope.addReport = () ->
      $scope.addingReport = true
      if $scope.reports.length < 1
        DashboardService
        .getReports

    $scope.createReport = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard:add_report.html',
        controller: "Reports.App.ModalReport"
        resolve:
          dashboard: () ->
            return $scope.dashboard
          dashboards: () ->
            return $scope.dashboards
      }
      modalInstance.result.then (newReport) ->
        newReport.dashboard_id = $scope.dashboard.id
        # This is very ugly hack ;(
        if newReport.cloned?
          $scope.dashboard.reports.push newReport
          $scope.changeReport newReport
        else
          DashboardService
            .createReport newReport
            .then (report) ->
              $scope.dashboard.reports.push report
              $scope.changeReport report

    $scope.cloneReport = (report) ->
      DashboardService
        .cloneReport report, $scope.dashboard.id
        .then (report) ->
          $scope.dashboard.reports.push report
          console.log $scope.dashboard.reports
          $scope.changeReport report

    $scope.removeReport = (report) ->
      DashboardService
      .removeReport(report)
      .then (reports) ->
        $scope.dashboard.reports = reports




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