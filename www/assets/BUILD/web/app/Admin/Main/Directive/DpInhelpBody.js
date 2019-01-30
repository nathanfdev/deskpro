define ->
  ###
    # Description
    # -----------
    #
    # This is the body portion of dp-inhelp-switch. See that directive for more information.
    ###
  Admin_Main_Directive_DpInhelpBody = [ 'InhelpState', '$rootScope', (InhelpState, $rootScope) ->
    return {
      restrict: 'A',
      link: (scope, element, attrs) ->
        id = attrs['dpInhelpBody'].replace(/\./g, '_')

        bodyId = 'dp_inhelp_' + id
        element.attr('id', bodyId).addClass('inhelp-body')

        closeBtn = angular.element('<button class="inhelp-body-closebtn"><i></i></button>')
        element.prepend(closeBtn)

        closeBtn.on('click', (ev) ->
          ev.preventDefault()
          scope.$apply(->
            if $rootScope.dp_ctrl_inhelp_state?[id]
              $rootScope.dp_ctrl_inhelp_state[id] = false
            else
              $rootScope.dp_ctrl_inhelp_state?[id] = true
          )
        )

        if $rootScope.dp_ctrl_inhelp_state?[id]
          element.show()
        else
          element.hide()
    }
  ]

  return Admin_Main_Directive_DpInhelpBody