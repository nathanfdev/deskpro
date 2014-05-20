(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var Admin_Main_Directive_DpOrderMenu;
    Admin_Main_Directive_DpOrderMenu = [
      function() {
        return {
          restrict: 'AE',
          replace: true,
          require: 'ngModel',
          transclude: true,
          template: "<span class=\"dp-order-ctrl dropdown\">\n	<div class=\"orig\" style=\"display: none;\" ng-transclude></div>\n	<a class=\"title dropdown-toggle\" data-toggle=\"dropdown\">Order by: {{title}} <i class=\"fa fa-sort-alpha-desc\" ng-show=\"sortDir == 'DESC'\"></i><i class=\"fa fa-sort-alpha-asc\" ng-show=\"sortDir == 'ASC'\"></i></a>\n	<ul class=\"dropdown-menu\">\n		<li class=\"dropdown-header\">Sort Field</li>\n		<li ng-repeat=\"opt in options\"><a ng-click=\"$event.preventDefault(); setSortField(opt.value);\">{{opt.title}} <i class=\"fa fa-check\" ng-show=\"sortField == opt.value\"></i></a></li>\n		<li class=\"divider\"></li>\n		<li class=\"dropdown-header\">Sort Direction</li>\n		<li><a ng-click=\"$event.preventDefault(); setSortDirection('ASC');\">Ascending <i class=\"fa fa-check\" ng-show=\"sortDir == 'ASC'\"></i></a></li>\n		<li><a ng-click=\"$event.preventDefault(); setSortDirection('DESC');\">Descending <i class=\"fa fa-check\" ng-show=\"sortDir == 'DESC'\"></i></a></li>\n	</ul>\n</span>",
          link: function(scope, element, attrs, ngModel) {
            scope.sortField = null;
            scope.sortDir = 'ASC';
            scope.title = '';
            scope.options = [];
            element.find('.orig').find('option').each(function() {
              return scope.options.push({
                title: Strings.trim($(this).text()),
                value: $(this).val()
              });
            });
            scope.setSortField = function(field) {
              return scope.sortField = field;
            };
            scope.setSortDirection = function(dir) {
              return scope.sortDir = dir;
            };
            ngModel.$parsers.push(function(viewValue) {
              return viewValue;
            });
            ngModel.$formatters.push(function(modelValue) {
              return modelValue;
            });
            ngModel.$render = function() {
              var v, _i, _len, _ref, _ref1, _ref2, _results;
              scope.sortField = ((_ref = ngModel.$viewValue) != null ? _ref.field : void 0) || scope.options[0].value;
              scope.sortDir = ((_ref1 = ngModel.$viewValue) != null ? _ref1.dir : void 0) || 'ASC';
              _ref2 = scope.options;
              _results = [];
              for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
                v = _ref2[_i];
                if (v.value === scope.sortField) {
                  scope.title = v.title;
                  break;
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            };
            return scope.$watch('sortField + sortDir', function() {
              var v, _i, _len, _ref, _results;
              ngModel.$setViewValue({
                field: scope.sortField,
                dir: scope.sortDir
              });
              _ref = scope.options;
              _results = [];
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                v = _ref[_i];
                if (v.value === scope.sortField) {
                  scope.title = v.title;
                  break;
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpOrderMenu;
  });

}).call(this);

//# sourceMappingURL=DpOrderMenu.js.map
