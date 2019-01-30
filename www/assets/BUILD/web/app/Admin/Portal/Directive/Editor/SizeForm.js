/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([], function() {
  const Admin_Portal_Directive_Editor_SizeForm = [ () =>
    ({
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/size-form.html',
      scope: {
        variable: '=',
        values: '='
      },
      link(scope, element, attrs, ngModel) {
        if (!scope.values[scope.variable.name]) {
          return scope.values[scope.variable.name] = {};
        }
      }
    })
  
  ];

  return Admin_Portal_Directive_Editor_SizeForm;
});