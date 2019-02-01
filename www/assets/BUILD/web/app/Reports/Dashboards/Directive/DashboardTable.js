define(['datatables', "datatables.pageResize", "datatables.rowsGroup"], function() {
  const Reports_Directive_DashboardTable = ['$sce', 'DashboardWidgetService', '$timeout', ($sce, DashboardWidgetService, $timeout) =>
    ({
      restrict: 'E',
      replace: true,
      scope: {
        tableData: '@',
        jsCode: '@',
        myIndex: '@',
        widgetId: '@',
        options: '@',
        row: '@',
        col: '@',
        loaded: '@'
      },

      templateUrl: $sce.trustAsResourceUrl("ReportsInterfaceBundle:Dashboard/Widget:table_dt.html"),

      link(scope, element) {
        scope.loaded = false;
        scope.noData = false;

        const el = $(element);
        let dt = null;
        const box = el.parent();
        const tableData = scope.tableData ? JSON.parse(scope.tableData) : {};

        const listItem = box.parent();
        const conf = scope.widgetId || 0;

        const initTable = function(widget) {
          let options;
          scope.loaded = true;

          scope.resetClick = () => dt.order([]).clear().rows.add(widget.data).draw();

          scope.columns = widget.columns;

          try {
            options = JSON.parse(scope.options);
          } catch (e) {
            options = {};
          }

          let drawn = false;

          const defaultOptions = {
            data:           widget.data,
            columns:        widget.columns,
            rowsGroup:      widget.rowsGroup || [],
            pagingType:     "first_last_numbers",
            pageResize:     true,
            searching:      false,
            bJQueryUI:      true,
            lengthChange:   true,
            sDom:           'T<"clear">lrtip',
            deferRender:    true,
            scrollCollapse: true,
            autoWidth:      true,
            ordering:       true,
            order:          [],
            fnDrawCallback(settings) {
              if ((settings._iDisplayLength === -1) || (settings._iDisplayLength >= settings.fnRecordsDisplay())) {
                $(settings.nTableWrapper).find('.dataTables_paginate').hide();
              } else {
                $(settings.nTableWrapper).find('.dataTables_paginate').show();
              }
              if (!drawn) {
                $timeout(
                  function() {
                    drawn = true;
                    scope.noData = false;
                    return box.height(100);
                  }
                , 100);
                return $timeout(
                  () => box.height('auto')
                , 105);
              }
            }
          };

          dt = el.find('table').DataTable(Object.assign(defaultOptions, options));

          return listItem
            .find('.handle-e')
            .remove;
        };


        if (tableData && (tableData.data != null)) {
          tableData.noRedraw = true;
          return $timeout(() => initTable(tableData)
          ,1);
        } else if (scope.jsCode) {
          try {
            eval(scope.jsCode);
          } catch (error) {
            const e = error;
            console.log(e);
          }

          if (promise && promise.then) {
            return promise.then(function(response) {
              scope.loaded = true;
              scope.noData = true;

              if (response && response.data) {
                return initTable(response);
              }
            });
          }
        } else if (DashboardWidgetService.widgetsResults && DashboardWidgetService.widgetsResults[scope.widgetId]) {
          return DashboardWidgetService.widgetsResults[scope.widgetId].promise.then(function(renderedResult) {
            scope.loaded = true;
            scope.noData = true;

            if (renderedResult && renderedResult.data) {
              return initTable(renderedResult);
            }
          });
        } else {
          return DashboardWidgetService
            .getWidget(conf).then(function(widget) {
              scope.loaded = true;
              scope.noData = true;

              if (widget.rendered_result && widget.rendered_result.data) {
                return initTable(widget.rendered_result);
              }
          });
        }
      }
    })
  
  ];

  return Reports_Directive_DashboardTable;
});