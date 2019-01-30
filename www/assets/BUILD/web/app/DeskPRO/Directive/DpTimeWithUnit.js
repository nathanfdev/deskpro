// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
  const DeskPRO_Directive_DpTimeWithUnit = [ () =>
    ({
      restrict: 'E',
      template: `\
<div class="dp-time-unit">
  <input type="text" ng-model="time_num" class="form-control time_num" style="vertical-align: middle;" />
  <select
    ng-model="time_unit"
    ui-select2
    style="min-width: 100px;"
  >
    <option ng-if="has_minutes" value="minutes">{{phrases.minutes}}</option>
    <option ng-if="has_hours" value="hours">{{phrases.hours}}</option>
    <option ng-if="has_days" value="days">{{phrases.days}}</option>
    <option ng-if="has_weeks" value="weeks">{{phrases.weeks}}</option>
    <option ng-if="has_months" value="months">{{phrases.months}}</option>
    <option ng-if="has_years" value="years">{{phrases.years}}</option>
  </select>
</div>\
`,
      scope: {},
      require: 'ngModel',
      replace: true,
      link(scope, iElement, iAttrs, ngModel) {

        scope.time_num = '';
        scope.time_unit = 'minutes';

        let availableUnits = null;
        if (iAttrs.availableUnits) {
          availableUnits = scope.$eval(iAttrs.availableUnits);
        }
        if (!availableUnits) {
          availableUnits = ['minutes', 'hours', 'days', 'weeks', 'months', 'years'];
        }

        let unitPhrases = null;
        if (iAttrs.unitPhrases) {
          unitPhrases = scope.$eval(iAttrs.unitPhrases);
        }
        if (!unitPhrases) {
          unitPhrases = {
            minutes: 'minutes',
            hours: 'hours',
            days: 'days',
            weeks: 'weeks',
            months: 'months',
            years: 'years'
          };
        }

        scope.phrases = unitPhrases;

        for (let v of Array.from(availableUnits)) {
          scope[`has_${v}`] = true;
        }

        let modelType = 'seconds';
        let objModelKeys = null;

        if (iAttrs.modelType) {
          if ((iAttrs.modelType === 'object') || (iAttrs.modelType.indexOf(':') !== -1)) {
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

        const multiplierMap = {
          seconds:   1,
          minutes:   60,
          hours:  3600,
          days:   86400,
          weeks:  604800,
          months: 2419200,
          years:  31536000
        };

        const multiplierTypes = [
          'seconds',
          'minutes',
          'hours',
          'days',
          'weeks',
          'months',
          'years'
        ];

        multiplierTypes.reverse();

        ngModel.$parsers.push( function(viewValue) {
          const unit = viewValue.unit || 'minutes';
          const num  = viewValue.num || 1;

          switch (modelType) {
            case "object":
              var obj = {};
              obj[objModelKeys[0]] = num;
              obj[objModelKeys[1]] = unit;
              return obj;
            case "array":
              var arr = [num, unit];
              return arr;
            default:
              var secs = multiplierMap[unit] * num;
              return secs;
          }
        });

        ngModel.$formatters.push( function(modelValue) {
          let unit = 'minutes';
          let num  = '';

          switch (modelType) {
            case "object":
              if ((modelValue != null ? modelValue[objModelKeys[0]] : undefined) != null) {
                unit = modelValue[objModelKeys[0]];
              }
              if ((modelValue != null ? modelValue[objModelKeys[1]] : undefined) != null) {
                num = modelValue[objModelKeys[1]];
              }
              break;
            case "array":
              if ((modelValue != null ? modelValue[1] : undefined) != null) {
                unit = modelValue[1];
              }
              if ((modelValue != null ? modelValue[0] : undefined) != null) {
                num = modelValue[0];
              }
              break;
            default:
              modelValue = parseInt(modelValue || 0);

              for (let unitName of Array.from(multiplierTypes)) {
                if ((modelValue % multiplierMap[unitName]) === 0) {
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

          // not an allowed unit, keep going 'down' until we get one
          if (!scope[`has_${unit}`]) {
            let newUnit;
            const secs = multiplierMap[unit] * num;
            let unitIdx = multiplierTypes.indexOf(unit);
            while (true) {
              unitIdx += 1;
              newUnit = (multiplierTypes[unitIdx] != null) ? multiplierTypes[unitIdx] : null;
              if (!newUnit || scope[`has_${newUnit}`]) { break; }
            }

            if (newUnit) {
              unit = newUnit;
              num = secs / multiplierMap[unit];
            }
          }

          return {
            unit,
            num
          };
        });

        scope.$watch('time_unit + time_num', function() {
          if (scope.time_unit && scope.time_num) {
            return ngModel.$setViewValue({
              unit: scope.time_unit,
              num:  parseInt(scope.time_num)
            });
          }
        });

        ngModel.$render = function() {
          const viewValue = ngModel.$viewValue;
          if (viewValue) {
            scope.time_num  = viewValue.num;
            scope.time_unit = viewValue.unit;
            return iElement.find('select').first().select2('val', viewValue.unit);
          }
        };

        return ngModel.$render();
      }
    })
  
  ];

  return DeskPRO_Directive_DpTimeWithUnit;
});