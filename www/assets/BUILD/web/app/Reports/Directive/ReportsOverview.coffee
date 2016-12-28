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
        ReportsOverviewService.getData(scope.innerType).then (data) ->
          dataKey = scope.innerType.replace(/\-/g, '_')
          scope[dataKey] = data.data
          $http.get(templateUrl).then (response) ->
            linkFn = $compile(response.data)
            content = linkFn(scope)
            element.replaceWith(content)
            scope.getStats scope.innerType

        scope.getStats = (dataKey) ->
          dataKey = scope.innerType.replace(/\-/g, '_')
          ReportsOverviewService
            .getStats dataKey
            .then (response) ->
              scope[dataKey] = response.data
    }
  ]

  return Reports_Directive_ReportsOverview