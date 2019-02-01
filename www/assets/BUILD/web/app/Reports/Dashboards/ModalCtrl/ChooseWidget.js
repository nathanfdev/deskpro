define(() => [
  '$scope', '$q', '$modalInstance', 'report', 'widget', 'reportWidget', 'DashboardsInfo', 'DashboardService', 'DashboardWidgetService',
  function ($scope, $q, $modalInstance, report, widget, reportWidget, DashboardsInfo, DashboardService, DashboardWidgetService) {
    // ###################################################################################################################
    // LOADING
    // ###################################################################################################################
    DashboardWidgetService.setDashboardService(DashboardService);
    $scope.widget = widget;
    $scope.widgetPreview  = null;
    $scope.reportWidget   = reportWidget;

    // ###################################################################################################################
    // UI handlers
    // ###################################################################################################################

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    $scope.back = () => $modalInstance.close({ back: true });

    $scope.restore = () => $scope.selected = false;

    $scope.saveChoice = () =>
      DashboardWidgetService.addWidget(report, $scope.widget)
        .then(
          () => {
            $scope.error = false;
            return $modalInstance.close({ add: true });
          }
        , (response) => {
          if (__guard__(__guard__(response.errors != null ? response.errors.fields : undefined, x1 => x1.type), x => x.errors[0])) {
            return $scope.error = 'You have to choose widget type before adding';
          } else if (__guard__(__guard__(response.errors != null ? response.errors.fields : undefined, x3 => x3.title), x2 => x2.errors[0])) {
            return $scope.error = 'Name your widget';
          } else if (response.errors) {
            return $scope.error = 'There is an error when was adding a widget, please pick up another widget type or another widget';
          }
        })
    ;

    return $scope.makeChoice = function (displayType) {
      $scope.widget.type = displayType;
      const type = (() => {
        switch (displayType) {
          case 'pie': case 'simple_area': case 'simple_bars': case 'simple_lines': case 'bubble': case 'gauge': return 'graph';
          case 'simple_stat': return 'simple_stat';
          default: return 'table';
        }
      })();

      if ((widget.widget_id === 'advanced') && widget.js_code) {
        try {
          eval(widget.js_code);
        } catch (e) {
          console.log(e);
        }

        if (promise && promise.then) {
          return promise.then(response =>
            $scope.widgetPreview = {
              rendered_result: response,
              type
            });
        }
      } else {
        const widgetToTest = angular.copy(reportWidget);
        for (let index = 0; index < widgetToTest.variables.length; index++) {
          const widgetVariable = widgetToTest.variables[index];
          if ($scope.widget.variables[widgetVariable.name]) {
            widgetToTest.variables[index].value = $scope.widget.variables[widgetVariable.name].value;
          }
        }
        widgetToTest.display_types = [displayType];
        widgetToTest.jsonTable = displayType === 'table';
        return DashboardWidgetService
          .testWidget(widgetToTest, $scope.widget)
          .then((response) => {
            $scope.selected = true;
            $scope.widgetPreview                 = response.data.data;
            $scope.widgetPreview.rendered_result = $scope.widgetPreview.rendered_result[0];
            return $scope.widgetPreview.type            = type;
          });
      }
    };
  }
]);

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
