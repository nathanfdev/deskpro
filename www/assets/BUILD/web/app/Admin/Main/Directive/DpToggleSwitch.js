// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  /*
    * Description
    * -----------
    *
    * This turns an element into an iOS7-style toggle on/off switch. It toggles the connected
    * model to true or false.
    *
    * Additional Attributes
    * ---------------------
    *
    * * locked-model: The model which indicates if the toggle is current locked
    * * ng-change:    A callback to fire when the model changes
    * * locked-tip:   A tooltip to display when the toggle is currently locked (locked-model is true)
    *
    * Example View
    * ------------
    * <button
    *     dp-toggle-switch
    *     locked-model="groupObj.perms.assign.locked"
    *     ng-model="groupObj.perms.assign.state"
    *     ng-change="TicketDepsEdit.propogatePermission(groupObj, 'assign')"
    *     locked-tip="This is locked because the 'full' permission is enabled"
    * ></button>
    */
  const Admin_Main_Directive_DpToggleSwitch = [ () =>
    ({
      restrict: 'A',
      require:  ['ngModel', '^?form'],
      template: `\
<div class="dp-switch">
  <label><span></span></label>
</div>\
`,
      replace: true,
      link(scope, element, attrs, ctrls) {

        const ngModel = ctrls[0];
        const formCtrl = ctrls[1] || null;

        if (formCtrl) {
          formCtrl.$addControl(ngModel);

          element.on('$destroy', () => formCtrl.$removeControl(ngModel));
        }

        ngModel.$viewChangeListeners.push(() => ngModel.$render());

        ngModel.$render = function() {
          const val = ngModel.$viewValue;
          if (val) {
            element.addClass('switch-on');
            return element.removeClass('switch-off');
          } else {
            element.removeClass('switch-on');
            return element.addClass('switch-off');
          }
        };

        element.on('click', function(ev) {
          ev.preventDefault();
          ev.stopPropagation();

          if (element.hasClass('locked')) {
            return;
          }

          return scope.$apply(() => ngModel.$setViewValue(!ngModel.$viewValue));
        });

        if (attrs.lockedModel) {
          scope.$watch(attrs.lockedModel, function(newVal) {
            if (newVal) {
              return element.addClass('locked');
            } else {
              return element.removeClass('locked');
            }
          });
        }

        if (attrs.lockedTip) {
          const tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>');
          tipTarget.attr('title', attrs.lockedTip);
          tipTarget.appendTo(element);
          return tipTarget.tooltip({
            placement: 'auto top',
            trigger: 'hover',
            container: 'body'
          });
        }
      }
    })
  
  ];

  return Admin_Main_Directive_DpToggleSwitch;
});