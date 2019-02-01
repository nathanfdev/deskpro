define(['DeskPRO/Util/Strings'], function(Strings) {
  const Reports_Directive_AgentPerformance = ['$compile', '$sce', 'AgentActivityService', 'AgentHoursService', '$http', ($compile, $sce, AgentActivityService, AgentHoursService, $http) =>
    ({
    restrict: 'E',
    replace: true,
    scope: {
      innerType: '@',
      myIndex: '@',
      outerType: '@'
    },
    // OMG this is REALLY TERRIBLE, but I have no idea for now how can I refactor this :(
    controller($scope, $element) {
      const templateUrl = `ReportsInterfaceBundle:AgentPerformance:${$scope.innerType}-wrapper.html`;
      const service = (() => { switch ($scope.innerType) {
        case 'agent_activity': return AgentActivityService;
        case 'agent_hours': return AgentHoursService;
        default: return null;
      } })();

      if (service) {
        $scope.service = service;
        service.loadResults();
        return $http.get(templateUrl).then(function(response) {
          const tpl = $sce.trustAsHtml(response.data);
          const template = $sce.getTrustedHtml(tpl);
          const linkFn = $compile(template);
          const content = linkFn($scope);
          return $element.replaceWith(content);
        });
      } else {
        return console.error(`No such service for ${$scope.innerType} type`);
      }

      /* $scope.updateFilter = () ->
        service.updateFilter().then (response) ->
          template = "#{$sce.getTrustedHtml(service.html)}"
          linkFn = $compile(template)
          content = linkFn($scope)
          $element.find('.append_here').html(content)
      */
    }

    })
  
  ];

  return Reports_Directive_AgentPerformance;
});