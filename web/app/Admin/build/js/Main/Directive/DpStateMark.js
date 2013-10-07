(function() {
  define(function() {
    /*
       # Description
       # -----------
       #
       # This directive adds a "state-on" and "active" classname to the element when the specified
       # route section is enabled.
       #
       # Sections can be named specifically or generally:
       #
       # * tickets.ticket_deps.edit.18
       # * tickets.ticket_deps.edit
       # * tickets.ticket_deps
       # * tickets
       #
       # If you specifiy a generic state name, then all states "under" that state will cause the on-state.
       #
       # If a is three-levels deep (e.g., nav > list > edit) then the 'id' param is appended and used as the last segment.
       #
       # Example View
       # ------------
       # <li dp-state-mark="tickets.ticket_deps">Ticket Departments</li>
    */

    var Admin_Main_Directive_DpStateMark;
    Admin_Main_Directive_DpStateMark = [
      '$rootScope', '$state', function($rootScope, $state) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var checkState, current_state_id, _ref;
            element.on('click', function() {
              element.closest('#dp_section_nav').find('.state-on').removeClass('state-on active');
              element.closest('#dp_section_list').find('.state-on').removeClass('state-on active');
              return element.addClass('state-on active');
            });
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