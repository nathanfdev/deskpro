(function() {
  define(['redactor', 'jquery'], function(redactor, $) {

    /*
    	 * Description
    	 * -----------
    	 * textarea editor, moved from agent iface
    	 *
     */
    var Admin_Main_Directive_DpRedactor;
    Admin_Main_Directive_DpRedactor = [
      '$timeout', function($timeout) {
        return {
          restrict: 'A',
          require: 'ngModel',
          link: function(scope, element, attrs, ngModel) {
            var defaults;
            defaults = {
              minHeight: 100,
              keyupCallback: function(html) {
                ngModel.$setViewValue(html.$el.val());
                return ngModel.$render();
              }
            };
            return $timeout(function() {
              return element.redactor(defaults);
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpRedactor;
  });

}).call(this);

//# sourceMappingURL=DpRedactor.js.map
