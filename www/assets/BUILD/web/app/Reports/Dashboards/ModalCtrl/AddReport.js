/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(() => [
  '$scope',
  '$modalInstance',
  'report',
  function($scope,
   $modalInstance,
   report
  ) {

    $scope.report = report;

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    return $scope.saveReport = () => $modalInstance.close($scope.report);
  }
] );
