define(() => [
  '$scope',
  '$modalInstance',
  'report',
  function ($scope,
   $modalInstance,
   report
  ) {
    $scope.report = report;

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    return $scope.saveReport = () => $modalInstance.close($scope.report);
  }
]);
