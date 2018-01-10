define -> [
  '$scope',
  '$modalInstance',
  'widget',
  (
    $scope,
    $modalInstance,
    widget
  ) ->

    $scope.widget = angular.copy(widget)

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveWidget = ->
      $modalInstance.close($scope.widget)
]
