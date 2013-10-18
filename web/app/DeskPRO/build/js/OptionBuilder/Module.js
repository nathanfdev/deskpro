(function() {
  define(['angular', 'DeskPRO/OptionBuilder/Controller'], function(angular, DeskPRO_OptionBuilder_Controller) {
    return angular.module('deskpro.option_builder', []).directive('dpOptionBuilder', [
      function() {
        return {
          restrict: 'E',
          require: 'ngModel',
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/OptionBuilder/control.html',
          replace: true,
          transclude: true,
          controller: DeskPRO_OptionBuilder_Controller.FACTORY,
          controllerAs: 'OptionBuilder',
          scope: {
            getTypesDef: '&typesDef',
            getOptions: '&options'
          }
        };
      }
    ]).directive('dpOptionbuilderRow', [
      function() {
        return {
          restrict: 'E',
          template: "<div class=\"dp-ob-row\">\n	<div class=\"remove-row-trigger\"><i class=\"icon-remove-sign\"></i></div>\n	<div class=\"dp-ob-row-content\" ng-transclude></div>\n</div>",
          replace: true,
          transclude: true
        };
      }
    ]);
  });

}).call(this);

/*
//@ sourceMappingURL=Module.js.map
*/