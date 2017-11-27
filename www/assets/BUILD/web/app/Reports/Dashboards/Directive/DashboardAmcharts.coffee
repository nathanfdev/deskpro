define ->
  Reports_Directive_Amcharts = ['$compile', '$state', 'DashboardWidgetService', ($compile, $state, DashboardWidgetService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        widgetId: '@'
        chartData: '@'
        reportLevelVars: '@'
        renderType: '@'

      link: (scope, element, attrs) ->
        template = "<div id=\"ch#{scope.widgetId}\"></div>"
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)
        chart = false

        chartDiv    = angular.element(document.getElementById("ch#{scope.widgetId}"))
        chartParent = chartDiv.parent().parent()
        chartHeader = chartDiv.parent().siblings('.box-header')
        chartData   = if scope.chartData then JSON.parse(scope.chartData) else []

        scope.$watch 'chartData', (n) ->
          chartData = if n then JSON.parse(n) else []
          initChart()

        initChart = () ->
          if attrs.chtype != 'graph'
            return
          if chart
            chart.destroy()
          if chartData and chartData.dataProvider?
            chartData.noRedraw = true
            drawWidget chartData
          else if (scope.renderType != 'test')
            DashboardWidgetService
              .getWidget(scope.widgetId || 0)
              .then (widget) =>
                if widget? and widget and widget.dataProvider
                  widget.noRedraw = false
                  drawWidget(widget)

        drawWidget = (widget) ->
          # ugly, but works right now
          chartDiv.height(chartParent.height() - chartHeader.outerHeight())
          chart = new AmCharts.makeChart("ch#{scope.widgetId}", widget);
          chart.handleResize()
          chart.invalidateSize()
          if widget.multiplePies?
            defaultDataProvider = widget.dataProvider
            chart.addListener "clickSlice", (event) ->
              if (event.dataItem.dataContext.id != undefined)
                selected = event.dataItem.dataContext.id
              else
                selected = undefined
              if selected?
                data = []
                angular.forEach defaultDataProvider, (element, index) ->
                  if index == selected
                    angular.forEach widget.pies[selected].dataProvider, (pie) ->
                      pie.color = '#'+Math.floor(Math.random()*16777215).toString(16);
                      data.push pie
                  else
                    data.push element
                chart.dataProvider = data
              else
                chart.dataProvider = defaultDataProvider
              chart.validateData()

          if (!widget.noRedraw)
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
    }
  ]

  return Reports_Directive_Amcharts