define ->
  Reports_Directive_DashboardTable = ['$state', 'Restangular', ($state, Restangular) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        tableData: '@',
        myIndex: '@',
        row: '@',
        col: '@'

      templateUrl: (elem, attr) ->
        return '/web/app/build/table_dt.html';

      link: (scope, element, attrs) ->
        el = $(element)

        hostname = window.location.origin
        dt = null


        box = el.parent()
        listItem = box.parent()

        width = listItem.width()
        height = listItem.height()

        Restangular
          .oneUrl 'Data', hostname + scope.tableData
          .get()
          .then \
            (resp) ->
              scope.columns = resp.columns
              setTimeout \
                () ->
                  dt = el.DataTable {
                    data: resp.Data,
                    deferRender: true,
                    dom: "rtS",
                    scrollY: 300,
                    scrollCollapse: true,
                    autoWidth: true
                  }
                , 750
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