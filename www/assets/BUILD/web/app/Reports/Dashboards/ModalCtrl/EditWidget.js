define(() => [
  '$scope',
  '$modalInstance',
  'widget',
  function(
    $scope,
    $modalInstance,
    widget
  ) {

    $scope.widget = angular.copy(widget);

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    return $scope.saveWidget = () => $modalInstance.close($scope.widget);
  }
] );
