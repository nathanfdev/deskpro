define ->
  ###
    # Description
    # -----------
    #
    # Check out dp-show-spinning, this is the opposite.
  ###
  DeskPRO_Directive_DpHideSpinning = [ ->
    return {
      restrict: 'A',
      link: (scope, element, attrs) ->
        id = attrs['dpHideSpinning']
        scopeName = 'dp_spin_els.' + id

        update = ->
          if not scope.dp_spin_els?[id]
            element.show()
          else if scope.dp_spin_els[id].doneTime and scope.dp_spin_els[id].doneSpin
            element.show()
          else
            element.hide()

        update()

        scope.$watch(scopeName+'.doneSpin', ->
          update()
        )
        scope.$watch(scopeName+'.doneTime', ->
          update()
        )
    }
  ]

  return DeskPRO_Directive_DpHideSpinning