(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {

    /*
        * Description
        * -----------
        *
        * This attaches known error response codes from server-side validation on to a model.
        * This is needed so we can mark up the proper parts of the form and show the proper errors
        * messages for this particular model when there's an error.
        *
        * For example, say we have strict requirements for a title field. Angular gives us 'required'
        * support by default but we might want to defer our strict format checking to the server.
        *
        * The server can return an error code but there are no facilities in Angular to connect that
        * error code to the title model. We'd essentially have to do it "manually". Which is why we
        * need to use this directive. We can connect the server error codes to the model and then
        * the rest of error handling process is kept the same (e.g., enabling 'has-error' classes to
        * show error state etc).
        *
        * Example View
        * ------------
        * <input type="text" model="myfield" name="myfield" dp-server-validation="myfield.strict_requirements" />
     */
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
                  if (code.indexOf('.') !== -1) {
                    code_safe = code.replace(/^.*\.(.*)$/, '$1');
                  } else {
                    code_safe = code;
                  }
                  code_safe = code_safe.replace(/\./g, '_');
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

//# sourceMappingURL=DpServerValidation.js.map
