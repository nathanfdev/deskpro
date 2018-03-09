define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$state', '$stateParams', '$q', '$modal', 'DashboardsInfo', 'DashboardService'
  ($scope, $state, $stateParams, $q, $modal, DashboardsInfo, DashboardService) ->

    dashboard_id = parseInt($stateParams.dashboard_id)

    $scope.loaded = false
    $scope.dashboard = null
    $scope.reports = []
    $scope.agents = {}

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    load_promises = []
    load_promises.push DashboardsInfo.getDashboardList().then((dbs) ->
      $scope.dashboards = dbs
      dashboard = Arrays.find(dbs, (x) -> x.id == dashboard_id)

      $state.go('reports.dashboards.view.empty')
      if not $scope.dashboard
        $scope.dashboard = dashboard
    )

    # fetches perm info
    load_promises.push DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( (db) ->
      $scope.dashboard = db
    )
    load_promises.push DashboardsInfo.getReportsList(dashboard_id).then( (reports) ->
      $scope.reports = reports
      if reports.length > 0
        $state.go('reports.dashboards.view.report', { report_id: reports[0].id} )
    )
    load_promises.push DashboardsInfo.getAgents().then( (agents) ->
      agents.map((agent) => $scope.agents[agent.id] = agent)
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
      DashboardsInfo.getReportsList(dashboard_id).then( (reports) ->
        $scope.reports = reports
      )
    )

    $q.all(load_promises).then(-> $scope.loaded = true)

    $scope.filterPermissions = (permission) ->
      permission.person || permission.team || permission.department

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
              title:   'new report'
              options: {}
            }
      }
      modalInstance.result.then (result) ->
        DashboardService.createReport(result).then (report) ->
          DashboardsInfo.getDashboardDetail($stateParams.dashboard_id, true).then( (db) ->
            $scope.dashboard = db
            $state.go('reports.dashboards.view.report', { report_id: report.id})
          )
          DashboardsInfo.getReportsList(dashboard_id).then( (reports) ->
            $scope.reports = reports
          )

    $scope.deleteDashboard = () ->
      if $scope.dashboard.is_default then return
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Index:modal-confirm.html",
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.title   = 'Confirm discard'
          $scope.message = 'Are you sure you want to delete this dashboard?'

          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.confirm = ->
            $modalInstance.close()
        ]
      }
      modalInstance.result.then(
        () =>
          DashboardService.deleteDashboard($scope.dashboard)
          $state.go('reports.dashboards.view.empty')
      )
  ]
