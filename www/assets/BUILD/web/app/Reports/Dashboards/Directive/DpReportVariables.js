define ->
  Reports_Directive_DpReportVariables = ['$state', '$compile', '$sce', '$http', 'TemplateManager', ($state, $compile, $sce, $http, TemplateManager) ->
    return {
      restrict: 'AE',
      replace: true,
      scope:
        widget: "=widget"
        changeParams: "&changeWidgetParams"
        report: "=report"
        possibleValues: "=possibleValues"

      link: (scope, element, attrs) ->
        templateUrl = "ReportsInterfaceBundle:Dashboard/Modal:add-widget-variables.html"
        scope.type = attrs.type || 'builtIn'
        scope.vars = {}

        for value in scope.report.variables
          if value.default then scope.vars[value.name] = value.default

        TemplateManager.get(templateUrl).then (response) ->
          tpl = $sce.trustAsHtml response
          template = $sce.getTrustedHtml tpl
          linkFn = $compile(template)
          content = linkFn(scope)
          element.replaceWith(content)

        scope.changeValue = () ->
            vars = {}
            for key, value of scope.vars
              vars[key] = {'value': value}
            scope.changeParams {params: vars, reportWidget: scope.report}

        scope.changeValue()
    }
  ]

  return Reports_Directive_DpReportVariables