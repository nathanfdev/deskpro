define [], () ->
  Admin_Portal_Directive_Editor_ColorForm = [ ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Portal/Editor/color-form.html',
      scope: {
        variable: '=',
        values: '='
      },
      link: (scope, element, attrs, ngModel) ->
    }
  ]

  return Admin_Portal_Directive_Editor_ColorForm