define(['DeskPRO/Util/Arrays'], Arrays => [
  '$scope',
  '$state',
  '$stateParams',
  '$q',
  '$http',
  '$modal',
  'Api2',
  'DashboardWidgetService',
  function ($scope,
    $state,
    $stateParams,
    $q,
    $http,
    $modal,
    Api2,
    DashboardWidgetService
  ) {
    $scope.widgets = [];
    $scope.groupParams = {};
    $scope.gridsterOptions = {
      margins:   [13, 13],
      width:     10000,
      columns:   150,
      colWidth:  50,
      pushing:   false,
      floating:  false,
      swapping:  true,
      draggable: {
        enabled: false,
        handle:  '.box-header'
      },
      resizable: {
        enabled: false,
        handles: ['n', 'e', 's', 'w', 'se', 'sw']
      }
    };

    $scope.loadReport = function () {
      $scope.loaded = false;
      const getWidgets = function () {
        const deferred = $q.defer();
        DashboardWidgetService.widgetsResults = {};

        Api2
          .sendGet(`/dashboard_view/${window.DP_AUTH_CODE}/reports/${$scope.report.id}/widgets`)
          .then((resp) => {
            const widgets = resp.data.data;

            for (var widget of Array.from(widgets)) {
              widget.sizeX = widget.size_x;
              widget.sizeY = widget.size_y;

              DashboardWidgetService.widgetsResults[widget.id] = $q.defer();
            }

            // load widget rendered results in batches
            const widgetIds = widgets.map(widget => widget.id);
            const idBatches = ((() => {
              const result = [];
              while (widgetIds.length) {
                result.push(widgetIds.splice(0, 10));
              }
              return result;
            })());
            for (const idBatch of Array.from(idBatches)) {
              Api2
                .sendGet(`/dashboard_view/${window.DP_AUTH_CODE}/reports/${$scope.report.id}/widgets?include=rendered_result&inline_sideloads=1&ids=${idBatch}`)
                .then((batchResp) => {
                  const batchWidgets = batchResp.data.data;
                  return (() => {
                    const result1 = [];
                    for (widget of Array.from(batchWidgets)) {
                      result1.push(DashboardWidgetService.widgetsResults[widget.id].resolve(widget.rendered_result));
                    }
                    return result1;
                  })();
                });
            }

            return deferred.resolve(widgets);
          });

        return deferred.promise;
      };

      const load_promises = [];
      load_promises.push(getWidgets().then(widgets => $scope.widgets = widgets)
      );

      return $q.all(load_promises).then(() => $scope.loaded = true);
    };

    for (const report of Array.from($scope.dashboard.reports)) {
      if ($scope.report_id === report.id) {
        $scope.report = report;
      }
    }

    $scope.loadReport();

    $scope.changeReport = function (report) {
      $scope.report = report;
      return $scope.loadReport();
    };

    $scope.refreshDashboardReport = () => $scope.loadReport();

    return $scope.toggleAutoRefreshReport = function () {
      $scope.autoRefresh = !$scope.autoRefresh;
      const newVal = $scope.autoRefresh ? 1 : 0;
      localStorage.setItem(`dp.dashboard.autoRefresh.${$scope.report_id}`, newVal);

      if ($scope.autoRefresh) {
        return $scope.refreshInterval = setInterval(() => $scope.refreshDashboardReport()
        , 10 * 60 * 1000);
      }
      return clearInterval($scope.refreshInterval);
    };
  }

]);
