define ['DeskPRO/Util/Strings'], (Strings) ->
  Reports_Directive_Hardcoded = ['$compile', 'HardcodedService', '$http', ($compile, HardcodedService, $http) ->
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
        HardcodedService.getData(scope.innerType).then (data) ->
          scope[scope.innerType] = data.data
          $http.get(templateUrl).then (response) ->
            linkFn = $compile(response.data)
            content = linkFn(scope)
            element.replaceWith(content)

        scope.getStats = (data_key) ->
          HardcodedService
            .getStats data_key
            .then (response) ->
              scope[scope.innerType] = response.data
    }
  ]

  return Reports_Directive_Hardcoded