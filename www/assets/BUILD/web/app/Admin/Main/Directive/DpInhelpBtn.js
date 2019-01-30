// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['jquery'], function($) {
  /*
   * Description
   * -----------
   *
   * This is the trigger for an inline help content body to display. When it is clicked,
   * this element will fade away and the body will slide in. When the body is closed again,
   * the body will "minimise into" this switch and the switch will become visible again.
   *
   * The ID of these inline helps should be globally unique because their state is saved.
   *
   * Help State
   * ----------
   *
   * A help state can either be open, closed, or undefined. Undefined states default to being
   * open unless the default-state attribute is used to set it open.
   *
   * Example View
   * ------------
   * <div class="panel-heading">
   *     <h3>
   *         <span>Permissions</span>
   *         <button class="btn inhelp-trigger" dp-inhelp-btn="admin.ticket_deps.edit.usergroup_perms"><i></i></button>
   *     </h3>
   * </div>
   * <div class="panel-help" dp-inhelp-body="admin.ticket_deps.edit.usergroup_perms">
   *     ...
   * </div>
   */
  const Admin_Main_Directive_DpInhelpBtn = ['InhelpState', '$rootScope', (InhelpState, $rootScope) =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {

        const id = attrs['dpInhelpBtn'].replace(/\./g, '_');
        const scopedId = `dp_ctrl_inhelp_state.${id}`;

        if (!$rootScope.dp_ctrl_inhelp_state) {
          $rootScope.dp_ctrl_inhelp_state = {};
        }

        let defaultState = true;
        if ((attrs['defaultState'] != null) === 'closed') {
          defaultState = false;
        }

        $rootScope.dp_ctrl_inhelp_state[id] = InhelpState.getState(id);
        if ($rootScope.dp_ctrl_inhelp_state[id] === null) {
          $rootScope.dp_ctrl_inhelp_state[id] = defaultState;
        }

        const bodyId = `dp_inhelp_${id}`;
        const btnId  = bodyId + '_btn';

        const icon = angular.element('<i></i>');
        element.prepend(icon);

        element.attr('id', btnId).addClass('inhelp-trigger');

        //---
        // Change the elements current display state
        //---

        const updateState = function() {
          if ($rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id] : undefined) {
            element.fadeOut(200);
            $(`#${bodyId}`).slideDown(200);
          } else {
            element.fadeIn(200);
            $(`#${bodyId}`).slideUp(200);
          }

          return InhelpState.setState(id, $rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id] : undefined);
        };


        //---
        // Respond to changes
        //---

        $rootScope.$watch(scopedId, newVal => updateState(newVal));

        element.on('click', function(ev) {
          ev.preventDefault();
          return scope.$apply(function() {
            if (($rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id] : undefined)) {
              return $rootScope.dp_ctrl_inhelp_state[id] = false;
            } else {
              return ($rootScope.dp_ctrl_inhelp_state != null ? $rootScope.dp_ctrl_inhelp_state[id]  = true : undefined);
            }
          });
        });


        //---
        // Set the initial view state
        //---

        if ($rootScope.dp_ctrl_inhelp_state[id]) {
          element.hide();
          return $(`#${bodyId}`).show();
        } else {
          element.show();
          return $(`#${bodyId}`).hide();
        }
      }
    })
  
  ];

  return Admin_Main_Directive_DpInhelpBtn;
});