define([], () => [
  '$scope', $scope =>

    $scope.gridsterOptions = {
      margins: [10, 10],
      width: 10000,
      columns: 150,
      colWidth: 50,
      draggable: {
        enabled: false,
        handle: 'h3'
      },
      resizable: {
        enabled: false,
        handles: ['n', 'e', 's', 'w', 'se', 'sw']
      }
    }
  
] );