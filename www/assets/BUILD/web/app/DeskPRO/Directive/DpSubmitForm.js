define(() => {
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
  const DeskPRO_Directive_DpSubmitForm = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        return element.on('click', (ev) => {
          ev.preventDefault();
          ev.stopPropagation();

          const form = element.closest('form');
          form.on('submit', ev => ev.preventDefault());
          const formName = form.attr('name');
          form.submit();
          scope[formName].$attempted = true;

          for (const k of Object.keys(scope[formName] || {})) {
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
