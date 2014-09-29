(function() {
  define(function() {

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
    var DeskPRO_Directive_DpShowSpinning;
    DeskPRO_Directive_DpShowSpinning = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var id, scopeName, update;
            id = attrs['dpShowSpinning'];
            scopeName = 'dp_spin_els.' + id;
            update = function() {
              var _ref;
              if (!((_ref = scope.dp_spin_els) != null ? _ref[id] : void 0)) {
                return element.hide();
              } else if (scope.dp_spin_els[id].doneTime && scope.dp_spin_els[id].doneSpin) {
                return element.hide();
              } else {
                return element.show();
              }
            };
            update();
            scope.$watch(scopeName + '.doneSpin', function() {
              return update();
            });
            return scope.$watch(scopeName + '.doneTime', function() {
              return update();
            });
          }
        };
      }
    ];
    return DeskPRO_Directive_DpShowSpinning;
  });

}).call(this);

//# sourceMappingURL=DpShowSpinning.js.map
