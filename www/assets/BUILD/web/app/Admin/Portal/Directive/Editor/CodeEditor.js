define [], () ->
  Admin_Portal_Directive_Editor_CodeEditor = [ ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/code-editor.html',
      scope: {
        info: '=',
        syntax: '@',
        visible: '=',
        cancel: '&',
        save: '&',
        revert: '&'
      }
    }
  ]

  return Admin_Portal_Directive_Editor_CodeEditor
