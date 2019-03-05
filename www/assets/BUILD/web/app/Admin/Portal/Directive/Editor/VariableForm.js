define([], function() {
  const Admin_Portal_Directive_Editor_VariableForm = [() =>
    ({
      restrict:    'E',
      templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Portal/Editor/variable-form.html`,
      scope:       {
        variable: '=',
        values:   '=',
      },
      link(scope, element, attrs, ngModel) {}
    })

  ];

  return Admin_Portal_Directive_Editor_VariableForm;
});
