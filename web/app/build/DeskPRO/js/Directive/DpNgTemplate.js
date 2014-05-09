(function() {
  define(['angular'], function(angular) {

    /*
        * Description
        * -----------
        *
        * Just like using 'text/ng-template' except this expects the template to be an inner JSON object with
        * 'name' and 'template' properties. This has the added benefit of allowing you to have templates with <script> tags inside.
     */
    var DeskPRO_Directive_DpJsonData;
    DeskPRO_Directive_DpJsonData = [
      '$templateCache', function($templateCache) {
        return {
          restrict: 'E',
          terminal: true,
          compile: function(element, attrs) {
            var data, err, id;
            if (attrs.type !== 'text/dp-ng-template') {
              return null;
            }
            id = attrs.id || null;
            try {
              data = angular.fromJson(element[0].text);
            } catch (_error) {
              err = _error;
              console.error("Error parsing JSON in dp-ng-template");
              console.debug(element);
              return;
            }
            if (!id && !data.name) {
              console.error("No template name in dp-ng-template");
              console.debug(element);
              return;
            }
            if (!id) {
              id = data.name;
            }
            if (!data.template) {
              console.error("No template code in dp-ng-template");
              console.debug(element);
              return;
            }
            $templateCache.put(id, data.template);
            return null;
          }
        };
      }
    ];
    return DeskPRO_Directive_DpJsonData;
  });

}).call(this);

//# sourceMappingURL=DpNgTemplate.js.map
