define ['handlebars'], (Handlebars) ->
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
        loaded: '@'

      link: (scope, element) ->
        window.initHandlebars(Handlebars)

        template = """
          <div>
            <div ng-hide='loaded' class="box stat-box"><div class="stat-value no-data">loading...</div></div>
            <div ng-show='loaded && noData' class="box stat-box"><div class="stat-value no-data">no data</div></div>
            <div ng-show='loaded' id="ch#{scope.widgetId}"></div>
          </div>
        """
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)
        chart = false
        scope.loaded = false
        scope.noData = false

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
                scope.loaded = true
                scope.noData = true

                drawWidget response
          else
            DashboardWidgetService
              .getWidget(scope.widgetId || 0)
              .then (widget) =>
                scope.loaded = true
                scope.noData = true

                if widget? && widget.rendered_result && (widget.rendered_result.dataProvider || widget.rendered_result.axes?[0]?.bands?)
                  drawWidget(widget.rendered_result)

        drawWidget = (widget) ->
          scope.loaded = true
          scope.noData = false
          setTimeout(->
            doDrawWidget(widget)
          , 1)

        doDrawWidget = (widget) ->
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

          if widget.valueAxes && widget.valueAxes[0] && (widget.valueAxes[0].hash || widget.valueAxes[0].labelTemplate)
            widget.valueAxes[0].labelFunction = (value) ->
              hash = widget.valueAxes[0].hash
              finalValue = value;
              if hash && hash[value]
                finalValue = hash[value]
              if widget.valueAxes[0].labelTemplate
                template = Handlebars.compile(widget.valueAxes[0].labelTemplate)
                finalValue = template({ 'value': finalValue })

              return finalValue

          if widget.valueAxes && widget.valueAxes[1] && widget.valueAxes[1].hash
            widget.valueAxes[1].labelFunction = (value) ->
              hash = widget.valueAxes[1].hash
              return if hash[value] then hash[value] else ''

          if widget.categoryAxis && widget.categoryAxis.labelTemplate
            widget.categoryAxis.labelFunction = (value) ->
              template = Handlebars.compile(widget.categoryAxis.labelTemplate)
              return template({ category: value })

          if widget.graphs
            widget.graphs = widget.graphs.map((g) ->
              if g.balloonTextTemplate
                g.balloonFunction = (item, graph) ->
                  vars = { item: item, graph: graph }
                  Object.keys(item.dataContext).forEach((k) -> vars[k] = item.dataContext[k])
                  return Handlebars.compile(g.balloonTextTemplate)(vars)

              return g
            )

          console.log(widget)

          if chart and widget.dataProvider
            chart.dataProvider = widget.dataProvider
          else
            chart = new AmCharts.makeChart("ch#{scope.widgetId}", lodashMerge(widget, options));

          chartDiv.height(chartParent.height() - chartHeader.outerHeight())
          chart.validateData()

          if options.click_url?

            if widget.type == 'pie'
              eventType = 'clickSlice'
              dataItem = 'dataItem'
            else
              eventType = 'clickGraphItem'
              dataItem = 'item'

            vars = {};
            matches = options.click_url.match(/\$\{([a-zA-z0-9_]+)\}/)
            for match, index in matches
              if index % 2 == 1
                vars[match] = matches[index - 1]

            chart.addListener eventType, (event) ->
              url = options.click_url
              for key, variable of vars
                if event[dataItem].dataContext[key]
                  url = url.replace(variable, event[dataItem].dataContext[key])
              window.open url

          else if widget.multiplePies?
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