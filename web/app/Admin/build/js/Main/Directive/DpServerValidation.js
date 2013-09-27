(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var Admin_Main_Directive_DpServerValidation;
    Admin_Main_Directive_DpServerValidation = [
      function() {
        return {
          require: 'ngModel',
          restrict: 'A',
          link: function(scope, elm, attrs, ngModel) {
            ngModel.dpServerValidationKeys = attrs.dpServerValidation.split(',');
            if (!ngModel.dpServerValidationKeys.length) {
              return;
            }
            return ngModel.$parsers.unshift(function(viewValue) {
              var code, code_safe, code_segs, error_code, is_error, last_seg, _i, _len, _ref, _ref1;
              _ref = ngModel.$error;
              for (error_code in _ref) {
                if (!__hasProp.call(_ref, error_code)) continue;
                is_error = _ref[error_code];
                if (!is_error) {
                  continue;
                }
                _ref1 = ngModel.dpServerValidationKeys;
                for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
                  code = _ref1[_i];
                  code_safe = code.replace(/\./g, '_');
                  if (error_code === code_safe) {
                    code_segs = code.split('.');
                    last_seg = code_segs.pop();
                    switch (last_seg) {
                      case 'required':
                        ngModel.$setValidity('required', true);
                        break;
                      default:
                        ngModel.$setValidity(code_safe, true);
                    }
                  }
                }
              }
              return viewValue;
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpServerValidation;
  });

}).call(this);

/*
//@ sourceMappingURL=DpServerValidation.js.map
*/