(function() {
  define(function() {
    return [
      '$compile', 'dpTemplateManager', function($compile, dpTemplateManager) {
        var directive;
        directive = {};
        directive.restrict = 'E';
        directive.replace = true;
        directive.template = function(el, attrs) {
          return dpTemplateManager.getNow("TicketDeps/layout-editor-" + attrs.type + "field.html");
        };
        directive.link = function(scope, element, attrs, ngModel) {
          return console.log("link field");
        };
        return directive;
      }
    ];
  });

}).call(this);

/*
//@ sourceMappingURL=LayoutEditorField.js.map
*/