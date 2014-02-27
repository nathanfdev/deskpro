(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * Check out dp-show-spinning, this is the opposite.
     */
    var DeskPRO_Directive_DpHideSpinning;
    DeskPRO_Directive_DpHideSpinning = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var id, scopeName, update;
            id = attrs['dpHideSpinning'];
            scopeName = 'dp_spin_els.' + id;
            update = function() {
              var _ref;
              if (!((_ref = scope.dp_spin_els) != null ? _ref[id] : void 0)) {
                return element.show();
              } else if (scope.dp_spin_els[id].doneTime && scope.dp_spin_els[id].doneSpin) {
                return element.show();
              } else {
                return element.hide();
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
    return DeskPRO_Directive_DpHideSpinning;
  });

}).call(this);

//# sourceMappingURL=DpHideSpinning.js.map
