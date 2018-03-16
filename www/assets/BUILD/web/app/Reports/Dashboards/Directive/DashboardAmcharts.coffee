define ->
  Reports_Directive_Amcharts = ['$compile', '$state', 'DashboardWidgetService', ($compile, $state, DashboardWidgetService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        widgetId: '@'
        chartData: '@'
        jsCode: '@'
        reportLevelVars: '@'
        renderType: '@'
        options: '@'
        version: '@'
        widgetType: '@'
        chartType: '@'

      link: (scope, element) ->
        template = "<div id=\"ch#{scope.widgetId}\"></div>"
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)
        chart = false

        chartDiv    = angular.element(document.getElementById("ch#{scope.widgetId}"))
        chartParent = chartDiv.parent().parent()
        chartHeader = chartDiv.parent().siblings('.box-header')
        chartData   = if scope.chartData then JSON.parse(scope.chartData) else []
        options     = scope.options
        drawn       = false
        interval    = false

        scope.$watch 'chartData', (n) ->
          return if !drawn
          chartData = if n then JSON.parse(n) else []
          initChart()

        scope.$watch 'options', (n) ->
          return if !drawn
          try
            newOptions = JSON.parse(n)
          catch e
            newOptions = {}
          if !angular.equals(newOptions, options)
            options = angular.copy(newOptions)
            drawWidget chartData

        initChart = () ->
          if scope.widgetType != 'graph'
            return

          # this is valid for serial and pie charts, gauge has not dataProvider
          if (chartData and chartData.dataProvider?) || (scope.chartType == 'gauge' && chartData.axes?[0]?.bands?)
            drawWidget chartData
          else if scope.jsCode
            try
              eval(scope.jsCode)
            catch e
              console.log(e)

            if promise and promise.then
              promise.then (response) ->
                drawWidget response
          else
            DashboardWidgetService
              .getWidget(scope.widgetId || 0)
              .then (widget) =>
                if widget? && widget && (widget.dataProvider || widget.axes?[0]?.bands?)
                  drawWidget(widget)

        drawWidget = (widget) ->
          if interval
            clearInterval(interval)
          drawn = true
          try
            options = if scope.options then JSON.parse(scope.options) else {}
          catch e
            options = {}
            console.warn("invalid options")
            console.log(e)

          options.theme = 'light'

          if widget.dataProvider? && widget.dataProvider[0]? && (Object.keys(widget.dataProvider[0]).length > 6 || (widget.type == 'pie' && widget.dataProvider.length > 6))
            widget.legend = false

          if chart and widget.dataProvider
            chart.dataProvider = widget.dataProvider
          else
            chart = new AmCharts.makeChart("ch#{scope.widgetId}", Object.assign(widget, options));

          chartDiv.height(chartParent.height() - chartHeader.outerHeight())
          chart.validateData()
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
                      pie.color = '#'+Math.floor(Math.random()*16777215).toString(16)
                      data.push pie
                  else
                    data.push element
                chart.dataProvider = data
              else
                chart.dataProvider = defaultDataProvider
              chart.validateData()

          width = chartParent.height()
          height = chartParent.width()

          interval = setInterval \
            () ->
              w = chartParent.width()
              h = chartParent.height()

              if h != height or width != w
                chartDiv.height(chartParent.height() - chartHeader.outerHeight())
                chart.invalidateSize()

                width = w
                height = h
          , 1000

        initChart()
    }
  ]

  return Reports_Directive_Amcharts