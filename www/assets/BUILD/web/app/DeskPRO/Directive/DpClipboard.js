define ['clipboard'], (Clipboard) ->
  DeskPRO_Directive_DpClipboard = [ ->
    return {
      restrict: 'A',
      scope: {
        ngclipboardSuccess: '&',
        ngclipboardError: '&'
      },
      link: ($scope, $el) ->
        clipboard = new Clipboard($el.get(0))
        clipboard.on('success', (e) ->
          $scope.$apply () ->
            $scope.ngclipboardSuccess({e: e})
        )

        clipboard.on('error', (e) ->
          $scope.$apply () ->
            scope.ngclipboardError({e: e})
        )
    }
  ]

  return DeskPRO_Directive_DpClipboard
