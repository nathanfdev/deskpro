define(['DeskPRO/Util/Numbers'], function(Numbers) {
  /*
    * Description
    * -----------
    *
    * This directive adds a new form element for a file size described as a number and a unit. For example,
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
    * $scope.values = [
    *   {id: 0, label: "0"},
    *   {id: 5, label: "5"},
    *   {id: 10, label: "10"},
    *   {id: 15, label: "15"}
    * ]
    *
    * Example View
    * ------------
    * <select dp-closest-number ng-model="my_model" ng-options="e.id as a.label for e in values">
    * </select>
    * (Will render with option 10)
    */
  const DeskPRO_Directive_DpClosestNumber = [() =>
    ({
      restrict: 'A',
      require:  'ngModel',
      link(scope, iElement, iAttrs, ngModel) {
        const valuesExpr = (iAttrs.numberValues != null) && iAttrs.numberValues ? iAttrs.numberValues : null;

        const getValues = function () {
          let values;
          if (valuesExpr) {
            values = scope.$eval(valuesExpr).map(n => parseInt(n));
          } else {
            values = [];
            iElement.find('option').each(function () { return values.push(parseInt(this.value.replace(/^number:/, ''))); });
          }
          return values;
        };

        return ngModel.$formatters.push(modelValue => Numbers.closest(modelValue, getValues()));
      }
    })

  ];

  return DeskPRO_Directive_DpClosestNumber;
});
