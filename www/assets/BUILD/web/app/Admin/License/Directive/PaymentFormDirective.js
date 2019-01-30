/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Util'], function(Util) {
  const Admin_License_Directive_PaymentFormDirective = [ () =>
    ({
      restrict: 'A',
      require: "ngModel",
      link(scope, element, attrs, model) {
        scope.form = {};
        scope.localForm = { set_mode: null };

        const getCcType = function(number) {
          if (!number) { return ""; }
          let re = new RegExp("^4");
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

        model.$formatters.push( function(modelValue) {
          if (!modelValue) { modelValue = {}; }

          const value = modelValue;
          if (!value.new_card) { value.new_card = {}; }
          if (!value.address) { value.address = {}; }

          if (!value.exist_card || Util.isBlankObject(value.exist_card)) {
            value.exist_card = null;
          }

          if (!value.new_card.expire_mm) { value.new_card.expire_mm = '0'; }
          if (!value.new_card.expire_yy) { value.new_card.expire_yy = '0'; }
          value.new_card.expire_mm = value.new_card.expire_mm + "";
          value.new_card.expire_yy = value.new_card.expire_yy + "";

          return value;
        });

        model.$parsers.push( function(viewValue) {
          const value = viewValue;
          value.type = getCcType(viewValue.new_card.number);
          return value;
        });

        scope.numberCharsFn = function() {
          scope.form.new_card.number = (scope.form.new_card.number||"").replace(/[^0-9]/g, '');
          return scope.form.new_card.type = getCcType(scope.form.new_card.number);
        };
        scope.cv2CharsFn    = () => scope.form.new_card.cv2 = (scope.form.new_card.cv2||"").replace(/[^0-9]/g, '').substr(0,4);

        scope.$watch('form', form => model.$setViewValue(form)
        , true);

        return model.$render = function() {
          const value = model.$viewValue || {};
          scope.form = value;
          return scope.invoice = value.invoice || null;
        };
      }
    })
  
  ];

  return Admin_License_Directive_PaymentFormDirective;
});