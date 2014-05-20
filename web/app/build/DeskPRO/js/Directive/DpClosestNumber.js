(function() {
  define(['DeskPRO/Util/Numbers'], function(Numbers) {

    /*
        * Description
        * -----------
        *
        * This directive adds a new form element for a filesize described as a number and a unit. For example,
        * "2 mb" or "5 gb". In the model, the number is saved as the size in bytes.
        *
        * Add a "model-type" attribute to the element to change how the time is represented in the model:
        * - bytes (default): Convert size into bytes. E.g., 1 kb is saved as 1024
        * - array: Save as an array: [size, unit]. E.g., 1 kb is [1, 'kb']
        * - object: Save in an object: { size: size, unit: unit}. E.g., 1 kb is {size: 1, unit: 'kb'}
        * - "X:Y": Save in an object using X and Y as keys: {X: size, Y: unit}
        *
        * Example Controller
        * ------------------
        * $scope.my_model = 8
        *
        * Example View
        * ------------
        * <select dp-closest-number ng-model="my_model">
        *     <option value="0">0</option>
        *     <option value="0">5</option>
        *     <option value="0">10</option>
        *     <option value="0">15</option>
        * </select>
        * (Will render with option 10)
     */
    var DeskPRO_Directive_DpClosestNumber;
    DeskPRO_Directive_DpClosestNumber = [
      function() {
        return {
          restrict: 'A',
          require: 'ngModel',
          link: function(scope, iElement, iAttrs, ngModel) {
            var getValues, valuesExpr;
            valuesExpr = (iAttrs.numberValues != null) && iAttrs.numberValues ? iAttrs.numberValues : null;
            getValues = function() {
              var values;
              if (valuesExpr) {
                values = scope.$eval(valuesExpr).map(function(n) {
                  return parseInt(n);
                });
              } else {
                values = [];
                iElement.find('option').each(function() {
                  return values.push(parseInt(this.value));
                });
              }
              return values;
            };
            return ngModel.$formatters.push(function(modelValue) {
              return Numbers.closest(modelValue, getValues());
            });
          }
        };
      }
    ];
    return DeskPRO_Directive_DpClosestNumber;
  });

}).call(this);

//# sourceMappingURL=DpClosestNumber.js.map
