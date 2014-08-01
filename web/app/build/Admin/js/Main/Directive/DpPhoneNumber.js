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
            return setTimeout(function() {
              var read;
              element.intlTelInput();
              read = function() {
                return ngModel.$setViewValue(element.val());
              };
              element.on('focus blur keyup change', function() {
                return scope.$apply(read);
              });
              return read();
            }, 1250);
          }
        };
      }
    ];
    return Admin_Main_Directive_DpPhoneNumber;
  });

}).call(this);

//# sourceMappingURL=DpPhoneNumber.js.map
