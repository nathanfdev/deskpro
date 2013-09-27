(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var Admin_Main_Directive_DpSubmitForm;
    Admin_Main_Directive_DpSubmitForm = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            return element.on('click', function(ev) {
              var form, formName, k, v, _ref;
              ev.preventDefault();
              ev.stopPropagation();
              form = element.closest('form');
              form.on('submit', function(ev) {
                return ev.preventDefault();
              });
              formName = form.attr('name');
              form.submit();
              scope[formName].$attempted = true;
              _ref = scope[formName];
              for (k in _ref) {
                if (!__hasProp.call(_ref, k)) continue;
                v = _ref[k];
                if (k.substring(0, 1) === '$') {
                  continue;
                }
                if (!v.$name || !v.$viewChangeListeners) {
                  continue;
                }
                v.$attempted = true;
              }
              return scope.$apply();
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpSubmitForm;
  });

}).call(this);

/*
//@ sourceMappingURL=DpSubmitForm.js.map
*/