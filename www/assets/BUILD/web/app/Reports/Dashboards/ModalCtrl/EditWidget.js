/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
