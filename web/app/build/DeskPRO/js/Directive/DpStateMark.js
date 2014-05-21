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
        * If the params ends with a '!', such as:
        *
        *     dp-state-mark="category_title!:my.example.type.123"
        *
        * ... then the value is hashed with Strings.murmurhash3.
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
            var currentStateVars, hashParams, m, myStateId, myStateIdRe, myStateIdRe1, myStateIdRe2, p, updateMarker, _i, _len, _ref;
            myStateId = attrs.dpStateMark;
            currentStateVars = null;
            hashParams = {};
            m = myStateId.match(/^(.*?):(.*?)$/);
            if (m) {
              myStateId = m[2];
              currentStateVars = [];
              _ref = m[1].split(',');
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                p = _ref[_i];
                if (p.substr(-1) === '!') {
                  p = p.substr(0, p.length - 1);
                  hashParams[p] = true;
                }
                currentStateVars.push(p);
              }
            }
            myStateIdRe = Strings.escapeRegex(myStateId);
            myStateIdRe1 = new RegExp('^' + myStateIdRe + '\\.');
            myStateIdRe2 = new RegExp('^' + myStateIdRe + '$');
            element.on('click', function() {
              element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active');
              element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active');
              return element.addClass('state-on active');
            });
            updateMarker = function() {
              var currentStateId, isOn, v, _j, _len1;
              currentStateId = $state.current.name;
              isOn = false;
              if (currentStateVars) {
                for (_j = 0, _len1 = currentStateVars.length; _j < _len1; _j++) {
                  v = currentStateVars[_j];
                  if ($state.params[v] != null) {
                    if (hashParams[v]) {
                      currentStateId += '.' + Strings.murmurhash3($state.params[v]);
                    } else {
                      currentStateId += '.' + $state.params[v];
                    }
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
              if (currentStateId.match(myStateIdRe1) || currentStateId.match(myStateIdRe2)) {
                isOn = true;
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
