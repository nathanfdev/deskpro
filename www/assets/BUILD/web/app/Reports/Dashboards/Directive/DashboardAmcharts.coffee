define ->
  Reports_Directive_Amcharts = ['$compile', '$state', 'DashboardWidgetService', ($compile, $state, DashboardWidgetService) ->
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

        chartDiv    = angular.element(document.getElementById("ch" + i))
        chartParent = chartDiv.parent().parent()
        chartHeader = chartDiv.parent().siblings('.box-header')
        chartData   = JSON.parse(scope.chartData)

        scope.$watch 'chartData', (n) ->
          chartData = JSON.parse(n)
          initChart()

        initChart = () ->
          if attrs.chtype != 'graph'
            return
          if chart
            chart.destroy()
          if chartData and chartData.dataProvider?
            chartData.noRedraw = true
            drawWidget chartData
          else
            DashboardWidgetService
              .getWidget(conf)
              .then (widget) =>
                if widget? and widget and widget.dataProvider
                  widget.noRedraw = false
                  drawWidget(widget)

        drawWidget = (widget) ->
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