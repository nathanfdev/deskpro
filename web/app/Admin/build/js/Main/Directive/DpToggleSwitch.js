(function() {
  define(function() {
    var Admin_Main_Directive_DpToggleSwitch;
    Admin_Main_Directive_DpToggleSwitch = [
      function() {
        return {
          restrict: 'A',
          require: 'ngModel',
          template: "<div class=\"dp-switch\">\n	<label><span></span></label>\n</div>",
          replace: true,
          scope: {
            model: '=ngModel',
            lockedModel: '=lockedModel',
            change: '=ngChange',
            lockedTip: '@'
          },
          link: function(scope, element, attrs, ngModel) {
            var tipTarget, updateVal;
            updateVal = function() {
              var val;
              val = scope.model;
              ngModel.$setViewValue(val);
              scope.model = val;
              if (val) {
                element.addClass('switch-on');
                element.removeClass('switch-off');
              } else {
                element.removeClass('switch-on');
                element.addClass('switch-off');
              }
              if (scope.lockedModel) {
                element.addClass('locked');
              } else {
                element.removeClass('locked');
              }
              if (scope.change) {
                return scope.$eval(scope.change);
              }
            };
            element.on('click', function(ev) {
              ev.preventDefault();
              if (element.hasClass('locked')) {
                return;
              }
              scope.model = !scope.model;
              return scope.$apply(function() {
                return updateVal(updateVal);
              });
            });
            scope.$watch('model', function() {
              return updateVal();
            });
            scope.$watch('lockedModel', function(newVal) {
              if (newVal) {
                return element.addClass('locked');
              } else {
                return element.removeClass('locked');
              }
            });
            if (scope.lockedTip) {
              tipTarget = angular.element('<div class="mouse-target show-on-locked-on"></div>');
              tipTarget.attr('title', scope.lockedTip);
              tipTarget.appendTo(element);
              tipTarget.tooltip({
                placement: 'auto top',
                trigger: 'hover',
                container: 'body'
              });
            }
            if (scope.model) {
              ngModel.$setViewValue(true);
              element.addClass('switch-on');
              return element.removeClass('switch-off');
            }
          }
        };
      }
    ];
    return Admin_Main_Directive_DpToggleSwitch;
  });

}).call(this);

/*
//@ sourceMappingURL=DpToggleSwitch.js.map
*/