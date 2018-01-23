define ['datatables'], () ->
  Reports_Directive_DashboardTable = ['$sce', 'DashboardWidgetService', ($sce, DashboardWidgetService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        tableData: '@',
        myIndex: '@',
        widgetId: '@'
        row: '@',
        col: '@'

      templateUrl: $sce.trustAsResourceUrl("ReportsInterfaceBundle:Dashboard/Widget:table_dt.html")

      link: (scope, element) ->
        el = $(element)
        dt = null
        box = el.parent()
        listItem = box.parent()

        height = listItem.height()
        conf = scope.widgetId || 0;
        tableData   = if scope.tableData then JSON.parse(scope.tableData) else []
        interval = 0;

        initTable = (widget) ->
          if interval?
            clearInterval(interval)
          scope.columns = widget.columns
          dt = el.DataTable {
            data:           widget.data,
            columns:        widget.columns,
            pagingType:     "full_numbers",
            pageLength:     10,
            bJQueryUI:      true,
            iDisplayLength: 5,
            sDom:           'T<"clear">lfrtip'
            lengthMenu:     [[10, 25, 50, -1], [10, 25, 50, "All"]]
            scrollY:        listItem.height() - 140,
            deferRender:    true,
            dom:            "rtS",
            scrollCollapse: true,
            autoWidth:      true
            fnDrawCallback: (settings) ->
                if settings._iDisplayLength == -1 || settings._iDisplayLength > settings.fnRecordsDisplay()
                  $(settings.nTableWrapper).find('.dataTables_paginate').hide();
                else
                  $(settings.nTableWrapper).find('.dataTables_paginate').show();
          }
          if !widget.noRedraw
            listItem
              .find '.handle-e'
              .remove
            listItem
              .css 'overflow-y', 'hidden'

          listItem.scroll () ->
            topOffset = box.offset().top - 34 - listItem.offset().top;
            resHandlers = listItem.find '.gridster-item-resizable-handler'
            resHandlers.each () ->
              handler = $(this)
              calculated = 1 + topOffset
              handler[0].style.bottom = "#{calculated}px"

          setTimeout \
            () ->
              tBody = listItem.find '.dataTables_scrollBody'
              h = listItem.height()
              calculated = h - 140
              settings = dt.settings()
              settings[0].oScroll.sY = calculated
              tBody.css 'height', "#{calculated}px"
              tBody.css 'max-height', "#{calculated}px"
              dt.draw()
          , 2000


          interval = setInterval \
            () ->
              return if widget.noRedraw
              h = listItem.height();
              tBody = listItem.find('.dataTables_scrollBody')
              if h != height
                if dt?
                  calculated = h - 140
                  settings = dt.settings();
                  settings[0].oScroll.sY = calculated
                  tBody.css 'height', "#{calculated}px"
                  tBody.css 'max-height', "#{calculated}px"
                  dt.draw()
                height = h;
          , 200

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