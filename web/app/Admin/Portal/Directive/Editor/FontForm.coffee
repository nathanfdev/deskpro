define [], () ->
  Admin_Portal_Directive_Editor_FontForm = [ ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/font-form.html',
      scope: {
        variable: '=',
        values: '='
      },
      link: (scope) ->
        scope.selected = 1
        scope.selectStack = (stack) ->
          scope.selected = stack
          scope.values[scope.variable.name] = {stack}
          if stack != 'custom'
            scope.values[scope.variable.name].custom = ''
        scope.isStackSelected = (stack) -> scope.selected == stack
    }
  ]

  return Admin_Portal_Directive_Editor_FontForm