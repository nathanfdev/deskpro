define -> [
  '$scope',
  '$modalInstance',
  'report',
  ($scope,
   $modalInstance
   report
  ) ->

    $scope.report = report;

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveReport = ->
      $modalInstance.close($scope.report)
]
