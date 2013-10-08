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
      '$timeout', function($timeout) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            $timeout(function() {
              return element.focus();
            }, 50);
            $timeout(function() {
              return element.focus();
            }, 150);
            return $timeout(function() {
              return element.focus();
            }, 200);
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