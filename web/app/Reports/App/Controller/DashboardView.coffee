define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$state', '$stateParams', '$q', '$modal', 'DashboardsInfo', 'DashboardService'
  ($scope, $state, $stateParams, $q, $modal, DashboardsInfo, DashboardService) ->

    dashboard_id = parseInt($stateParams.dashboard_id)

    DashboardsInfo.getDashboardList().then((dbs) ->
      $scope.dashboards = dbs
    )
    DashboardService.getDashboardById(dashboard_id).then((db) ->
      $scope.dashboard = db
      $state.go('app.reports.dashboards.view.report', { report_id: $scope.dashboard.reports[0].id});
    )

    $scope.expandedDashboard = []


    ###
    # Creates modal instance and resolves dashboard as null, so ModalInstance controller will have to create new object
    ###
    $scope.newDashboardModal = () ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:new_dashboard.html',
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
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit_dashboard.html',
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
          reportIndex = -1
          reportIndex = Arrays.findIndex dashboard.reports
          , (v, i) ->
            if v.id is $scope.currentReport.id then true else false
          $scope.dashboard = saved

          if reportIndex < 0
            $state.go('app.reports.dashboards.view.report', { report_id: $scope.dashboard.reports[0].id});
          else if reportsLength < $scope.dashboard.reports.length
            $state.go('app.reports.dashboards.view.report', { report_id: $scope.dashboard.reports[$scope.dashboard.reports.length - 1].id})



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
            templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit_dashboard.html',
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
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit_report.html',
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

    $scope.expandDashboard = (dashboard) ->
      if dashboard.loaded is false
        DashboardService.getDashboard(dashboard)
      $scope.expandedDashboard[dashboard.id] = true

    $scope.collide = (dashboard) ->
      $scope.expandedDashboard[dashboard.id] = false

    $scope.isExpanded = (dashboard) ->
      $scope.expandedDashboard[dashboard.id]? and $scope.expandedDashboard[dashboard.id] is true
]