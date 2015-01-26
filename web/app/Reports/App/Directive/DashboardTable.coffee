define ['datatables'], () ->
  Reports_Directive_DashboardTable = ['$sce', 'DashboardWidgetService', 'DashboardService', ($sce, DashboardWidgetService, DashboardService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        tableData: '@',
        myIndex: '@',
        widgetId: '@'
        row: '@',
        col: '@'

      templateUrl: $sce.trustAsResourceUrl("ReportsInterfaceBundle:Dashboard:table_dt.html")

      link: (scope, element, attrs) ->
        el = $(element)
        dt = null
        DashboardService.setWidgetService(DashboardWidgetService)

        box = el.parent()
        listItem = box.parent()

        width = listItem.width()
        height = listItem.height()
        conf = scope.widgetId || 0;

        DashboardWidgetService
        .getWidget(conf).then (widget) =>
          scope.columns = widget.columns
          dt = el.DataTable {
            data: widget.data,
            aoColumns: widget.aoColumns,
            deferRender: true,
            dom: "rtS",
            scrollY: 300,
            scrollCollapse: true,
            autoWidth: true
          }
          listItem
            .find '.handle-e'
            .remove
          listItem
            .css "overflow-y", "hidden"

          listItem.scroll () ->
            t = box.offset().top - 47 - listItem.offset().top;

            resHandlers = listItem.find '.gridster-item-resizable-handler'

            resHandlers.each (index, element) ->
              h = $(this)

              c = 1 + t
              h[0].style.bottom = c + "px"

          setTimeout \
            () ->
              tBody = listItem.find '.dataTables_scrollBody'
              h = listItem.height()

              settings = dt.settings()
              s = settings[0].oScroll.sY
              settings[0].oScroll.sY = h - 87
              tBody.css 'height', h - 87 + 'px'
            , 2000


          setInterval \
            () ->
              w = listItem.width();
              h = listItem.height();
              tBody = listItem.find('.dataTables_scrollBody')
              if h != height
                if dt?
                  settings = dt.settings();
                  s = settings[0].oScroll.sY;
                  settings[0].oScroll.sY = h - 87
                  tBody.css 'height', h - 87 + 'px'

                width = w;
                height = h;
          , 200

    }
  ]

  return Reports_Directive_DashboardTable