(function() {
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
    var Admin_Main_Directive_DpRegisterMessage;
    Admin_Main_Directive_DpRegisterMessage = [
      function() {
        return {
          restrict: 'A',
          scope: false,
          link: function(scope, element, attrs) {
            element.hide();
            if (!scope._element_messages) {
              scope._element_messages = {};
            }
            scope._element_messages[attrs['dpRegisterMessage']] = function() {
              return element.html();
            };
          }
        };
      }
    ];
    return Admin_Main_Directive_DpRegisterMessage;
  });

}).call(this);

//# sourceMappingURL=DpRegisterMessage.js.map
