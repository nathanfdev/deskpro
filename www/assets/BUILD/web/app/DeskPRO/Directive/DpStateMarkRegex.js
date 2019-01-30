/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Strings'
], function(Util, Strings) {
  /*
    * Description
    * -----------
    *
    * Like DpStateMark except the attr is expected to be a regex pattern.
    */
  const DeskPRO_Directive_DpStateMarkRegex = ['$state', $state =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        const myStateId   = attrs.dpStateMarkRegex;
        const currentStateVars = null;

        const myStateIdRe1 = new RegExp(myStateId);

        // This sets the active state immediately on click
        // which makes the UI feel faster
        element.on('click', function() {
          element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active');
          element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active');
          return element.addClass('state-on active');
        });

        const updateMarker = function() {
          const checkStateId = $state.current.name;
          let checkStateId2 = null;

          if ($state.current.data != null ? $state.current.data.stateMarkId : undefined) {
            checkStateId2 = $state.current.data.stateMarkId;
          }

          let isOn = false;

          for (let currentStateId of [checkStateId, checkStateId2]) {
            if (isOn || !currentStateId) { continue; }

            if (currentStateVars) {
              for (let v of Array.from(currentStateVars)) {
                if ($state.params[v] != null) {
                    currentStateId += `.${$state.params[v]}`;
                } else {
                  currentStateId += '.0';
                }
              }
            } else {
              if ($state.params['id'] != null) {
                currentStateId += `.${$state.params['id']}`;
              }
            }

            if (currentStateId.match(myStateIdRe1)) {
              isOn = true;
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

        scope.$on('$stateChangeSuccess', () => updateMarker());

        return updateMarker();
      }
    })
  
  ];

  return DeskPRO_Directive_DpStateMarkRegex;
});
