// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Strings'
], function(Strings) {
  const Admin_Main_Directive_DpOrderMenu = [ () =>
    ({
      restrict: 'AE',
      replace: true,
      require: 'ngModel',
      transclude: true,
      template: `\
<span class="dp-order-ctrl dropdown">
  <div class="orig" style="display: none;" ng-transclude></div>
  <a class="title dropdown-toggle" data-toggle="dropdown">Order by: {{title}} <i class="fas fa-sort-alpha-up" ng-show="sortDir == 'DESC'"></i><i class="fa fa-sort-alpha-asc" ng-show="sortDir == 'ASC'"></i></a>
  <ul class="dropdown-menu">
    <li class="dropdown-header">Sort Field</li>
    <li ng-repeat="opt in options"><a ng-click="$event.preventDefault(); setSortField(opt.value);">{{opt.title}} <i class="fa fa-check" ng-show="sortField == opt.value"></i></a></li>
    <li class="divider"></li>
    <li class="dropdown-header">Sort Direction</li>
    <li><a ng-click="$event.preventDefault(); setSortDirection('ASC');">Ascending <i class="fa fa-check" ng-show="sortDir == 'ASC'"></i></a></li>
    <li><a ng-click="$event.preventDefault(); setSortDirection('DESC');">Descending <i class="fa fa-check" ng-show="sortDir == 'DESC'"></i></a></li>
  </ul>
</span>\
`,
      link(scope, element, attrs, ngModel) {
        scope.sortField = null;
        scope.sortDir = 'ASC';
        scope.title = '';
        scope.options = [];

        const orderPrefs = attrs.orderDirPrefs ? scope.$eval(attrs.orderDirPrefs) : {};

        element.find('.orig').find('option').each(function() {
          return scope.options.push({
            title: Strings.trim($(this).text()),
            value: $(this).val()
          });
        });

        scope.setSortField = field => scope.sortField = field;
        scope.setSortDirection = dir => scope.sortDir = dir;

        ngModel.$parsers.push( viewValue => viewValue);

        ngModel.$formatters.push( modelValue => modelValue);

        ngModel.$render = function() {
          scope.sortField = (ngModel.$viewValue != null ? ngModel.$viewValue.field : undefined) || scope.options[0].value;
          scope.sortDir   = (ngModel.$viewValue != null ? ngModel.$viewValue.dir : undefined) || 'ASC';

          return (() => {
            const result = [];
            for (let v of Array.from(scope.options)) {
              if (v.value === scope.sortField) {
                scope.title = v.title;
                break;
              } else {
                result.push(undefined);
              }
            }
            return result;
          })();
        };

        const update = function() {
          ngModel.$setViewValue({
            field: scope.sortField,
            dir: scope.sortDir
          });

          return (() => {
            const result = [];
            for (let v of Array.from(scope.options)) {
              if (v.value === scope.sortField) {
                scope.title = v.title;
                break;
              } else {
                result.push(undefined);
              }
            }
            return result;
          })();
        };

        scope.$watch('sortField', function() {
          if (orderPrefs[scope.sortField]) {
            scope.sortDir = orderPrefs[scope.sortField];
          }

          return update();
        });

        return scope.$watch('sortDir', () => update());
      }
    })
  
  ];

  return Admin_Main_Directive_DpOrderMenu;
});