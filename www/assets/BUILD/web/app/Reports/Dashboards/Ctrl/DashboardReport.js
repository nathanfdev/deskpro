define(['DeskPRO/Util/Arrays'], Arrays => [
  '$scope',
  '$state',
  '$stateParams',
  '$q',
  '$http',
  '$modal',
  'DashboardsInfo',
  'DashboardWidgetService',
  'DashboardService',
  function($scope,
   $state,
   $stateParams,
   $q,
   $http,
   $modal,
   DashboardsInfo,
   DashboardWidgetService,
   DashboardService,
  ) {
    $scope.loaded = false;
    $scope.report = {
      dashboard: 0,
      options: {},
      variables: []
    };
    $scope.widgets = [];
    $scope.groupParams = DashboardWidgetService.groupParams;
    $scope.layoutEditing = false;

    const report_id = parseInt($stateParams.report_id);
    $scope.report_id = parseInt($stateParams.report_id);
    $scope.autoRefresh = localStorage.getItem(`dp.dashboard.autoRefresh.${$scope.report_id}`) === '1' ? 1 : 0;
    if ($scope.autoRefresh) {
      $scope.refreshInterval = setInterval(() => {
        return $scope.refreshDashboardReport();
      }
      , 10*60*1000);
    }

    $scope.gridsterOptions = {
      margins: [13, 13],
      width: 10000,
      columns: 150,
      colWidth: 50,
      pushing: true,
      floating: true,
      swapping: true,
      draggable: {
        enabled: false,
        handle: '.box-header'
      },
      resizable: {
        enabled: false,
        handles: ['n', 'e', 's', 'w', 'se', 'sw']
      }
    };

    const load_promises = [];
    load_promises.push(DashboardsInfo.getReportDetail(report_id).then(function(loadedReport) {
      $scope.report = loadedReport;
      $scope.report.variables = loadedReport.variables;
      $scope.had_empty_vars = !loadedReport.variables || (loadedReport.variables.length === 0);

      return DashboardsInfo.getDashboardDetail(loadedReport.dashboard).then( db => $scope.dashboard = db);
    })
    );

    load_promises.push(DashboardWidgetService.getWidgets(report_id).then(widgets => $scope.widgets = widgets)
    );

    $scope.me = {};
    load_promises.push(DashboardsInfo.getMe().then( me => $scope.me = me)
    );

    $scope.agents = [];
    load_promises.push(DashboardsInfo.getAgents().then( agents => agents.map(agent => { return $scope.agents[agent.id] = agent; }))
    );

    $q.all(load_promises).then(() => $scope.updateReportVariables(false, () => { return $scope.loaded = true; }));

    // just reload info when its been changed
    $scope.$watch(
      () => DashboardsInfo.lastDashboardDetail,
      function() {
        const reloadPromises = [];
        if ($scope.report.dashboard) {
          reloadPromises.push(DashboardsInfo.getDashboardDetail($scope.report.dashboard).then( db => $scope.dashboard = db)
          );
        }

        reloadPromises.push(DashboardsInfo.getReportDetail(report_id).then(function(loadedReport) {
          $scope.report = loadedReport;
          $scope.report.variables = loadedReport.variables;
          return $scope.updateReportVariables();
        })
        );

        return $q.all(reloadPromises).then(function() {
          if ($scope.had_empty_vars) {
            $scope.had_empty_vars = false;
            DashboardService.saveReportVars($scope.report, true).then( () => $scope.refreshDashboardReport());
          }

        }
        , () => $state.go('reports.dashboards.view.empty'));
      }
      , true
    );

    //###################################################################################################################
    // UI handlers
    //###################################################################################################################

    $scope.toggleLayoutEdit = function() {
      $scope.layoutEditing = !$scope.layoutEditing;
//      $scope.gridsterOptions.pushing = $scope.layoutEditing
//      $scope.gridsterOptions.floating = $scope.layoutEditing
      $scope.gridsterOptions.draggable.enabled = $scope.layoutEditing;
      return $scope.gridsterOptions.resizable.enabled = $scope.layoutEditing;
    };


    /*
     * Staff for removing widget from dashboard. Works if and only if the dashboard.layoutEditing is switched on
     */
    $scope.removeWidget = function(widget) {
      const removeWidget = function() {
        const index = DashboardWidgetService.getIndexById($scope.widgets, widget.id);
        return DashboardWidgetService
          .removeWidget(widget)
          .then(function() {
            $scope.widgets.splice(index, 1);
            return DashboardsInfo.getReportDetail($scope.report.id, true).then(function(loadedReport) {
              $scope.report.variables = loadedReport.variables;
              return $scope.updateReportVariables(true);
            });
        });
      };

      if ($scope.layoutEditing) {
        return $modal.open({
          templateUrl: "ReportsInterfaceBundle:Index:modal-confirm.html",
          controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {

            $scope.title   = 'Confirm discard';
            $scope.message = 'Are you sure you want to delete this widget?';

            $scope.dismiss = () => $modalInstance.dismiss();

            return $scope.confirm = function() {
              removeWidget();
              return $modalInstance.dismiss();
            };
          }
          ]
        });
      }
    };

    $scope.download = function(widget) {
      window.open($http.formatApi2Url(`/dashboard_report_widgets/${widget.id}/download/csv`));
      return true;
    };

    $scope.downloadPdf = function(widget) {
      if (widget.widget_type === 'graph') {
        widget.print();
      } else {
       window.open($http.formatApi2Url(`/dashboard_report_widgets/${widget.id}/download/pdf`));
     }
      return true;
    };

    //###################################################################################################################
    // MODAL HANDLERS
    //###################################################################################################################

    $scope.openWidgetChoose = function(report, widget, reportWidget) {
      if ($scope.dashboard.is_default) { return; }
      const modalInstance = $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:widget-type-choose.html',
        controller: 'Reports.Dashboards.Modals.ChooseWidget',
        resolve: {
          report() { return report; },
          widget() { return widget; },
          reportWidget() { return reportWidget; }
        }
      });

      return modalInstance.result.then(function(result) {
        if ((result != null ? result.back : undefined) === true) {
          $scope.openAddWidget(widget);
        }
        if ((result != null ? result.add : undefined) === true) {
          DashboardsInfo.getReportDetail($scope.report.id, true).then(function(loadedReport) {
            $scope.report.variables = loadedReport.variables;
            return $scope.updateReportVariables();
          });
          return DashboardWidgetService.getWidgets(report_id).then(function(widgets) {
            $scope.widgets = widgets;
            return $scope.updateReportVariables();
          });
        }
      });
    };

    $scope.openAddWidget = function(widget) {
      const modalInstance = $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:add-widget.html',
        controller: 'Reports.Dashboards.Modals.AddWidget',
        resolve: {
          report_id() { return report_id; },
          widget() { return widget; }
        }
      });

      return modalInstance.result.then(result => $scope.openWidgetChoose(result.report, result.widget, result.reportWidget));
    };

    $scope.openEditWidget = function(widget) {
      const modalInstance = $modal.open({
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:edit-widget.html",
        controller: 'Reports.Dashboards.Modals.EditWidget',
        resolve: {
          widget() {
            return widget;
          }
        }
      });
      return modalInstance.result.then(function(result) {
        const index = DashboardWidgetService.getIndexById($scope.widgets, widget.id);
        $scope.widgets[index] = result;
        return DashboardWidgetService.saveWidget(result).then(function() {});
      });
    };


    $scope.editReportModal = function(report) {
      const modalInstance = $modal.open({
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:edit-report.html",
        controller: 'Reports.Dashboards.Modals.EditReport',
        resolve: {
          report() {
            return report;
          }
        }
      });
      modalInstance.result.then(result =>
        DashboardService.saveReport(result).then(savedReport =>
          DashboardsInfo.getReportDetail(report_id).then(function(loadedReport) {
            $scope.report = loadedReport;
            $scope.report.variables = loadedReport.variables;
            return $state.go('reports.dashboards.view.report', { report_id: loadedReport.id});
          })
        )
      );
      return modalInstance.result.catch(function(reason) {
        if (reason === 'scheduled') {
          return DashboardsInfo.getReportDetail(report_id).then(function(loadedReport) {
            $scope.report = loadedReport;
            $scope.report.variables = loadedReport.variables;
            return $state.go('reports.dashboards.view.report', { report_id: loadedReport.id});
          });
        }
      });
    };



    $scope.changeReportLevelVar = function() {
      $scope.loaded = false;

      return DashboardService.saveReportVars($scope.report, true).then( () => $scope.refreshDashboardReport());
    };

    $scope.canViewAllAgents = function() {
      const permission = $scope.dashboard.permissions.filter(permission => permission.person === parseInt(window.DP_PERSON_ID))[0];
      return permission && permission.view_all;
    };

    $scope.updateReportVariables = function(forceUpdate, cb = null) {

      if (forceUpdate == null) { forceUpdate = false; }
      if (!$scope.report || (!$scope.widgets.length && !forceUpdate)|| !$scope.dashboard || !$scope.me || !$scope.groupParams) {
        if (cb) { cb(); }
        return;
      }

      const vars = [];
      $scope.widgets.map(widget =>
        (widget.widget_variables || []).map(function(variable) {
          let cloneVar;
          if (vars.map(reportVar => reportVar.name).indexOf(variable.name) !== -1) {
            return;
          }

          if (variable.value === 'from_report_value') {
            cloneVar = $.extend({}, variable);
            if ((variable.type === 'dates') && $scope.groupParams[variable.type]) {
              cloneVar.value = $scope.groupParams[variable.type][Object.keys($scope.groupParams[variable.type])[0]][0];
            } else if ((variable.type === 'values') && $scope.groupParams[variable.type]) {
              cloneVar.value = Object.keys($scope.groupParams[variable.type][variable.field_type])[0];
            } else if ($scope.groupParams[variable.type]) {
              cloneVar.value = $scope.groupParams[variable.type][variable.field_type][Object.keys($scope.groupParams[variable.type][variable.field_type])[0]][0];
            }

            angular.forEach($scope.report.variables, function(reportVar) {
              if (reportVar.name === cloneVar.name) {
                return cloneVar.value = reportVar.value;
              }
            });

            return vars.push(cloneVar);
          } else if (((variable.type === 'values') && ((variable.field_type === 'agent') || (variable.field_type === 'agent_team'))) && $scope.dashboard.is_agent) {
            cloneVar = $.extend({}, variable);
            if (variable.field_type === 'agent') {
              cloneVar.value = parseInt($scope.me.person.id);
            } else if (variable.field_type === 'agent_team') {
              cloneVar.value = parseInt($scope.me.person.primary_team);
            }

            return vars.push(cloneVar);
          }
        })
      );

      $scope.report.variables = vars;

      if (vars.length === 0) {
        $scope.had_empty_vars = false;
      }

      if (cb) { return cb(); }
    };

    $scope.canEdit = function() {
      if (!$scope.dashboard || !$scope.me.person) { return false; }
      if ($scope.me.person.can_admin || $scope.me.person.can_reports) {
        return true;
      }
      for (let permission of Array.from($scope.dashboard.permissions)) {
        if (((permission.person === $scope.me.person.id) || (!permission.person && !permission.team && !permission.department)) && (permission.name === 'full')) { return true; }
      }
      return false;
    };

    $scope.refreshDashboardReport = function() {
      const reloadPromises = [];
      $scope.widgets = [];
      reloadPromises.push(DashboardsInfo.getReportDetail($scope.report.id, true).then(loadedReport => $scope.report = loadedReport)
      );

      reloadPromises.push(DashboardWidgetService.getWidgets(report_id).then(widgets => $scope.widgets = widgets)
      );

      return $q.all(reloadPromises).then(() => $scope.updateReportVariables(false, () => { return $scope.loaded = true; }));
    };

    return $scope.toggleAutoRefreshReport = function() {
      $scope.autoRefresh = !$scope.autoRefresh;
      const newVal = $scope.autoRefresh ? 1 : 0;
      localStorage.setItem(`dp.dashboard.autoRefresh.${$scope.report_id}`, newVal);

      if ($scope.autoRefresh) {
        return $scope.refreshInterval = setInterval(() => {
          return $scope.refreshDashboardReport();
        }
        , 10*60*1000);
      } else {
        return clearInterval($scope.refreshInterval);
      }
    };
  }

] );
