define ->
  Reports_Directive_Widget= ['$compile', '$state', 'DashboardWidgetService', ($compile, $state, DashboardWidgetService) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        widgetId: '@'
        myIndex: '@'

      link: (scope, element, attrs) ->
        i = attrs.widgetId
        template = "<div id=\"ch#{i}\"></div>"
        linkFn = $compile(template)
        content = linkFn(scope)
        element.replaceWith(content)
        conf = scope.widgetId || 0;
        chartDiv = angular.element(document.getElementById("ch" + i))

        initChart = () ->
          DashboardWidgetService
            .getWidget(conf)
            .then (widget) =>
              chartDiv.html(widget.rendered_result)

        initChart()
    }
  ]

  return Reports_Directive_Widget