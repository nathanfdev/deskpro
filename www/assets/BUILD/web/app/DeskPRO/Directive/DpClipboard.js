define(['clipboard'], function(Clipboard) {
  const DeskPRO_Directive_DpClipboard = [() =>
    ({
      restrict: 'A',
      scope:    {
        ngclipboardSuccess: '&',
        ngclipboardError:   '&'
      },
      link($scope, $el) {
        const clipboard = new Clipboard($el.get(0));
        clipboard.on('success', e =>
          $scope.$apply(() => $scope.ngclipboardSuccess({ e }))
        );

        return clipboard.on('error', e =>
          $scope.$apply(() => scope.ngclipboardError({ e }))
        );
      }
    })

  ];

  return DeskPRO_Directive_DpClipboard;
});
