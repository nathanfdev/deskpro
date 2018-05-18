define ['datatables', "datatables.pageResize"], () ->
  Reports_Directive_DashboardTable = ['$sce', 'DashboardWidgetService', '$timeout', ($sce, DashboardWidgetService, $timeout) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        tableData: '@'
        jsCode: '@'
        myIndex: '@'
        widgetId: '@'
        options: '@'
        row: '@'
        col: '@'
        loaded: '@'

      templateUrl: $sce.trustAsResourceUrl("ReportsInterfaceBundle:Dashboard/Widget:table_dt.html")

      link: (scope, element) ->
        scope.loaded = false
        scope.noData = false

        el = $(element)
        dt = null
        box = el.parent()
        tableData = if scope.tableData then JSON.parse(scope.tableData) else {}

        listItem = box.parent()
        conf = scope.widgetId || 0

        initTable = (widget) ->
          scope.loaded = true

          scope.resetClick = () ->
            dt.order([]).clear().rows.add(widget.data).draw()

          scope.columns = widget.columns

          try
            options = JSON.parse(scope.options)
          catch e
            options = {}

          drawn = false

          defaultOptions = {
            data:           widget.data,
            columns:        widget.columns,
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
            order:          []
            fnDrawCallback: (settings) ->
              if settings._iDisplayLength == -1 || settings._iDisplayLength >= settings.fnRecordsDisplay()
                $(settings.nTableWrapper).find('.dataTables_paginate').hide();
              else
                $(settings.nTableWrapper).find('.dataTables_paginate').show();
              if !drawn
                $timeout(
                  ->
                    drawn = true
                    box.height(100)
                , 100)
                $timeout(
                  ->
                    box.height('auto')
                , 105)
          }

          dt = el.find('table').DataTable Object.assign(defaultOptions, options)

          listItem
            .find '.handle-e'
            .remove


        if tableData and tableData.data?
          tableData.noRedraw = true
          $timeout(->
            initTable tableData
          ,1)
        else if scope.jsCode
          try
            eval(scope.jsCode)
          catch e
            console.log(e)

          if promise and promise.then
            promise.then (response) ->
              scope.loaded = true
              scope.noData = true

              if response and response.data
                initTable response
        else if DashboardWidgetService.widgetsResults and DashboardWidgetService.widgetsResults[scope.widgetId]
          DashboardWidgetService.widgetsResults[scope.widgetId].promise.then (renderedResult) =>
            scope.loaded = true
            scope.noData = true

            if renderedResult and renderedResult.data
              initTable renderedResult
        else
          DashboardWidgetService
            .getWidget(conf).then (widget) =>
              scope.loaded = true
              scope.noData = true

              if widget.rendered_result and widget.rendered_result.data
                initTable widget.rendered_result
    }
  ]

  return Reports_Directive_DashboardTable