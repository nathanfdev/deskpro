(function() {
  define(["jquery", "intl-tel-input"], function($, intlTelInput) {

    /*
        * Description
        * -----------
        *
        * This turns an input into an intl-tel-input:
        * https://github.com/Bluefieldscom/intl-tel-input
        *
        * Example
        * -------
        * <input dp-phone-number>
     */
    var Admin_Main_Directive_DpPhoneNumber;
    Admin_Main_Directive_DpPhoneNumber = [
      '$rootScope', '$timeout', function($rootScope, $timeout) {
        return {
          require: 'ngModel',
          restrict: 'A',
          link: function(scope, element, attrs, ngModel) {
            return scope.$watch('loaded', function() {
              return setTimeout(function() {
                var read;
                read = function() {
                  return ngModel.$setViewValue(element.val());
                };
                element.on('focus blur keyup change', function() {
                  return scope.$apply(read);
                });
                element.intlTelInput();
                return read();
              }, 0);
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpPhoneNumber;
  });

}).call(this);

//# sourceMappingURL=DpPhoneNumber.js.map
