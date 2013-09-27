(function() {
  define(function() {
    var Admin_Main_Directive_DpStateMark;
    Admin_Main_Directive_DpStateMark = [
      '$rootScope', '$state', function($rootScope, $state) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var checkState, current_state_id, _ref;
            checkState = function(stateId, newStateId) {
              var stateIdRegex;
              if (!stateId || !newStateId) {
                return;
              }
              stateIdRegex = '^';
              stateIdRegex += stateId.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
              stateIdRegex += '\\b';
              if (newStateId.match(new RegExp(stateIdRegex))) {
                return true;
              } else {
                return false;
              }
            };
            if ((_ref = $state.current) != null ? _ref.name : void 0) {
              current_state_id = $state.current.name;
              if ($state.params.id) {
                current_state_id += '.' + $state.params.id;
              }
              if (checkState(attrs.dpStateMark, current_state_id)) {
                element.addClass('state-on active');
              }
            }
            return $rootScope.$on('dp_activeStateChange', function(ev, newStateId) {
              if (checkState(attrs.dpStateMark, newStateId)) {
                return element.addClass('state-on active');
              } else {
                return element.removeClass('state-on active');
              }
            }, true);
          }
        };
      }
    ];
    return Admin_Main_Directive_DpStateMark;
  });

}).call(this);

/*
//@ sourceMappingURL=DpStateMark.js.map
*/