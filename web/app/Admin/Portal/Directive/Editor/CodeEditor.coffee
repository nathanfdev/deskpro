define [], () ->
  Admin_Portal_Directive_Editor_CodeEditor = [ ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/code-editor.html',
      scope: {
        code: '=',
        syntax: '@',
        visible: '=',
        close: '&'
      },
      link: (scope, element, attrs) ->
        scope.close = scope.close();
    }
  ]

  return Admin_Portal_Directive_Editor_CodeEditor