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
          template: "<div class=\"dp-ob-row\">\n	<div class=\"remove-row-trigger\"><i class=\"icon-remove-sign\"></i></div>\n	<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" style=\"margin: 0; padding: 0; border: none;\">\n		<tr>\n			<td style=\"vertical-align: middle; padding: 0; margin: 0;\"><div class=\"dp-ob-row-tag-wrap\"></div></td>\n			<td style=\"vertical-align: middle; padding: 0; margin: 0;\" width=\"100%\">\n				<div class=\"dp-ob-row-content\" ng-transclude></div>\n			</td>\n		</tr>\n	</table>\n</div>",
          replace: true,
          transclude: true,
          link: function(scope, element, attrs) {
            var tag;
            if (scope.tag != null) {
              tag = $('<em class="dp-ob-row-tag"></em>').addClass(scope.tag).text(scope.tag);
              return tag.prependTo(element.find('.dp-ob-row-tag-wrap').addClass('with-tag'));
            }
          }
        };
      }
    ]);
  });

}).call(this);

/*
//@ sourceMappingURL=Module.js.map
*/