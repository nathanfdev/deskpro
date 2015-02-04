define -> [
  '$scope',
  '$modalInstance',
  'DashboardService',
  'DashboardWidgetService',
  'widget',
  ($scope,
   $modalInstance,
   DashboardService,
   DashboardWidgetService,
   widget) ->

    $scope.widget = widget

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveWidget = ->
      $modalInstance.close($scope.widget)
]