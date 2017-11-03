define -> [
  '$scope',
  '$modalInstance',
  'report',
  ($scope,
   $modalInstance
   report
  ) ->

    $scope.report = report;
    $scope.title = if report.id then 'Edit report' else 'Add report'

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveReport = ->
      $modalInstance.close($scope.report)
]
