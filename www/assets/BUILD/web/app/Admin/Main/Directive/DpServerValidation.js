/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
  const Admin_Main_Directive_DpServerValidation = [() =>
    ({
      require: 'ngModel',
      restrict: 'A',
      link(scope, elm, attrs, ngModel) {
        ngModel.dpServerValidationKeys = attrs.dpServerValidation.split(',');

        if (!ngModel.dpServerValidationKeys.length) {
          return;
        }

        // Server-side validation errors always reset
        // when we re-validate on the client (e.g., so they can re-submit)
        return ngModel.$parsers.unshift( function(viewValue) {
          for (let error_code of Object.keys(ngModel.$error || {})) {
            const is_error = ngModel.$error[error_code];
            if (!is_error) { continue; }

            for (let code of Array.from(ngModel.dpServerValidationKeys)) {
              var code_safe;
              if (code.indexOf('.') !== -1) {
                code_safe = code.replace(/^.*\.(.*)$/, '$1');
              } else {
                code_safe = code;
              }
              code_safe = code_safe.replace(/\./g, '_');

              if (error_code === code_safe) {
                const code_segs = code.split('.');
                const last_seg = code_segs.pop();

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
    })
  
  ];

  return Admin_Main_Directive_DpServerValidation;
});