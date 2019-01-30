define ->
  DeskPRO_Directive_DpOpenPopover = [ ->
    return {
      restrict: 'A',
      link: (scope, element) ->
        element.on('click', (ev) ->
            ev.preventDefault()
            popover = window.parent.DeskPRO_Window._initInterfacePopover($(ev.currentTarget));
            popover.open()
        )
    }
  ]

  return DeskPRO_Directive_DpOpenPopover
