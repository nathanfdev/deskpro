(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This directive adds a new form element for a time period described as a number and a unit. For example,
        * "2 days" or "5 hours". In the model, the number is saved as the time in seconds.
        *
        * Add a "model-type" attribute to the element to change how the time is represented in the model:
        * - seconds (default): Convert time into seconds. E.g., 1 hour is saved as 3600
        * - array: Save as an array: [time, unit]. E.g., 1 hour is [1, 'hours']
        * - object: Save in an object: { time: time, unit: unit}. E.g., 1 hour is {time: 1, unit: 'hours'}
        * - "X:Y": Save in an object using X and Y as keys: {X: time, Y: unit}
        *
        * Example Controller
        * ------------------
        * $scope.my_model = 7200
        * $scope.my_model_alt = {num: 4, time_unit: "hours"}
        *
        * Example View
        * ------------
        * <dp-time-with-unit ng-model="my_model" />
        * (7200 will render as "2 hours")
        *
        * <dp-time-with-unit model-type="num:time_unit" ng-model="my_model" />
        * (Renders as "4 hours")
     */
    var DeskPRO_Directive_DpTimeWithUnit;
    DeskPRO_Directive_DpTimeWithUnit = [
      function() {
        return {
          restrict: 'E',
          template: "<div class=\"dp-time-unit\">\n	<input type=\"text\" ng-model=\"time_num\" class=\"form-control time_num\" />\n	<select\n		ng-model=\"time_unit\"\n		ui-select2\n		style=\"min-width: 100px;\"\n	>\n		<option value=\"minutes\">minutes</option>\n		<option value=\"hours\">hours</option>\n		<option value=\"days\">days</option>\n		<option value=\"weeks\">weeks</option>\n		<option value=\"months\">months</option>\n		<option value=\"years\">years</option>\n	</select>\n</div>",
          scope: {},
          require: 'ngModel',
          replace: true,
          link: function(scope, iElement, iAttrs, ngModel) {
            var modelType, multiplierMap, multiplierTypes, objModelKeys;
            scope.time_num = '';
            scope.time_unit = 'minutes';
            modelType = 'seconds';
            objModelKeys = null;
            if (iAttrs.modelType) {
              if (iAttrs.modelType === 'object' || iAttrs.modelType.indexOf(':') !== -1) {
                modelType = 'object';
                if (iAttrs.modelType.indexOf(':') !== -1) {
                  objModelKeys = iAttrs.modelType.split(':');
                } else {
                  objModelKeys = ['time', 'unit'];
                }
              } else if (iAttrs.modelType === 'array') {
                modelType = 'array';
              } else {
                modelType = 'seconds';
              }
            }
            multiplierMap = {
              seconds: 1,
              minutes: 60,
              hours: 3600,
              days: 86400,
              weeks: 604800,
              months: 2419200,
              years: 31536000
            };
            multiplierTypes = ['seconds', 'minutes', 'hours', 'days', 'weeks', 'months', 'years'];
            multiplierTypes.reverse();
            ngModel.$parsers.push(function(viewValue) {
              var arr, num, obj, secs, unit;
              unit = viewValue.unit || 'minutes';
              num = viewValue.num || 1;
              switch (modelType) {
                case "object":
                  obj = {};
                  obj[objModelKeys[0]] = num;
                  obj[objModelKeys[1]] = unit;
                  return obj;
                case "array":
                  arr = [num, unit];
                  return arr;
                default:
                  secs = multiplierMap[unit] * num;
                  return secs;
              }
            });
            ngModel.$formatters.push(function(modelValue) {
              var num, unit, unitName, _i, _len;
              unit = 'minutes';
              num = '';
              switch (modelType) {
                case "object":
                  if ((modelValue != null ? modelValue[objModelKeys[0]] : void 0) != null) {
                    unit = modelValue[objModelKeys[0]];
                  }
                  if ((modelValue != null ? modelValue[objModelKeys[1]] : void 0) != null) {
                    num = modelValue[objModelKeys[1]];
                  }
                  break;
                case "array":
                  if ((modelValue != null ? modelValue[1] : void 0) != null) {
                    unit = modelValue[1];
                  }
                  if ((modelValue != null ? modelValue[0] : void 0) != null) {
                    num = modelValue[0];
                  }
                  break;
                default:
                  modelValue = parseInt(modelValue || 0);
                  for (_i = 0, _len = multiplierTypes.length; _i < _len; _i++) {
                    unitName = multiplierTypes[_i];
                    if (modelValue % multiplierMap[unitName] === 0) {
                      unit = unitName;
                      break;
                    }
                  }
                  if (!unit) {
                    unit = 'minutes';
                  }
                  if (modelValue) {
                    num = modelValue / multiplierMap[unit];
                  }
              }
              return {
                unit: unit,
                num: num
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

//# sourceMappingURL=DpTimeWithUnit.js.map
