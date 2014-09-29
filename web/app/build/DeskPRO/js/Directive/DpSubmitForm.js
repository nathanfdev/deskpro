(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {

    /*
        * Description
        * -----------
        *
        * This should be used to submit any forms within the DeskPRO interface. It has special
        * logic to add the "attempted" state to models which is used to show correct error state
        * in the UI.
        *
        * Example View
        * ------------
        * <button dp-submit-form>Save</button>
     */
    var DeskPRO_Directive_DpSubmitForm;
    DeskPRO_Directive_DpSubmitForm = [
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
    return DeskPRO_Directive_DpSubmitForm;
  });

}).call(this);

//# sourceMappingURL=DpSubmitForm.js.map
