/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Arrays'], Arrays => [
  '$scope', '$state', '$stateParams', '$q', '$modal', 'DashboardsInfo', 'DashboardService',
  function($scope, $state, $stateParams, $q, $modal, DashboardsInfo, DashboardService) {

    const dashboard_id = parseInt($stateParams.dashboard_id);

    $scope.loaded = false;
    $scope.dashboard = null;
    $scope.reports = [];
    $scope.agents = {};
    $scope.me = {};


    //###################################################################################################################
    // LOADING
    //###################################################################################################################

    const load_promises = [];

    // fetches perm info
    load_promises.push(DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( db => $scope.dashboard = db)
    );
    load_promises.push(DashboardsInfo.getReportsList(dashboard_id).then( function(reports) {
      $scope.reports = reports;
      if ($state.params.report_id) {
        return $state.go('reports.dashboards.view.report', { report_id: $state.params.report_id } );
      } else if (reports.length > 0) {
        return $state.go('reports.dashboards.view.report', { report_id: reports[0].id} );
      } else {
        return $state.go('reports.dashboards.view.empty');
      }
    })
    );
    load_promises.push(DashboardsInfo.getAgents().then( agents => agents.map(agent => { return $scope.agents[agent.id] = agent; }))
    );
    load_promises.push(DashboardsInfo.getMe().then( me => $scope.me = me)
    );

    // just reload info when its been changed
    $scope.$watch(
      () => DashboardsInfo.lastDashboardDetail,
      function() {
        DashboardsInfo.getDashboardDetail($stateParams.dashboard_id).then( db => $scope.dashboard = db);
        return DashboardsInfo.getReportsList(dashboard_id).then( reports => $scope.reports = reports);
      }
      , true
    );

    $q.all(load_promises).then(() => $scope.loaded = true);

    $scope.filterPermissions = permission => permission.person || permission.team || permission.department;

    $scope.getInitials = function(agent) {
      if ((agent == null)) { return '?'; }
      const first    = agent.first_name;
      const last     = agent.last_name;
      const initials = (first && first.length ? first[0] : '') + (last && last.length ? last[0] : '');

      return initials || '?';
    };

    //###################################################################################################################
    // MODAL HANDLERS
    //###################################################################################################################

    $scope.openEdit = activeTab =>
      $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:edit-dashboard.html',
        controller: 'Reports.Dashboards.Modals.EditDashboard',
        resolve: {
          dashboard_id() { return dashboard_id; },
          modal_options() { return {
            activeTab: activeTab || 'info'
          }; }
        }
      })
    ;

    $scope.openCreateReport = function() {
      if ($scope.dashboard.is_default) { return; }
      const modalInstance = $modal.open({
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:add-report.html",
        controller: 'Reports.Dashboards.Modals.AddReport',
        resolve: {
          report() {
            return {
              dashboard_id: $scope.dashboard.id,
              title:   'new report',
              options: {}
            };
          }
        }
      });
      return modalInstance.result.then(result =>
        DashboardService.createReport(result).then(function(report) {
          DashboardsInfo.getDashboardDetail($stateParams.dashboard_id, true).then( function(db) {
            $scope.dashboard = db;
            return $state.go('reports.dashboards.view.report', { report_id: report.id});
          });
          return DashboardsInfo.getReportsList(dashboard_id).then( reports => $scope.reports = reports);
        })
      );
    };

    $scope.deleteDashboard = function() {
      if ($scope.dashboard.is_default) { return; }
      const modalInstance = $modal.open({
        templateUrl: "ReportsInterfaceBundle:Index:modal-confirm.html",
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.title   = 'Confirm discard';
          $scope.message = 'Are you sure you want to delete this dashboard?';

          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.confirm = () => $modalInstance.close();
        }
        ]
      });
      return modalInstance.result.then(
        () => {
          return DashboardService.deleteDashboard($scope.dashboard).then(() => $state.go('reports.dashboards.index'));
      });
    };

    return $scope.canEdit = function() {
      if (!$scope.dashboard || !$scope.me.person) { return false; }
      if ($scope.me.person.can_admin || $scope.me.person.can_reports) {
        return true;
      }
      for (let permission of Array.from($scope.dashboard.permissions)) {
        if (((permission.person === $scope.me.person.id) || (!permission.person && !permission.team && !permission.department)) && (permission.name === 'full')) { return true; }
      }
      return false;
    };
  }
  ] );
