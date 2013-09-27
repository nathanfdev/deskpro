(function() {
  define(['jquery'], function($) {
    /*
    	# Description
    	# -----------
    	#
    	# This is the trigger for an inline help content body to display. When it is clicked,
    	# this element will fade away and the body will slide in. When the body is closed again,
    	# the body will "minimise into" this switch and the switch will become visible again.
    	#
    	# The ID of these inline helps should be globally unique because their state is saved.
    	#
    	# Help State
    	# ----------
    	#
    	# A help state can either be open, closed, or undefined. Undefined states default to being
    	# open unless the default-state attribute is used to set it open.
    	#
    	# Example View
    	# ------------
    	# <div class="panel-heading">
    	#     <h3>
    	#         <span>Permissions</span>
    	#         <button class="btn inhelp-trigger" dp-inhelp-btn="admin.ticket_deps.edit.usergroup_perms"><i></i></button>
    	#     </h3>
    	# </div>
    	# <div class="panel-help" dp-inhelp-body="admin.ticket_deps.edit.usergroup_perms">
    	#     ...
    	# </div>
    */

    var Admin_Main_Directive_DpInhelpBtn;
    Admin_Main_Directive_DpInhelpBtn = [
      'InhelpState', '$rootScope', function(InhelpState, $rootScope) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var bodyId, btnId, defaultState, icon, id, scopedId, updateState;
            id = attrs['dpInhelpBtn'].replace(/\./g, '_');
            scopedId = '_ctrl_inhelp_state.' + id;
            if (!$rootScope._ctrl_inhelp_state) {
              $rootScope._ctrl_inhelp_state = {};
            }
            defaultState = true;
            if ((attrs['defaultState'] != null) === 'closed') {
              defaultState = false;
            }
            $rootScope._ctrl_inhelp_state[id] = InhelpState.getState(id);
            if ($rootScope._ctrl_inhelp_state[id] === null) {
              $rootScope._ctrl_inhelp_state[id] = defaultState;
            }
            bodyId = 'dp_inhelp_' + id;
            btnId = bodyId + '_btn';
            icon = angular.element('<i></i>');
            element.prepend(icon);
            element.attr('id', btnId).addClass('inhelp-trigger');
            updateState = function() {
              var _ref, _ref1;
              if ((_ref = $rootScope._ctrl_inhelp_state) != null ? _ref[id] : void 0) {
                element.fadeOut(200);
                $('#' + bodyId).slideDown(200);
              } else {
                element.fadeIn(200);
                $('#' + bodyId).slideUp(200);
              }
              return InhelpState.setState(id, (_ref1 = $rootScope._ctrl_inhelp_state) != null ? _ref1[id] : void 0);
            };
            $rootScope.$watch(scopedId, function(newVal) {
              return updateState(newVal);
            });
            element.on('click', function(ev) {
              return scope.$apply(function() {
                var _ref, _ref1;
                if ((_ref = $rootScope._ctrl_inhelp_state) != null ? _ref[id] : void 0) {
                  return $rootScope._ctrl_inhelp_state[id] = false;
                } else {
                  return (_ref1 = $rootScope._ctrl_inhelp_state) != null ? _ref1[id] = true : void 0;
                }
              });
            });
            if ($rootScope._ctrl_inhelp_state[id]) {
              element.hide();
              return $('#' + bodyId).show();
            } else {
              element.show();
              return $('#' + bodyId).hide();
            }
          }
        };
      }
    ];
    return Admin_Main_Directive_DpInhelpBtn;
  });

}).call(this);

/*
//@ sourceMappingURL=DpInhelpBtn.js.map
*/