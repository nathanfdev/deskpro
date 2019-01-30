define ['DeskPRO/Util/Strings'], (Strings) ->
  Reports_Directive_AgentPerformance = ['$compile', '$sce', 'AgentActivityService', 'AgentHoursService', '$http', ($compile, $sce, AgentActivityService, AgentHoursService, $http) ->
    return {
    restrict: 'E'
    replace: true
    scope:
      innerType: '@'
      myIndex: '@'
      outerType: '@'
    # OMG this is REALLY TERRIBLE, but I have no idea for now how can I refactor this :(
    controller: ($scope, $element) ->
      templateUrl = "ReportsInterfaceBundle:AgentPerformance:#{$scope.innerType}-wrapper.html"
      service = switch $scope.innerType
        when 'agent_activity' then AgentActivityService
        when 'agent_hours' then AgentHoursService
        else null

      if service
        $scope.service = service
        service.loadResults()
        $http.get(templateUrl).then (response) ->
          tpl = $sce.trustAsHtml response.data
          template = $sce.getTrustedHtml tpl
          linkFn = $compile(template)
          content = linkFn($scope)
          $element.replaceWith(content)
      else
        console.error "No such service for #{$scope.innerType} type"

      ### $scope.updateFilter = () ->
        service.updateFilter().then (response) ->
          template = "#{$sce.getTrustedHtml(service.html)}"
          linkFn = $compile(template)
          content = linkFn($scope)
          $element.find('.append_here').html(content)
      ###

    }
  ]

  return Reports_Directive_AgentPerformance