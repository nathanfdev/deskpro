define ->
  Reports_Directive_Amcharts = ['$compile', '$state', 'DashboardWidgetService', 'DashboardService', ($compile, $state, DashboardWidgetService, DashboardService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        widgetId: '@'
        myIndex: '@'
        chartData: '@'

      link: (scope, element, attrs) ->
        i = attrs.widgetId
        template = "<div style='height: 90%;' id=\"ch#{i}\"></div>"
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)
        chart = false
        conf = scope.widgetId || 0;

        initChart = () ->
          if chart
            chart.destroy()
          DashboardService.setWidgetService(DashboardWidgetService)
          DashboardWidgetService
            .getWidget(conf)
            .then (widget) =>
              if widget? and widget
                chart = new AmCharts.makeChart('ch' + i, widget);
                chart.handleResize()
                chart.invalidateSize()
                if widget.multiplePies?
                  defaultDataProvider = widget.dataProvider
                  chart.addListener "clickSlice", (event) ->
                    if (event.dataItem.dataContext.id != undefined)
                      selected = event.dataItem.dataContext.id
                      color = event.dataItem.color
                    else
                      selected = undefined
                    if selected? and selected
                      data = []
                      angular.forEach defaultDataProvider, (element, index) ->
                        if index == selected
                          angular.forEach widget.pies[selected].dataProvider, (pie) ->
                            pie.color = color
                            data.push pie
                        else
                          data.push element
                      chart.dataProvider = data
                    else
                      chart.dataProvider = defaultDataProvider
                    chart.validateData()

                c = document.getElementById("ch" + i).parentNode.parentNode
                width = c.style.width;
                height = c.style.height;

                setInterval \
                  () ->
                    w = c.style.width
                    h = c.style.height

                    if h != height or width != w
                      chart.handleResize();

                      width = w
                      height = h
                  , 200

        if attrs.chtype == 'graph'
          initChart()
          c = document.getElementById("ch" + i).parentNode.parentNode


          el = $(c)
          box = el.find('div:first-child')
          listItem = el
    }
  ]

  return Reports_Directive_Amcharts