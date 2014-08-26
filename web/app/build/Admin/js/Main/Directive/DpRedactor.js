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
            var api, defaults, updateModel;
            api = null;
            defaults = {
              minHeight: 100
            };
            updateModel = function(val) {
              return $timeout(function() {
                return scope.$apply(function() {
                  return ngModel.$setViewValue(val);
                });
              });
            };
            ngModel.$render = function() {
              if (api) {
                return $timeout(function() {
                  return api.setCode(ngModel.$viewValue || '');
                });
              }
            };
            return $timeout(function() {
              var origSyncCode;
              element.redactor(defaults);
              api = element.data('redactor');
              origSyncCode = api.syncCode;
              api.syncCode = function() {
                origSyncCode.call(api);
                return updateModel(api.getCode());
              };
              return ngModel.$render();
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpRedactor;
  });

}).call(this);

//# sourceMappingURL=DpRedactor.js.map
