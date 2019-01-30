/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
  const DeskPRO_Directive_DpSubmitForm = [ () =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        return element.on('click', function(ev) {
          ev.preventDefault();
          ev.stopPropagation();

          const form = element.closest('form');
          form.on('submit', ev => ev.preventDefault());
          const formName = form.attr('name');
          form.submit();
          scope[formName].$attempted = true;

          for (let k of Object.keys(scope[formName] || {})) {
            const v = scope[formName][k];
            if (k.substring(0, 1) === '$') { continue; }
            if (!v.$name || !v.$viewChangeListeners) { continue; }

            v.$attempted = true;
          }

          return scope.$apply();
        });
      }
    })
  
  ];

  return DeskPRO_Directive_DpSubmitForm;
});