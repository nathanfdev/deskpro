(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['angular', 'DeskPRO/OptionBuilder/Controller'], function(angular, DeskPRO_OptionBuilder_Controller) {
    return angular.module('deskpro.option_builder', []).directive('dpOptionBuilder', [
      function() {
        return {
          restrict: 'E',
          templateUrl: DP_BASE_ADMIN_URL + '/load-view/OptionBuilder/control.html',
          replace: true,
          transclude: true,
          controller: DeskPRO_OptionBuilder_Controller.FACTORY,
          controllerAs: 'OptionBuilder',
          scope: {
            getTypesDef: '&typesDef',
            getOptions: '&options',
            optionTypes: '=optionTypes',
            saveTarget: '=saveTarget'
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
            } else {
              tag = $('<em class="dp-ob-row-tag"></em>').addClass('no-tag');
              return tag.prependTo(element.find('.dp-ob-row-tag-wrap').addClass('without-tag'));
            }
          }
        };
      }
    ]).directive('dpOptionBuilderSet', [
      '$compile', '$templateCache', function($compile, $templateCache) {
        return {
          restrict: 'A',
          link: function(scope, iElement, iAttrs) {
            var addRow, containRow, lastEmpty, opts, reset;
            opts = scope.$eval(iAttrs.dpOptionBuilderSet);
            scope.setCount = 0;
            lastEmpty = null;
            containRow = iElement.find('.dp-ob-addition-setrow');
            reset = function(withSet) {
              var any, set, setId;
              opts = scope.$eval(iAttrs.dpOptionBuilderSet);
              scope.setCount = 0;
              lastEmpty = null;
              containRow.empty();
              any = false;
              for (setId in withSet) {
                if (!__hasProp.call(withSet, setId)) continue;
                set = withSet[setId];
                addRow(setId);
                any = true;
              }
              if (!any) {
                return addRow();
              }
            };
            scope.$watch(iAttrs.setsObject, function(newVal) {
              return reset(newVal);
            });
            addRow = function(useExistSetId) {
              var element, rowScope, setId, setsObject, tpl;
              tpl = $templateCache.get(opts.template);
              rowScope = scope.$new();
              setsObject = scope.$eval(iAttrs.setsObject);
              if (!setsObject) {
                setsObject = {};
              }
              if (useExistSetId) {
                setId = useExistSetId;
              } else {
                setId = rowScope.$id;
                setsObject[setId] = {};
              }
              rowScope.criteria_typedef = opts.typedef;
              rowScope.criteria_set_row = setsObject[setId];
              rowScope.option_types = opts.option_types;
              rowScope.$on('rowAdded', function() {
                if (element.hasClass('empty') && lastEmpty === element) {
                  addRow();
                }
                return element.removeClass('empty');
              });
              rowScope.$on('rowRemoved', function(ev, ctrl, e, s, rowsCount) {
                if (rowsCount === 0) {
                  if (scope.setCount === 1) {
                    return element.addClass('empty');
                  }
                }
              });
              element = $compile(tpl)(rowScope);
              element.addClass('empty');
              element.find('.removerow_btn').on('click', function(ev) {
                ev.preventDefault();
                rowScope.$destroy();
                element.slideUp(200, function() {
                  element.remove();
                  if (scope.setCount === 0) {
                    return addRow();
                  }
                });
                return scope.setCount -= 1;
              });
              containRow.append(element);
              scope.setCount += 1;
              return lastEmpty = element;
            };
            iElement.find('.add_btn').on('click', function(ev) {
              ev.preventDefault();
              return addRow();
            });
            return reset();
          }
        };
      }
    ]);
  });

}).call(this);

/*
//@ sourceMappingURL=Module.js.map
*/