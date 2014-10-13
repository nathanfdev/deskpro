(function() {
  define(function() {
    var Admin_License_Directive_PaymentFormDirective;
    Admin_License_Directive_PaymentFormDirective = [
      function() {
        return {
          restrict: 'A',
          require: "ngModel",
          scope: {},
          link: function(scope, element, attrs, model) {
            model.$formatters.push(function(m) {
              return m;
            });
          }
        };
      }
    ];
    return Admin_License_Directive_PaymentFormDirective;
  });

}).call(this);

//# sourceMappingURL=PaymentFormDirective.js.map
