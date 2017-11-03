define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$state', '$stateParams', '$q', '$modal', 'DashboardsInfo', 'DashboardService'
  ($scope, $state, $stateParams, $q, $modal, DashboardsInfo, DashboardService) ->

    dashboard_id = parseInt($stateParams.dashboard_id)

    $scope.loaded = false
    $scope.dashboard = null

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    DashboardsInfo.getDashboardList().then((dbs) ->
      $scope.dashboards = dbs
      dashboard = Arrays.find(dbs, (x) -> x.id == dashboard_id)

      if dashboard.reports[0]?
        $state.go('reports.dashboards.view.report', { report_id: dashboard.reports[0].id})
      else
        $state.go('reports.dashboards.view.empty')

      $scope.loaded = true

      if not $scope.dashboard
        $scope.dashboard = dashboard
    )

    # fetches perm info
    DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( (db) ->
      $scope.dashboard = db
    )

    # just reload info when its been changed
    $scope.$watch('dashboard.version_id + \'.\' + dashboard.reports_version_id', (n, o) ->
      return if not o
      DashboardsInfo.getDashboardList().then((dbs) ->
        $scope.dashboards = dbs
        $scope.dashboard = Arrays.find(dbs, (x) -> x.id == dashboard_id)
      )
      DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( (db) ->
        $scope.dashboard = db
      )
    )

    ####################################################################################################################
    # FILTERS
    ####################################################################################################################

    $scope.permissionsFilter = (value) ->
      if value.permissions > 0
        return true
      else
        return false

    ####################################################################################################################
    # MODAL HANDLERS
    ####################################################################################################################

    $scope.openEdit = (activeTab) ->
      $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit-dashboard.html'
        controller: 'Reports.Dashboards.Modals.EditDashboard'
        resolve: {
          dashboard_id: -> dashboard_id
          modal_options: -> {
            activeTab: activeTab || 'info'
          }
        }
      })

    $scope.openCreateReport = () ->
      if $scope.dashboard.is_default then return
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:add-report.html"
        controller: 'Reports.Dashboards.Modals.AddReport'
        resolve:
          report: () ->
            {
              dashboard_id: $scope.dashboard.id
              title:        'new report'
              options:
                columns:    24
            }
      }
      modalInstance.result.then (result) ->
        DashboardService.createReport(result).then (report) ->
          DashboardsInfo.getDashboardDetail($stateParams.dashboard_id, true).then( (db) ->
            $scope.dashboard = db
            $state.go('reports.dashboards.view.report', { report_id: report.id})
          )
  ]
