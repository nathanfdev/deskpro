define(function() {
  /*
    * Description
    * -----------
    *
    * This turns an element into a simple on/off switch.
    *
    * Example View
    * ------------
    * <button
    *     dp-onoff-switch
    *     ng-model="my_state"
    * ></button>
    */
  const Admin_Main_Directive_DpOnOffSwitch = [ () =>
    ({
      restrict: 'A',
      require:  ['ngModel', '^?form'],
      template: `\
<span>
  <span class="on-off-inlet">
    <span class="on-off-status"><span class="on-text">on</span><span class="off-text">off</span></span>
    <span class="on-off-cover"><i class="fa fa-check on-icon"></i><i class="fa fa-times off-icon"></i></span>
  </span>
</span>\
`,
      replace: true,
      link(scope, element, attrs, ctrls) {

        const ngModel = ctrls[0];
        const formCtrl = ctrls[1] || null;

        if (attrs.onoffClass) {
          element.addClass(attrs.onoffClass);
        } else {
          element.addClass('on-off');
        }

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

        return element.on('click', function(ev) {
          ev.preventDefault();
          ev.stopPropagation();

          return scope.$apply(() => ngModel.$setViewValue(!ngModel.$viewValue));
        });
      }
    })
  
  ];

  return Admin_Main_Directive_DpOnOffSwitch;
});
