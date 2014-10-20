(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_License_Directive_PaymentFormDirective;
    Admin_License_Directive_PaymentFormDirective = [
      function() {
        return {
          restrict: 'A',
          require: "ngModel",
          link: function(scope, element, attrs, model) {
            var getCcType;
            scope.form = {};
            scope.localForm = {
              set_mode: null
            };
            getCcType = function(number) {
              var re;
              if (!number) {
                return "";
              }
              re = new RegExp("^4");
              if (number.match(re) !== null) {
                return "visa";
              }
              re = new RegExp("^(34|37)");
              if (number.match(re) !== null) {
                return "amex";
              }
              re = new RegExp("^5[1-5]");
              if (number.match(re) !== null) {
                return "mastercard";
              }
              re = new RegExp("^6011");
              if (number.match(re) !== null) {
                return "discover";
              }
              return "unknown";
            };
            model.$formatters.push(function(modelValue) {
              var value;
              if (!modelValue) {
                modelValue = {};
              }
              value = modelValue;
              if (!value.new_card) {
                value.new_card = {};
              }
              if (!value.address) {
                value.address = {};
              }
              if (!value.exist_card || Util.isBlankObject(value.exist_card)) {
                value.exist_card = null;
              }
              if (!value.new_card.expire_mm) {
                value.new_card.expire_mm = '0';
              }
              if (!value.new_card.expire_yy) {
                value.new_card.expire_yy = '0';
              }
              value.new_card.expire_mm = value.new_card.expire_mm + "";
              value.new_card.expire_yy = value.new_card.expire_yy + "";
              return value;
            });
            model.$parsers.push(function(viewValue) {
              var value;
              value = viewValue;
              value.type = getCcType(viewValue.new_card.number);
              return value;
            });
            scope.numberCharsFn = function() {
              scope.form.new_card.number = (scope.form.new_card.number || "").replace(/[^0-9]/g, '');
              return scope.form.new_card.type = getCcType(scope.form.new_card.number);
            };
            scope.cv2CharsFn = function() {
              return scope.form.new_card.cv2 = (scope.form.new_card.cv2 || "").replace(/[^0-9]/g, '').substr(0, 4);
            };
            scope.$watch('form', function(form) {
              return model.$setViewValue(form);
            }, true);
            return model.$render = function() {
              var value;
              value = model.$viewValue || {};
              scope.form = value;
              return scope.invoice = value.invoice || null;
            };
          }
        };
      }
    ];
    return Admin_License_Directive_PaymentFormDirective;
  });

}).call(this);

//# sourceMappingURL=PaymentFormDirective.js.map
