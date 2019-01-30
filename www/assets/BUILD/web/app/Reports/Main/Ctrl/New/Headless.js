// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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