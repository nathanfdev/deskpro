define [], () -> [
  '$scope', ($scope) ->

    console.log($scope.report)

    $scope.gridsterOptions =
      margins: [10, 10],
      width: 10000,
      columns: 150,
      colWidth: 50,
      draggable:
        enabled: true
        handle: 'h3'
      resizable:
        enabled: true
        handles: ['n', 'e', 's', 'w', 'se', 'sw']
]