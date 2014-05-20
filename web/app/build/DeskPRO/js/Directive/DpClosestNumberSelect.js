(function() {
  define(function() {

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
        * $scope.my_model = 14
        *
        * Example View
        * ------------
        * <select
        * <dp-closest-number-select ng-model="my_model" />
        * (Will render with option 15)
     */
    var DeskPRO_Directive_DpClosestNumberSelect;
    DeskPRO_Directive_DpClosestNumberSelect = [
      function() {
        return {
          restrict: 'A'
        };
      }
    ];
    return DeskPRO_Directive_DpClosestNumberSelect;
  });

}).call(this);

//# sourceMappingURL=DpClosestNumberSelect.js.map
