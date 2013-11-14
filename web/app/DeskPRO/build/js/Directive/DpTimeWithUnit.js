(function() {
  define(function() {
    /*
       # Description
       # -----------
       #
       # This directive adds a new form element for a time period described as a number and a unit. For example,
       # "2 days" or "5 hours". In the model, the number is saved as the time in seconds.
       #
       # Example Controller
       # ------------------
       # $scope.my_model = 7200
       #
       # Example View
       # ------------
       # <dp-time-with-unit ng-model="my_model" />
       # (7200 will render as "2 hours")
    */

    var DeskPRO_Directive_DpTimeWithUnit;
    DeskPRO_Directive_DpTimeWithUnit = [
      function() {
        return {
          restrict: 'E',
          template: "<div class=\"dp-time-unit\">\n	<input type=\"text\" ng-model=\"time_num\" class=\"form-control time_num\" />\n	<select\n		ng-model=\"time_unit\"\n		ui-select2\n		style=\"min-width: 100px;\"\n	>\n		<option value=\"mins\">minutes</option>\n		<option value=\"hours\">hours</option>\n		<option value=\"days\">days</option>\n		<option value=\"weeks\">weeks</option>\n		<option value=\"months\">months</option>\n		<option value=\"years\">years</option>\n	</select>\n</div>",
          require: 'ngModel',
          replace: true,
          link: function(scope, iElement, iAttrs, ngModel) {
            var multiplierMap, multiplierTypes;
            multiplierMap = {
              secs: 1,
              mins: 60,
              hours: 3600,
              days: 86400,
              weeks: 604800,
              months: 2419200,
              years: 31536000
            };
            multiplierTypes = ['secs', 'mins', 'hours', 'days', 'weeks', 'months', 'years'];
            multiplierTypes.reverse();
            ngModel.$parsers.push(function(viewValue) {
              var num, unit;
              unit = viewValue.unit || 'mins';
              num = viewValue.num || 1;
              return multiplierMap[unit] * num;
            });
            ngModel.$formatters.push(function(modelValue) {
              var unit, unitName, _i, _len;
              unit = null;
              modelValue = parseInt(modelValue);
              for (_i = 0, _len = multiplierTypes.length; _i < _len; _i++) {
                unitName = multiplierTypes[_i];
                if (modelValue % multiplierMap[unitName] === 0) {
                  unit = unitName;
                  break;
                }
              }
              if (!unit) {
                unit = 'mins';
              }
              return {
                unit: unit,
                num: modelValue / multiplierMap[unit]
              };
            });
            scope.$watch('time_unit + time_num', function() {
              if (scope.time_unit && scope.time_num) {
                return ngModel.$setViewValue({
                  unit: scope.time_unit,
                  num: parseInt(scope.time_num)
                });
              }
            });
            ngModel.$render = function() {
              var viewValue;
              viewValue = ngModel.$viewValue;
              if (viewValue) {
                scope.time_num = viewValue.num;
                scope.time_unit = viewValue.unit;
                return iElement.find('select').first().select2('val', viewValue.unit);
              }
            };
            return ngModel.$render();
          }
        };
      }
    ];
    return DeskPRO_Directive_DpTimeWithUnit;
  });

}).call(this);

/*
//@ sourceMappingURL=DpTimeWithUnit.js.map
*/