(function() {
  define(['DeskPRO/Util/Util', 'DeskPRO/Util/Strings'], function(Util, Strings) {
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
            var myStateData, myStateId, myStateIdRe, updateMarker;
            myStateId = attrs.dpStateMark;
            myStateIdRe = new RegExp(Strings.escapeRegex(myStateId));
            myStateData = attrs.dpStateMark ? scope.$eval(attrs.dpStateMark) : null;
            element.on('click', function() {
              element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active');
              element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active');
              return element.addClass('state-on active');
            });
            updateMarker = function() {
              var currentStateId, isOn;
              currentStateId = $state.current.name;
              isOn = false;
              if (myStateData) {
                if (currentStateId.match(myStateIdRe) && Util.equals(myStateData, $state.params)) {
                  isOn = true;
                }
              } else {
                if ($state.params.id) {
                  currentStateId += '.' + $state.params.id;
                }
                if ($state.params.type) {
                  currentStateId += '.' + $state.params.type;
                }
                if (currentStateId.match(myStateIdRe)) {
                  isOn = true;
                }
              }
              if (isOn) {
                element.addClass('state-on active');
                return element.closest('[dp-nav-subnav]').show().closest('li').addClass('sublist-open');
              } else {
                return element.removeClass('state-on active');
              }
            };
            $rootScope.$on('$stateChangeSuccess', function() {
              return updateMarker();
            });
            return updateMarker();
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