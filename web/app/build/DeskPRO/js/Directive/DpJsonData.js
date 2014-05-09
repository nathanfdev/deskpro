(function() {
  define(['angular'], function(angular) {

    /*
        * Description
        * -----------
        *
        * Lets evaluate JSON data in the view to assign it to the scope.
        * Useful for when you have data from the system (e.g., app settings)
        * that you want to make available to the scope without having to make API calls.
     */
    var DeskPRO_Directive_DpJsonData;
    DeskPRO_Directive_DpJsonData = [
      function() {
        return {
          restrict: 'E',
          terminal: true,
          compile: function(element, attrs) {
            var assign, exec, json, method;
            if (attrs.type !== 'text/dp-json-data') {
              return null;
            }
            assign = attrs['assign'] || null;
            method = attrs['method'] || null;
            exec = attrs['exec'] || null;
            json = element[0].text;
            return {
              pre: function(scope, iElement, iAttrs) {
                var data, err;
                try {
                  data = angular.fromJson(json);
                } catch (_error) {
                  err = _error;
                  console.error("Error parsing JSON in text/dp-json-data");
                  console.debug(element);
                  return;
                }
                if (assign) {
                  scope.$eval(assign + " = __dp_data", {
                    "__dp_data": data
                  });
                }
                if (method) {
                  scope.$eval(method + "(__dp_data)", {
                    "__dp_data": data
                  });
                }
                if (exec) {
                  return scope.$eval(exec, {
                    "data": data
                  });
                }
              },
              post: function(scope, iElement, iAttrs) {}
            };
          }
        };
      }
    ];
    return DeskPRO_Directive_DpJsonData;
  });

}).call(this);

//# sourceMappingURL=DpJsonData.js.map
