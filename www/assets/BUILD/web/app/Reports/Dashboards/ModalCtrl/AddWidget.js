define(['DeskPRO/Util/Arrays',], Arrays => [
  '$scope', '$q', '$modalInstance', 'DashboardsInfo', 'DashboardWidgetService', 'report_id', 'TemplateManager',
  function ($scope, $q, $modalInstance, DashboardsInfo, DashboardWidgetService, report_id, TemplateManager) {
    // ###################################################################################################################
    // LOADING
    // ###################################################################################################################

    const load_promises = [];
    $scope.loaded = false;

    $scope.widget = {
      col:              '0',
      row:              '0',
      data:             [],
      id:               0,
      title:            'new widget',
      sizeX:            '8',
      sizeY:            '5',
      type:             null,
      widget_id:        0,
      widget_variables: null,
      js_code:          "// your custom code should return promise object,\n// e.g.:\nvar promise = $.get('http://');"
    };

    $scope.state = 'stats';
    $scope.searchText = '';
    $scope.reports = [];
    $scope.labels = [];
    $scope.selectedLabels = 0;
    $scope.vars = {};

    $scope.groupParams = DashboardWidgetService.groupParams;

    load_promises.push(DashboardsInfo.getReportDetail(report_id).then(loadedReport => $scope.report = loadedReport)
    );

    TemplateManager.queue('ReportsInterfaceBundle:Dashboard/Modal:add-widget-variables.html');

    load_promises.push(DashboardWidgetService.getReports().then((result) => {
      $scope.reports = result.reports;
      for (const label of Array.from(result.labels)) {
        $scope.labels.push({ title: label, active: false });
      }
      return $scope.filterByLabels();
    })
    );

    $q.all(load_promises).then(() => $scope.loaded = true);

    // ###################################################################################################################
    // UI handlers
    // ###################################################################################################################

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    $scope.isActiveLabel = function (label) {
      let index = -1;
      index = Arrays.findIndex($scope.labels,
        (v) => {
          if ((v.title === label) && (v.active === true)) { return true; }
        });
      if (index >= 0) {
        return true;
      }
      return false;
    };

    // ###################################################################################################################
    // Sort'n'filter
    // ###################################################################################################################

    // Mmmm... super script to toggle labels by it's title or label itself
    $scope.toggleLabel = function (label) {
      if (typeof label === 'string') {
        let index = -1;
        index = Arrays.findIndex($scope.labels,
          (v) => {
            if (v.title === label) { return true; }
          });
        if (index >= 0) {
          label = $scope.labels[index];
        }
      }
      if (label != null) {
        const currentLabel = $scope.labels[$scope.labels.indexOf(label)];
        if (currentLabel.active === true) {
          $scope.selectedLabels -= 1;
        } else {
          $scope.selectedLabels += 1;
        }
        currentLabel.active = !currentLabel.active;
        return $scope.filterByLabels();
      }
    };

    /*
     * Filter prefilteredReports by labels
     */
    $scope.filterByLabels = function () {
      if ($scope.selectedLabels !== 0) {
        return $scope.prefilteredReports = $scope.reports.filter((report) => {
          for (const label of Array.from(report.labels)) { if ($scope.isActiveLabel(label)) { return true; } }
        });
      }
      return $scope.prefilteredReports = $scope.reports;
    };

    /*
     * Filter prefilteredReports by labels
     * Note that this will filter by label.title too (@see OneNote->DR->ImplementingDesign->(4) Adding Widgets)
     */
    $scope.filterBySearchText = function (value) {
      if ($scope.searchText === '') {
        return true;
      }
      const search = $scope.searchText.toLocaleLowerCase();
      if (value.title.toLocaleLowerCase().indexOf(search) >= 0) {
        return true;
      }
      for (const label of Array.from(value.labels)) { if (label.toLocaleLowerCase().indexOf(search) >= 0) { return true; } }
      return false;
    };

    $scope.isAdvancedMatchSearchText = function () {
      if ($scope.searchText === '') {
        return true;
      }
      const search = $scope.searchText.toLocaleLowerCase();
      if ('Advanced: Widget from arbitrary Javascript code'.toLocaleLowerCase().indexOf(search) >= 0) {
        return true;
      }


      return false;
    };

    // ###################################################################################################################
    // SAVE
    // ###################################################################################################################

    $scope.makeChoice = function (report) {
      if (report === 'advanced') {
        return $scope.widget.widget_id = 'advanced';
      }
      $scope.reportWidget     = report;
      $scope.widget.widget_id = report.id;
      return $scope.widget.variables = $scope.vars[report.id];
    };

    $scope.chooseType = function () {
      if (__guard__($scope != null ? $scope.widget : undefined, x => x.widget_id)) {
        $scope.widget.variables = $scope.vars[$scope.widget.widget_id];
        return $modalInstance.close({ reportWidget: $scope.reportWidget, report: $scope.report, widget: $scope.widget });
      }
    };

    return $scope.changeWidgetParams = (params, report) => $scope.vars[report.id] = params;
  }
]);

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
