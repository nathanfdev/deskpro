(function() {
  define(['angular', 'DeskPRO/CategoryBuilder/Controller'], function(angular, DeskPRO_CategoryBuilder_Controller) {
    return angular.module('deskpro.category_builder', []).directive('dpCategoryBuilder', [
      function() {
        return {
          restrict: 'E',
          require: 'ngModel',
          template: "<div class=\"dp-category-builder\">\n	<ul class=\"dp-cb-root\" ui-sortable=\"sortedListOptions\"></ul>\n	<div class=\"dp-cb-newrow\">\n		<input type=\"text\" class=\"form-control\" ng-model=\"new_cat_title\" placeholder=\"Enter a title...\" />\n		<span class=\"dp-cb-select-wrap\">\n			<select ng-model=\"new_cat_parent\"\n				ui-select2\n				style=\"min-width:200px;\"\n			>\n				<option value=\"0\">No parent</option>\n				<option value=\"{{c.id}}\" ng-repeat=\"c in parent_cat_list\">{{c.title}}</option>\n			</select>\n		</span>\n		<button class=\"btn dp-cb-addbtn\">Add</button>\n	</div>\n</div>",
          replace: true,
          controller: DeskPRO_CategoryBuilder_Controller.FACTORY,
          controllerAs: 'CategoryBuilder',
          scope: {
            saveFlatArray: '='
          },
          link: function(scope, iElement, iAttrs, ngModel) {
            return scope.categoryBuilder.setModel(ngModel);
          }
        };
      }
    ]);
  });

}).call(this);

//# sourceMappingURL=Module.js.map
