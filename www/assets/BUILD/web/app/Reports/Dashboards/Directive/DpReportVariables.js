define(function() {
  const Reports_Directive_DpReportVariables = ['$state', '$compile', '$sce', '$http', 'TemplateManager', ($state, $compile, $sce, $http, TemplateManager) =>
    ({
      restrict: 'AE',
      replace:  true,
      scope:    {
        widget:         '=widget',
        changeParams:   '&changeWidgetParams',
        report:         '=report',
        possibleValues: '=possibleValues'
      },

      link(scope, element, attrs) {
        const templateUrl = 'ReportsInterfaceBundle:Dashboard/Modal:add-widget-variables.html';
        scope.type = attrs.type || 'builtIn';
        scope.vars = {};

        for (var value of Array.from(scope.report.variables)) {
          if (value.default) { scope.vars[value.name] = value.default; }
        }

        TemplateManager.get(templateUrl).then((response) => {
          const tpl = $sce.trustAsHtml(response);
          const template = $sce.getTrustedHtml(tpl);
          const linkFn = $compile(template);
          const content = linkFn(scope);
          return element.replaceWith(content);
        });

        scope.changeValue = function () {
          const vars = {};
          for (const key in scope.vars) {
            value = scope.vars[key];
            vars[key] = { value };
          }
          return scope.changeParams({ params: vars, reportWidget: scope.report });
        };

        return scope.changeValue();
      }
    })

  ];

  return Reports_Directive_DpReportVariables;
});
