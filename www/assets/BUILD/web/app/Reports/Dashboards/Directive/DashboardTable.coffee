define ['datatables', "datatables.pageResize"], () ->
  Reports_Directive_DashboardTable = ['$sce', 'DashboardWidgetService', ($sce, DashboardWidgetService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        tableData: '@',
        myIndex: '@',
        widgetId: '@'
        options: '@'
        row: '@',
        col: '@'

      templateUrl: $sce.trustAsResourceUrl("ReportsInterfaceBundle:Dashboard/Widget:table_dt.html")

      link: (scope, element) ->
        el = $(element)
        dt = null
        box = el.parent()
        tableData = if scope.tableData then JSON.parse(scope.tableData) else {}

        listItem = box.parent()
        conf = scope.widgetId || 0;
        interval = 0;

        initTable = (widget) ->
          if interval?
            clearInterval(interval)
          scope.columns = widget.columns

          try
            options = JSON.parse(scope.options)
          catch e
            options = {}

          defaultOptions = {
            data:           widget.data,
            columns:        widget.columns,
            pagingType:     "first_last_numbers",
            pageResize:     true,
            searching:      false,
            bJQueryUI:      true,
            lengthChange:   true,
            sDom:           'T<"clear">lfrtip'
            deferRender:    true,
            dom:            "rtS",
            scrollCollapse: true,
            autoWidth:      true
            fnDrawCallback: (settings) ->
              if settings._iDisplayLength == -1 || settings._iDisplayLength >= settings.fnRecordsDisplay()
                $(settings.nTableWrapper).find('.dataTables_paginate').hide();
              else
                $(settings.nTableWrapper).find('.dataTables_paginate').show();
          }

          dt = el.find('table').DataTable Object.assign(defaultOptions, options)

          listItem
            .find '.handle-e'
            .remove


        if tableData and tableData.data?
          tableData.noRedraw = true
          initTable tableData
        else
          DashboardWidgetService
            .getWidget(conf).then (widget) =>
              if widget and widget.data
                initTable widget
    }
  ]

  return Reports_Directive_DashboardTable