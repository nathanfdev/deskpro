(function() {
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
    var Admin_Main_Directive_TristateCheck;
    Admin_Main_Directive_TristateCheck = [
      function() {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            var getValFromStr, n, names, setValFromStr, updateState, _i, _len, _ref;
            names = [];
            _ref = attrs['dpTristateCheck'].split(',');
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              n = _ref[_i];
              n = $.trim(n);
              names.push(n);
              scope.$watch(n, function(val) {
                return updateState();
              });
            }
            getValFromStr = function(obj, str) {
              var parts;
              parts = str.split('.');
              return parts.reduce((function(o, x) {
                return o[x];
              }), obj);
            };
            setValFromStr = function(obj, str, val) {
              var name, parts;
              parts = str.split('.');
              name = parts.pop();
              obj = parts.reduce((function(o, x) {
                return o[x];
              }), obj);
              return obj[name] = val;
            };
            element.on('click', function() {
              var val;
              val = this.checked;
              return scope.$apply(function() {
                var _j, _len1, _results;
                _results = [];
                for (_j = 0, _len1 = names.length; _j < _len1; _j++) {
                  n = names[_j];
                  _results.push(setValFromStr(scope, n, val));
                }
                return _results;
              });
            });
            return updateState = function() {
              var count, _j, _len1;
              count = 0;
              for (_j = 0, _len1 = names.length; _j < _len1; _j++) {
                n = names[_j];
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
        };
      }
    ];
    return Admin_Main_Directive_TristateCheck;
  });

}).call(this);

//# sourceMappingURL=DpTristateCheck.js.map
