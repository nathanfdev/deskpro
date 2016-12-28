define ['DeskPRO/Util/Strings'], (Strings) ->
  Reports_Directive_ReportsOverview = ['$compile', 'ReportsOverviewService', '$http', ($compile, ReportsOverviewService, $http) ->
    return {
      restrict: 'E'
      replace: true
      scope:
        innerType: '@'
        myIndex: '@'
        outerType: '@'

      link: (scope, element) ->

        scope.outerType = Strings.ucFirst scope.outerType
        templateUrl = "ReportsInterfaceBundle:#{scope.outerType}:#{scope.innerType}.html"
        dataKey = scope.innerType.replace(/\-/g, '_')
        $http.get(templateUrl).then (response) ->
          linkFn = $compile(response.data)
          content = linkFn(scope)
          element.replaceWith(content)
          ReportsOverviewService.getData(scope.innerType).then (data) ->
            scope[dataKey] = data

        scope.getStats = (dataKey) ->
          dataKey = scope.innerType.replace(/\-/g, '_')
          ReportsOverviewService.getStats(scope.innerType).then (data) ->
            scope[dataKey] = data.data
    }
  ]

  return Reports_Directive_ReportsOverview