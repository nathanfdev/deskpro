define(function() {
  /*
    * Description
    * -----------
    *
    * This turns a checkbox into a tri-state checkbox that reflects the state of
    * multiple models, and toggling it will toggle the watched models.
    *
    * Example
    * -------
    * <input type="checkbox" dp-tristate-check="model1, model2, model3" /> Status
    * -- <input type="checkbox" ng-model="model1" /> Sub-Checkbox 1
    * -- <input type="checkbox" ng-model="model2" /> Sub-Checkbox 2
    * -- <input type="checkbox" ng-model="model3" /> Sub-Checkbox 3
  */
  const Admin_Main_Directive_TristateCheck = [ () =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        let updateState;
        const names = [];
        for (var n of Array.from(attrs['dpTristateCheck'].split(','))) {
          n = $.trim(n);
          names.push(n);

          scope.$watch(n, val => updateState());
        }

        const getValFromStr = function(obj, str) {
          const parts = str.split('.');
          return parts.reduce( ((o, x) => o[x]), obj);
        };

        const setValFromStr = function(obj, str, val) {
          const parts = str.split('.');
          const name = parts.pop();
          obj = parts.reduce( ((o, x) => o[x]), obj);
          return obj[name] = val;
        };

        element.on('click', function() {
          const val = this.checked;
          return scope.$apply(() =>
            (() => {
              const result = [];
              for (n of Array.from(names)) {
                result.push(setValFromStr(scope, n, val));
              }
              return result;
            })()
          );
        });

        return updateState = function() {
          let count = 0;
          for (n of Array.from(names)) {
            if (getValFromStr(scope, n)) {
              count++;
            }
          }

          if (count) {
            element.prop('checked', true);
            if (count < names.length) {
              return element.prop('indeterminate', true);
            } else {
              return element.prop('indeterminate', false);
            }
          } else {
            element.prop('checked', false);
            return element.prop('indeterminate', false);
          }
        };
      }
    })
  
  ];

  return Admin_Main_Directive_TristateCheck;
});