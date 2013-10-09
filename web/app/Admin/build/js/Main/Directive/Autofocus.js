(function() {
  define(function() {
    /*
       # Description
       # -----------
       #
       # This sets the initial focus once a form is loaded.
       #
       # Example
       # -------
       # <input autofocus>
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
            return scope.$on('dp_loadingstate_change', function(evt, id, is_loading) {
              if (!done && id === 'dp_section_page' && !is_loading) {
                return $timeout(function() {
                  return element.focus();
                }, 150);
              }
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_Autofocus;
  });

}).call(this);

/*
//@ sourceMappingURL=Autofocus.js.map
*/