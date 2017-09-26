define ['DeskPRO/Util/Arrays'], (Arrays) ->


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
        ###
        # Below variables will look like following
        #
        # scope.texts = ['Number of tickets created','grouped by',' & ']
        # scope.options = [[{value: 'yesterday', label: 'Yesterday'}, {value: 'today', label: 'Today'}, {value: '123', label: '123'}, {value: '456', label: '456'}]
        #                                   [{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
        #                                   [{value: 'department', label: 'Department'}, {value: 'agent', label: 'Agent'}]
        # ]
        # scope.selected = ['today', 'agent', 'department']
        ###
        scope.type = attrs.type || 'builtIn'
        scope.vars = {}

        for value in scope.report.variables
          if value.default then scope.vars[value.name] = value.default

        ###
        # This function builds directive by constructing it on 'the fly' using DOM operations
        # The reason for doing so - problems with inner directives that were compiled with $compile() functionality
        ###

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
            scope.changeParams {params: vars}
    }
  ]

  return Reports_Directive_DpReportVariables