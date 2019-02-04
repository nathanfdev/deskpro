define(() => {
  /*
    * Description
    * -----------
    *
    * Just like ng-show but works specifically on spinner IDs.
    *
    * Example
    * -------
    * <span dp-show-spinning="saving_dep" class="spinner">Saving</span>
    * <span dp-hide-spinning="saving_dep"><button>Click here to save</button></span>
    *
    * Controller:
    * @startSpinner('saving_dep')
    * ...
    * @stopSpinner('enableSpinner')
  */
  const DeskPRO_Directive_DpShowSpinning = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const id = attrs.dpShowSpinning;
        const scopeName = `dp_spin_els.${id}`;

        const update = function () {
          if (!(scope.dp_spin_els != null ? scope.dp_spin_els[id] : undefined)) {
            return element.hide();
          } else if (scope.dp_spin_els[id].doneTime && scope.dp_spin_els[id].doneSpin) {
            return element.hide();
          }
          return element.show();
        };

        update();

        scope.$watch(`${scopeName}.doneSpin`, () => update());
        return scope.$watch(`${scopeName}.doneTime`, () => update());
      }
    })

  ];

  return DeskPRO_Directive_DpShowSpinning;
});
