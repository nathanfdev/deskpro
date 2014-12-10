define ->
  Reports_Directive_Amchart = ['$compile', '$state', 'DataService', ($compile, $state, DataService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        chartId: '@'
        myIndex: '@'

      link: (scope, element, attrs) ->
        i = attrs.myIndex
        template = "<div style='height: 100%' id='ch#{i}'></div>"
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)


        chart = false
        conf = scope.chartId || 0;

        initChart = () ->
          if chart
            chart.destroy()
          widgetService = DataService.get('DashboardWidgetService')
          service = DataService.get('DashboardService');
          service.setWidgetService(widgetService)
          widgetService
            .getWidget(conf)
            .then (widget) =>
              chart = new AmCharts.makeChart('ch' + i, widget);
              chart.handleResize()
              chart.invalidateSize()

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

  return Reports_Directive_Amchart