(function() {
  define(['angular', 'DeskPRO/CategoryBuilder/Controller'], function(angular, DeskPRO_CategoryBuilder_Controller) {
    return angular.module('deskpro.category_builder', []).directive('dpCategoryBuilder', [
      function() {
        return {
          restrict: 'E',
          require: 'ngModel',
          template: "<div class=\"dp-category-builder\"></div>",
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