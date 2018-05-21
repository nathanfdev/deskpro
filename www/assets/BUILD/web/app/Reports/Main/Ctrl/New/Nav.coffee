define [], () -> [
  '$scope',
  '$state',
  'DashboardService',
  '$modal',
  (
    $scope,
    $state,
    DashboardService,
    $modal
  ) ->

    $scope.hasAccessToBuiltIn = $scope.hasAccessToCustom = false

    $scope.canUseReports = () ->
      return window.DESKPRO_PERSON_PERMS['agent_reports.use'];

    $scope.getDashboardList = (firstLoad = false) ->
      DashboardService.getDashboards().then((dbs) ->
        $scope.dashboards = dbs
        $scope.hasAccessToBuiltIn = dbs.filter((db) => db.is_default).length >= 1
        $scope.hasAccessToCustom = dbs.filter((db) => !db.is_default).length >= 1

        if(firstLoad && $state.includes('reports.dashboards'))
          db = dbs[0]
          if db.reports?.length
            $state.go('reports.dashboards.view.report', { dashboard_id: db.id, report_id: db.reports[0].id })
          else
            $state.go('reports.dashboards.view.index', { dashboard_id: db.id })
      )

    $scope.$watch(
      () -> DashboardService,
      (dbinfo) -> $scope.getDashboardList(),
      true
    )

    $scope.getDashboardList(true)

    $scope.defaultDashboardsFilter = (value) -> value.is_default
    $scope.customDashboardsFilter  = (value) -> !value.is_default

    $scope.openCreate = ->
      $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit-dashboard.html',
        controller: 'Reports.Dashboards.Modals.EditDashboard'
        resolve:
          dashboard_id: -> null
          modal_options: -> {
            activeTab: 'info'
          }
      }
]