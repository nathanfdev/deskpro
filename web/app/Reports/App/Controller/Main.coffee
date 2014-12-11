define -> ['$scope', '$state', ($scope, $state) ->
  $scope.hello = "World";
  $scope.goto = ->
    console.log('going to dashboards')
    $state.transitionTo('app.reports.dashboards')
]