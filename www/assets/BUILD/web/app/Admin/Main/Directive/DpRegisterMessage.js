define(function() {
  /*
    * Description
    * -----------
    *
    * A simple way to pass a string from the template into the contorller. Typically this is used
    * to pass contents for things like Growl notifications.
    *
    * Example View
    * ------------
    * <span dp-register-message="department_saved">Saved {{dep.name}}</button>
    *
    * Example Controller
    * ------------------
    * someAction: ->
    *     @Growl.success(@getRegisteredMessage('department_saved'))
    */
  const Admin_Main_Directive_DpRegisterMessage = [ () =>
    ({
      restrict: 'A',
      scope: false,
      link(scope, element, attrs) {
        element.hide();

        if (!scope._element_messages) {
          scope._element_messages = {};
        }

        scope._element_messages[attrs['dpRegisterMessage']] = () => element.html();

      }
    })
  
  ];

  return Admin_Main_Directive_DpRegisterMessage;
});