define(['angular'], function(angular) {
  /*
    * Description
    * -----------
    *
    * Lets evaluate JSON data in the view to assign it to the scope.
    * Useful for when you have data from the system (e.g., app settings)
    * that you want to make available to the scope without having to make API calls.
  */
  const DeskPRO_Directive_DpJsonData = [ () =>
    ({
      restrict: 'E',
      terminal: true,
      compile(element, attrs) {
        if (attrs.type !== 'text/dp-json-data') {
          return null;
        }

        const assign = attrs['assign'] || null;
        const method = attrs['method'] || null;
        const exec   = attrs['exec']   || null;
        const json   = element[0].text;

        return {
          pre(scope, iElement, iAttrs) {
            let data;
            try {
              data = angular.fromJson(json);
            } catch (err) {
              console.error("Error parsing JSON in text/dp-json-data");
              console.debug(element);
              return;
            }

            if (assign) {
              scope.$eval(assign + " = __dp_data", {"__dp_data": data});
            }
            if (method) {
              scope.$eval(method + "(__dp_data)", {"__dp_data": data});
            }
            if (exec) {
              return scope.$eval(exec, {"data": data});
            }
          },

          post(scope, iElement, iAttrs) {
          }
        };
      }
    })
  

  ];

  return DeskPRO_Directive_DpJsonData;
});