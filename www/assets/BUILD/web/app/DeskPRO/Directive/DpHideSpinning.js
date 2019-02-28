define(function() {
  /*
    * Description
    * -----------
    *
    * Check out dp-show-spinning, this is the opposite.
  */
  const DeskPRO_Directive_DpHideSpinning = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const id = attrs.dpHideSpinning;
        const scopeName = `dp_spin_els.${id}`;

        const update = function () {
          if (!(scope.dp_spin_els != null ? scope.dp_spin_els[id] : undefined)) {
            return element.show();
          } else if (scope.dp_spin_els[id].doneTime && scope.dp_spin_els[id].doneSpin) {
            return element.show();
          }
          return element.hide();
        };

        update();

        scope.$watch(`${scopeName}.doneSpin`, () => update());
        return scope.$watch(`${scopeName}.doneTime`, () => update());
      }
    })

  ];

  return DeskPRO_Directive_DpHideSpinning;
});
