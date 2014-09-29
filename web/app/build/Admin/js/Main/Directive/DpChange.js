(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * Custom on-change directive
        *
        * Example
        * -------
        * <input dp-change="submit" />
     */
    var Admin_Main_Directive_DpChange;
    Admin_Main_Directive_DpChange = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            return element.bind('change', function() {
              return scope.$eval(attrs.dpChange);
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpChange;
  });

}).call(this);

//# sourceMappingURL=DpChange.js.map
