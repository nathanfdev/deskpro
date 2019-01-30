define ->
  ###
    # Description
    # -----------
    #
    # A simple way to pass a string from the template into the contorller. Typically this is used
    # to pass contents for things like Growl notifications.
    #
    # Example View
    # ------------
    # <span dp-register-message="department_saved">Saved {{dep.name}}</button>
    #
    # Example Controller
    # ------------------
    # someAction: ->
    #     @Growl.success(@getRegisteredMessage('department_saved'))
    ###
  Admin_Main_Directive_DpRegisterMessage = [ ->
    return {
      restrict: 'A',
      scope: false,
      link: (scope, element, attrs) ->
        element.hide()

        if not scope._element_messages
          scope._element_messages = {}

        scope._element_messages[attrs['dpRegisterMessage']] = ->
          return element.html()

        return
    }
  ]

  return Admin_Main_Directive_DpRegisterMessage