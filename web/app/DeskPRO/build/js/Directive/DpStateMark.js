(function() {
  define(['DeskPRO/Util/Util', 'DeskPRO/Util/Strings'], function(Util, Strings) {

    /*
        * Description
        * -----------
        *
        * This directive adds a "state-on" and "active" classname to the element when the specified
        * route section is enabled.
        *
        * Sections can be named specifically or generally:
        *
        * * tickets.ticket_deps.edit.18
        * * tickets.ticket_deps.edit
        * * tickets.ticket_deps
        * * tickets
        *
        * If you specifiy a generic state name, then all states "under" that state will cause the on-state.
        *
        * If a is three-levels deep (e.g., nav > list > edit) then the 'id' param is appended and used as the last segment.
        *
        * You can prefix the string with a comma-separated list of target route paramters. For example, if a route
        * takes 'id' and 'type', you can specify the match param like:
        *
        *     dp-state-mark="id,type:my.example.type.123"
        *
        * And the match will be done against <route_name>.<id>.<type>
        *
        * Example View
        * ------------
        * <li dp-state-mark="tickets.ticket_deps">Ticket Departments</li>
     */
    var DeskPRO_Directive_DpStateMark;
    DeskPRO_Directive_DpStateMark = [
      '$state', function($state) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var currentStateVars, m, myStateId, myStateIdReBase, updateMarker;
            myStateId = attrs.dpStateMark;
            currentStateVars = null;
            m = myStateId.match(/^(.*?):(.*?)$/);
            if (m) {
              myStateId = m[2];
              currentStateVars = m[1].split(',');
            }
            myStateIdReBase = Strings.escapeRegex(myStateId);
            element.on('click', function() {
              element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active');
              element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active');
              return element.addClass('state-on active');
            });
            updateMarker = function() {
              var currentStateId, firstRegExpOccurrence, firstStateOccurrence, isOn, myStateIdRe, occurrenceFound, v, _i, _len;
              myStateIdRe = myStateIdReBase;
              currentStateId = $state.current.name;
              isOn = false;
              if (true) {
                if (currentStateVars) {
                  for (_i = 0, _len = currentStateVars.length; _i < _len; _i++) {
                    v = currentStateVars[_i];
                    if ($state.params[v] != null) {
                      currentStateId += '.' + $state.params[v];
                    } else {
                      currentStateId += '.0';
                    }
                  }
                } else {
                  if ($state.params.type) {
                    currentStateId += '.' + $state.params.type;
                  }
                  if ($state.params.id) {
                    currentStateId += '.' + $state.params.id;
                  }
                }
                myStateIdRe += '(\\.|$)';
                if (currentStateId.match(myStateIdRe)) {

                  /*
                  							 * This is workaround for situations when we have both routes like 'chat.setup' and 'setup'
                  							 * In this case both the elements will be highlighted
                  							 *
                  							 * If you will need to understand what is done uncomment following lines of code:
                  							 *
                  							 * console.log currentStateId, myStateIdRe
                  							 * console.log currentStateId.split('.')[0], myStateIdRe.toString().split('.')[0]
                  							 * console.log myStateIdRe.toString().split('.')[0].indexOf(currentStateId.split('.')[0])
                   */
                  firstStateOccurrence = currentStateId.split('.')[0];
                  firstRegExpOccurrence = myStateIdRe.toString().split('.')[0];
                  occurrenceFound = firstRegExpOccurrence.indexOf(firstStateOccurrence);
                  if (occurrenceFound > -1) {
                    isOn = true;
                  }
                }
              }
              if (isOn) {
                element.addClass('state-on active');
                if (element.closest('[dp-nav-subnav]')) {
                  return element.closest('[dp-nav-subnav]').show().closest('li').addClass('sublist-open');
                }
              } else {
                return element.removeClass('state-on active');
              }
            };
            scope.$on('$stateChangeSuccess', function() {
              return updateMarker();
            });
            return updateMarker();
          }
        };
      }
    ];
    return DeskPRO_Directive_DpStateMark;
  });

}).call(this);

//# sourceMappingURL=DpStateMark.js.map
