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

    $scope.addReport = ->
      $modalInstance.close($scope.report)
]
