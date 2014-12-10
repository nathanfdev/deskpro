define -> ['$scope', ($scope) ->
  $scope.hello = "World";
  $scope.ping = -> alert('pong')
  console.log($scope);
]