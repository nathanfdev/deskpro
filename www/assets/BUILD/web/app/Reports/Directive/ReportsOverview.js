define(['DeskPRO/Util/Strings'], (Strings) => {
  const Reports_Directive_ReportsOverview = ['$compile', 'ReportsOverviewService', '$http', ($compile, ReportsOverviewService, $http) =>
    ({
      restrict: 'E',
      replace:  true,
      scope:    {
        innerType: '@',
        myIndex:   '@',
        outerType: '@'
      },

      link(scope, element) {
        scope.outerType = Strings.ucFirst(scope.outerType);
        const templateUrl = `ReportsInterfaceBundle:${scope.outerType}:${scope.innerType}.html`;
        const dataKey = scope.innerType.replace(/\-/g, '_');
        $http.get(templateUrl).then((response) => {
          const linkFn = $compile(response.data);
          const content = linkFn(scope);
          element.replaceWith(content);
          return ReportsOverviewService.getData(scope.innerType).then(data => scope[dataKey] = data);
        });

        return scope.getStats = function (dataKey) {
          dataKey = scope.innerType.replace(/\-/g, '_');
          return ReportsOverviewService.getStats(scope.innerType).then(data => scope[dataKey] = data.data);
        };
      }
    })

  ];

  return Reports_Directive_ReportsOverview;
});
