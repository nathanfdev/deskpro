define(function() {
  /*
    * Description
    * -----------
    *
    * This turns an element into an iOS7-style toggle on/off switch. It toggles the connected
    * model to true or false.
    *
    * (This is similar to DpToggleSwitch, this is just cleaner; aka 'version 2' of that component)
    *
    * Additional Attributes
    * ---------------------
    *
    * * is-locked:    Expression to evaluate when checking if the locked symbol is on
    * * is-on:        Expression to evaluate when showing this as 'on'. When ng-model is true or when this is true, then it shows on
    * * is-some:      Expression to evaluate when showing if a sub-option is one. Use this to show 'half on' status.
    * * ng-model:     The on/off model
    * * locked-tip:   A string for the locked tooltop
    * * locked-top-e: An expression that returns a string
    *
    * Example View
    * ------------
    * <input
    *     dp-slider-switch
    *     ng-model="myModel"
    *     is-on="myOtherModel.showAsOn"
    *     is-locked="myOtherModel.isLocked"
    *     locked-tip="This is locked because the 'full' permission is enabled"
    * />
    */
  const Admin_Main_Directive_DpToggleSwitch = [ () =>
    ({
      restrict: 'A',
      require:  'ngModel',
      template: `\
<div class="dp-switch">
  <label><span></span></label>
</div>\
`,
      replace: true,
      link(scope, element, attrs, ngModel) {

        ngModel.$render = function() {
          const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
          if (val.on || val.checked) {
            element.addClass('switch-on');
            element.removeClass('switch-off switch-some');
          } else if (val.some) {
            element.removeClass('switch-on switch-off');
            element.addClass('switch-some');
          } else {
            element.removeClass('switch-on switch-some');
            element.addClass('switch-off');
          }

          if (val.locked) {
            return element.addClass('locked');
          } else {
            return element.removeClass('locked');
          }
        };

        ngModel.$formatters.push( function(modelValue) {
          const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
          if (modelValue) {
            val.checked = true;
          } else {
            val.checked = false;
          }

          return val;
        });

        ngModel.$parsers.push( function(viewValue) {
          if (viewValue && viewValue.checked) {
            return true;
          } else {
            return false;
          }
        });

        element.on('click', function(ev) {
          ev.preventDefault();
          ev.stopPropagation();

          if (element.hasClass('locked')) {
            return;
          }

          const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
          val.checked = !val.checked;
          scope.$apply(() => ngModel.$setViewValue(val));
          return ngModel.$render();
        });

        if (attrs.isLocked) {
          scope.$watch(attrs.isLocked, function(newVal) {
            const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
            val.locked = newVal;
            ngModel.$setViewValue(val);
            return ngModel.$render();
          });
        }

        if (attrs.isOn) {
          scope.$watch(attrs.isOn, function(newVal) {
            const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
            val.on = newVal;
            ngModel.$setViewValue(val);
            return ngModel.$render();
          });
        }

        if (attrs.isSome) {
          scope.$watch(attrs.isSome, function(newVal) {
            const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
            val.some = !!newVal;
            ngModel.$setViewValue(val);
            return ngModel.$render();
          });
        }

        scope.$watch(attrs.ngModel, function(newVal) {
          const val = ngModel.$viewValue || { checked: false, on: false, locked: false, some: false };
          val.checked = newVal;
          ngModel.$setViewValue(val);
          return ngModel.$render();
        });

        if (attrs.lockedTip || attrs.lockedTipE) {
          const tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>');
          tipTarget.appendTo(element);
          return tipTarget.tooltip({
            placement: 'auto top',
            trigger: 'hover',
            container: 'body',
            title() {
              if (attrs.lockedTipE) {
                return scope.$eval(attrs.lockedTipE);
              } else {
                return attrs.lockedTip;
              }
            }
          });
        }
      }
    })
  
  ];

  return Admin_Main_Directive_DpToggleSwitch;
});