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
    ]).directive('dpOptionBuilderSet', [
      '$compile', '$templateCache', function($compile, $templateCache) {
        return {
          restrict: 'A',
          link: function(scope, iElement, iAttrs) {
            var addRow, opts;
            opts = scope.$eval(iAttrs.dpOptionBuilderSet);
            addRow = function() {
              var containRow, element, rowScope, setId, tpl;
              containRow = iElement.find('.dp-ob-addition-setrow');
              setId = _.uniqueId('set');
              opts.setsObject[setId] = {};
              tpl = $templateCache.get(opts.template);
              rowScope = scope.$new();
              rowScope.criteria_typedef = opts.typedef;
              rowScope.criteria_set_row = opts.setsObject[setId];
              element = $compile(tpl)(rowScope);
              element.find('.removerow_btn').on('click', function(ev) {
                ev.preventDefault();
                rowScope.$destroy();
                return element.slideUp(200, function() {
                  return element.remove();
                });
              });
              return containRow.append(element);
            };
            iElement.find('.add_btn').on('click', function(ev) {
              ev.preventDefault();
              return addRow();
            });
            return addRow();
          }
        };
      }
    ]);
  });

}).call(this);

/*
//@ sourceMappingURL=Module.js.map
*/