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
        template = "<div id=\"ch#{i}\"></div>"
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)
        chart = false
        conf = scope.widgetId || 0;

        chartDiv = angular.element(document.getElementById("ch" + i))
        chartParent = chartDiv.parent().parent()
        chartHeader = chartDiv.parent().siblings('.box-header')

        initChart = () ->
          if chart
            chart.destroy()
          DashboardWidgetService
            .getWidget(conf)
            .then (widget) =>
              if widget? and widget
                # ugly, but works right now
                chartDiv.height(chartParent.height() - chartHeader.outerHeight())
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


                width = chartParent.height();
                height = chartParent.width();

                setInterval \
                  () ->
                    w = chartParent.width()
                    h = chartParent.height()

                    if h != height or width != w
                      # ugly, but works right now
                      chartDiv.height(chartParent.height() - chartHeader.outerHeight())
                      chart.handleResize();

                      width = w
                      height = h
                  , 500
              else


        if attrs.chtype == 'graph'
          initChart()
    }
  ]

  return Reports_Directive_Amcharts