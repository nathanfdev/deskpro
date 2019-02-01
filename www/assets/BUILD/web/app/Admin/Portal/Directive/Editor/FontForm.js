define([], () => {
  const Admin_Portal_Directive_Editor_FontForm = [() =>
    ({
      restrict:    'E',
      templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Portal/Editor/font-form.html`,
      scope:       {
        variable: '=',
        values:   '='
      },
      link(scope) {
        scope.stack1 = 'Open Sans, Helvetica, Arial, Sans Serif';
        scope.stack2 = 'Baskerville, Georgia, Serif';
        scope.stack3 = 'Lato, Myriad Pro, Arial, Sans Serif';
        scope.stack4 = 'Ubuntu, Trebuchet, Arial, Sans Serif';

        scope.select = font => scope.values[scope.variable.name] = font;
        scope.isSelected = font => scope.values[scope.variable.name] === font;
        scope.selectCustom = () => scope.values[scope.variable.name] = '';
        return scope.isCustomSelected = function () {
          const val = scope.values[scope.variable.name];
          return (val !== scope.stack1) && (val !== scope.stack2) && (val !== scope.stack3) && (val !== scope.stack4);
        };
      }
    })

  ];

  return Admin_Portal_Directive_Editor_FontForm;
});
