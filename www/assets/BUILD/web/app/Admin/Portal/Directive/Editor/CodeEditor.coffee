define [], () ->
  Admin_Portal_Directive_Editor_CodeEditor = [ ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/code-editor.html',
      scope: {
        code: '=',
        is_custom: '=',
        syntax: '@',
        visible: '=',
        cancel: '&',
        save: '&',
        revert: '&'
      },
      link: (scope, element, attrs) ->
        scope.save   = scope.save();
        scope.cancel = scope.cancel();
        scope.revert = scope.revert();
    }
  ]

  return Admin_Portal_Directive_Editor_CodeEditor
