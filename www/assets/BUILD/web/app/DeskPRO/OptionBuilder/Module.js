/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'angular',
  'DeskPRO/OptionBuilder/Controller',
  'DeskPRO/Util/Arrays'
], (
  angular,
  DeskPRO_OptionBuilder_Controller,
  Arrays
) =>
  angular.module('deskpro.option_builder', [])
    .directive('dpOptionBuilder', [ () =>
      ({
        restrict: 'E',
        templateUrl: DP_BASE_ADMIN_URL+'/load-view/OptionBuilder/control.html',
        replace: true,
        transclude: true,
        controller: DeskPRO_OptionBuilder_Controller.FACTORY,
        controllerAs: 'OptionBuilder',
        scope: {
          getTypesDef: '&typesDef',
          getOptions:  '&options',
          optionTypes: '=optionTypes',
          saveTarget: '=saveTarget'
        }
      })
    
    ])
    .directive('dpOptionbuilderRow', [ '$timeout', $timeout =>
      ({
        restrict: 'E',
        template: `\
<div class="dp-ob-row">
  <div class="remove-row-trigger" ng-click="rowFn.removeRow()" ng-if="!rowOpts.hideRemove"><i class="fa fa-times-circle"></i></div>
  <table cellspacing="0" cellpadding="0" width="100%" style="margin: 0; padding: 0; border: none;">
    <tr>
      <td style="vertical-align: middle; padding: 0; margin: 0;"><div class="dp-ob-row-tag-wrap"></div></td>
      <td style="vertical-align: middle; padding: 0; margin: 0;" width="1"><input type="checkbox" id="{{rowOpts.withCheckId}}" class="rowOpts-withCheck" ng-if="rowOpts.withCheck" ng-model="rowOpts.rowEnabled" ng-disabled="rowOpts.isFixedOn" /></td>
      <td style="vertical-align: middle; padding: 0; margin: 0;" width="100%">
        <div class="dp-ob-row-content-wrap" ng-class="{'as-post-render': doShow}">
          <div class="dp-ob-row-content-placeholder" ng-if="!doShow">
            <span class="place1"></span> <span class="place2"></span> <span class="place3"></span>
          </div>
          <div class="dp-ob-row-content" ng-class="{'as-post-render': doShow}" ng-transclude></div>
        </div>
      </td>
    </tr>
  </table>
</div>\
`,
        replace: true,
        transclude: true,
        link(scope, element, attrs) {
          const tagWrap = element.find('.dp-ob-row-tag-wrap');
          const updateTag = function() {
            let tag = tagWrap.find('.dp-ob-row-tag');
            if (((scope.rowOpts != null ? scope.rowOpts.rowIdx : undefined) > 1) && (scope.rowOpts != null ? scope.rowOpts.tagString : undefined)) {
              if (!tag[0]) {
                tag = $('<em class="dp-ob-row-tag"></em>').addClass(scope.rowOpts.tagClass);
                tag.prependTo(tagWrap);
              }

              tag.text(scope.rowOpts != null ? scope.rowOpts.tagString : undefined);
              return tagWrap.addClass('with-tag');
            } else {
              if (tag[0]) { tag.remove(); }
              return tagWrap.addClass('without-tag');
            }
          };

          scope.$watch('rowOpts.rowIdx', () => updateTag());
          updateTag();

          return $timeout(() =>
            $timeout(() => $timeout(() => scope.doShow = true))
          );
        }
      })
    
    ]).directive('dpOptionBuilderSet', [ '$compile', '$templateCache', ($compile, $templateCache) =>
      ({
      restrict: 'A',
      link(scope, iElement, iAttrs) {

        let rows = [];
        let opts = scope.$eval(iAttrs.dpOptionBuilderSet);
        scope.setCount = 0;
        let lastEmpty = null;
        const containRow = iElement.find('.dp-ob-addition-setrow');

        const reset = function(withSet) {
          opts = scope.$eval(iAttrs.dpOptionBuilderSet);
          scope.setCount = 0;
          lastEmpty = null;
          containRow.empty();
          rows = [];

          let any = false;
          for (let setId of Object.keys(withSet || {})) {
            const set = withSet[setId];
            addRow(setId);
            any = true;
          }

          if (!any) {
            return addRow();
          }
        };

        scope.$watch(iAttrs.setsObject, newVal => reset(newVal));

        const recountRows = () =>
          (() => {
            const result = [];
            for (var i = 0; i < rows.length; i++) {
              var row = rows[i];
              if (row.rowScope.setIndex !== (i+1)) {
                result.push(row.rowScope.$apply(function() {
                  row.rowScope.setIndex = i+1;

                  if (row.rowScope.setIndex === 1) {
                    return row.element.find('.remove-btn-wrap').hide();
                  } else {
                    return row.element.find('.remove-btn-wrap').show();
                  }
                }));
              } else {
                result.push(undefined);
              }
            }
            return result;
          })()
        ;

        var addRow = function(useExistSetId) {
          let setId;
          const tpl = $templateCache.get(opts.template);
          const rowScope = scope.$new();

          let setsObject = scope.$eval(iAttrs.setsObject);
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
          rowScope.option_types     = opts.option_types;

          rowScope.$on('rowAdded', function() {
            if ((element.hasClass('empty') || (scope.setCount <= 1)) && (lastEmpty === element)) {
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
          rowScope.setIndex = rows.length+1;

          var element = $compile(tpl)(rowScope);

          rows.push({
            rowScope,
            element
          });

          if (scope.setCount >= 1) {
            element.addClass('empty');
          }

          if (rowScope.setIndex === 1) {
            element.find('.remove-btn-wrap').hide();
          } else {
            element.find('.remove-btn-wrap').show();
          }

          element.find('.removerow_btn').on('click', function(ev) {
            ev.preventDefault();
            rowScope.$destroy();
            scope.setCount -= 1;
            element.remove();

            Arrays.findAndRemove(rows, v => v.rowScope === rowScope);

            // unset options that were on the set so the model is updated
            for (let k of Object.keys(rowScope.criteria_set_row || {})) {
              const v = rowScope.criteria_set_row[k];
              delete rowScope.criteria_set_row[k];
            }

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
      })
    
    ])
);