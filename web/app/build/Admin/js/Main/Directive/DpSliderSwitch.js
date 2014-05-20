(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This turns an element into an iOS7-style toggle on/off switch. It toggles the connected
        * model to true or false.
        *
        * (This is similar to DpToggleSwitch, this is just cleaner; aka 'version 2' of that compontent)
        *
        * Additional Attributes
        * ---------------------
        *
        * * is-locked:    Expression to evaluate when checking if the locked symbol is on
        * * is-on:        Expression to evaluate when showing this as 'on'. When ng-model is true or when this is true, then it shows on
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
    var Admin_Main_Directive_DpToggleSwitch;
    Admin_Main_Directive_DpToggleSwitch = [
      function() {
        return {
          restrict: 'A',
          require: 'ngModel',
          template: "<div class=\"dp-switch\">\n	<label><span></span></label>\n</div>",
          replace: true,
          link: function(scope, element, attrs, ngModel) {
            var tipTarget;
            ngModel.$render = function() {
              var val;
              val = ngModel.$viewValue || {
                checked: false,
                on: false,
                locked: false
              };
              if (val.on || val.checked) {
                element.addClass('switch-on');
                element.removeClass('switch-off');
              } else {
                element.removeClass('switch-on');
                element.addClass('switch-off');
              }
              if (val.locked) {
                return element.addClass('locked');
              } else {
                return element.removeClass('locked');
              }
            };
            ngModel.$formatters.push(function(modelValue) {
              var val;
              val = ngModel.$viewValue || {
                checked: false,
                on: false,
                locked: false
              };
              if (modelValue) {
                val.checked = true;
              } else {
                val.checked = false;
              }
              return val;
            });
            ngModel.$parsers.push(function(viewValue) {
              if (viewValue && viewValue.checked) {
                return true;
              } else {
                return false;
              }
            });
            element.on('click', function(ev) {
              var val;
              ev.preventDefault();
              ev.stopPropagation();
              if (element.hasClass('locked')) {
                return;
              }
              val = ngModel.$viewValue || {
                checked: false,
                on: false,
                locked: false
              };
              val.checked = !val.checked;
              scope.$apply(function() {
                return ngModel.$setViewValue(val);
              });
              return ngModel.$render();
            });
            if (attrs.isLocked) {
              scope.$watch(attrs.isLocked, function(newVal) {
                var val;
                val = ngModel.$viewValue || {
                  checked: false,
                  on: false,
                  locked: false
                };
                val.locked = newVal;
                ngModel.$setViewValue(val);
                return ngModel.$render();
              });
            }
            if (attrs.isOn) {
              scope.$watch(attrs.isOn, function(newVal) {
                var val;
                val = ngModel.$viewValue || {
                  checked: false,
                  on: false,
                  locked: false
                };
                val.on = newVal;
                ngModel.$setViewValue(val);
                return ngModel.$render();
              });
            }
            scope.$watch(attrs.ngModel, function(newVal) {
              var val;
              val = ngModel.$viewValue || {
                checked: false,
                on: false,
                locked: false
              };
              val.checked = newVal;
              ngModel.$setViewValue(val);
              return ngModel.$render();
            });
            if (attrs.lockedTip || attrs.lockedTipE) {
              tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>');
              tipTarget.appendTo(element);
              return tipTarget.tooltip({
                placement: 'auto top',
                trigger: 'hover',
                container: 'body',
                title: function() {
                  if (attrs.lockedTipE) {
                    return scope.$eval(attrs.lockedTipE);
                  } else {
                    return attrs.lockedTip;
                  }
                }
              });
            }
          }
        };
      }
    ];
    return Admin_Main_Directive_DpToggleSwitch;
  });

}).call(this);

//# sourceMappingURL=DpSliderSwitch.js.map
