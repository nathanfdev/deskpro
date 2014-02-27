(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This sets the initial focus once a form is loaded.
        *
        * Example
        * -------
        * <input autofocus>
     */
    var Admin_Main_Directive_Autofocus;
    Admin_Main_Directive_Autofocus = [
      '$rootScope', '$timeout', function($rootScope, $timeout) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var done;
            element.focus();
            done = false;
            $timeout(function() {
              return element.focus();
            }, 150);
            return $timeout(function() {
              return element.focus();
            }, 250);
          }
        };
      }
    ];
    return Admin_Main_Directive_Autofocus;
  });

}).call(this);

//# sourceMappingURL=Autofocus.js.map
