define([], () => [
  '$scope',
  '$state',
  'DashboardService',
  '$modal',
  function (
    $scope,
    $state,
    DashboardService,
    $modal
  ) {
    $scope.hasAccessToBuiltIn = ($scope.hasAccessToCustom = false);

    $scope.canUseReports = () => window.DESKPRO_PERSON_PERMS['agent_reports.use'];

    $scope.getDashboardList = () =>
      DashboardService.getDashboards().then((dbs) => {
        $scope.dashboards = dbs;
        $scope.hasAccessToBuiltIn = dbs.filter(db => db.is_default).length >= 1;
        return $scope.hasAccessToCustom = dbs.filter(db => !db.is_default).length >= 1;
      })
    ;

    $scope.$watch(
      () => DashboardService,
      dbinfo => $scope.getDashboardList(),
      true
    );

    $scope.getDashboardList(true);

    $scope.defaultDashboardsFilter = value => value.is_default;
    $scope.customDashboardsFilter  = value => !value.is_default;

    return $scope.openCreate = () =>
      $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit-dashboard.html',
        controller:  'Reports.Dashboards.Modals.EditDashboard',
        resolve:     {
          dashboard_id() { return null; },
          modal_options() {
            return {
              activeTab: 'info'
            };
          }
        }
      })
    ;
  }
]);
