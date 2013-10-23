(function() {
  define(['angular', 'DeskPRO/CategoryBuilder/Controller'], function(angular, DeskPRO_CategoryBuilder_Controller) {
    return angular.module('deskpro.category_builder', []).directive('dpCategoryBuilder', [
      function() {
        return {
          restrict: 'E',
          require: 'ngModel',
          template: "<div class=\"dp-category-builder\">\n	<ul class=\"dp-cb-root\">\n		<li class=\"dp-cb-addrow\">\n			<div class=\"dp-cb-rowwrap\">\n				<input type=\"text\" class=\"form-control input-sm\" />\n				<button class=\"btn btn-xs dp-cb-addbtn\">Add</button>\n			</div>\n		</li>\n	</ul>\n</div>",
          replace: true,
          controller: DeskPRO_CategoryBuilder_Controller.FACTORY,
          controllerAs: 'CategoryBuilder',
          link: function(scope, iElement, iAttrs, ngModel) {
            return scope.categoryBuilder.setModel(ngModel);
          }
        };
      }
    ]);
  });

}).call(this);

/*
//@ sourceMappingURL=Module.js.map
*/