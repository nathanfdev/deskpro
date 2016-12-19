define [], () ->
  Admin_Portal_Directive_Editor_SizeForm = [ ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/size-form.html',
      scope: {
        variable: '=',
        values: '='
      },
      link: (scope, element, attrs, ngModel) ->
        if not scope.values[scope.variable.name]
          scope.values[scope.variable.name] = {}
    }
  ]

  return Admin_Portal_Directive_Editor_SizeForm