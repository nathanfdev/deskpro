(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['angular', 'DeskPRO/OptionBuilder/Controller', 'DeskPRO/Util/Arrays'], function(angular, DeskPRO_OptionBuilder_Controller, Arrays) {
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
          template: "<div class=\"dp-ob-row\">\n	<div class=\"remove-row-trigger\"><i class=\"fa fa-times-circle\"></i></div>\n	<table cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" style=\"margin: 0; padding: 0; border: none;\">\n		<tr>\n			<td style=\"vertical-align: middle; padding: 0; margin: 0;\"><div class=\"dp-ob-row-tag-wrap\"></div></td>\n			<td style=\"vertical-align: middle; padding: 0; margin: 0;\" width=\"100%\">\n				<div class=\"dp-ob-row-content\" ng-transclude></div>\n			</td>\n		</tr>\n	</table>\n</div>",
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
            var addRow, containRow, lastEmpty, opts, recountRows, reset, rows;
            rows = [];
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
              rows = [];
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
            recountRows = function() {
              var i, row, _i, _len, _results;
              _results = [];
              for (i = _i = 0, _len = rows.length; _i < _len; i = ++_i) {
                row = rows[i];
                if (row.rowScope.setIndex !== i + 1) {
                  _results.push(row.rowScope.$apply(function() {
                    return row.rowScope.setIndex = i + 1;
                  }));
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            };
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
                if ((element.hasClass('empty') || scope.setCount <= 1) && lastEmpty === element) {
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
              rowScope.setIndex = rows.length + 1;
              element = $compile(tpl)(rowScope);
              rows.push({
                rowScope: rowScope,
                element: element
              });
              if (scope.setCount >= 1) {
                element.addClass('empty');
              }
              element.find('.removerow_btn').on('click', function(ev) {
                ev.preventDefault();
                rowScope.$destroy();
                scope.setCount -= 1;
                element.remove();
                Arrays.findAndRemove(rows, function(v) {
                  return v.rowScope === rowScope;
                });
                recountRows();
                if (scope.setCount === 0) {
                  return addRow();
                }
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

//# sourceMappingURL=Module.js.map
