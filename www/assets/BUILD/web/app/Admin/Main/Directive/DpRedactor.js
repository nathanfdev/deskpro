define(['redactor', 'jquery'], (redactor, $) => {
  /*
   * Description
   * -----------
   * textarea editor, moved from agent iface
   *
   */
  const Admin_Main_Directive_DpRedactor = [('$timeout'), $timeout =>
    ({
      restrict: 'A',
      require:  'ngModel',
      link(scope, element, attrs, ngModel) {
        let api = null;

        const defaults = {
          minHeight: 100
        };

        const updateModel = val =>
          $timeout(() =>
            scope.$apply(() => ngModel.$setViewValue(val))
          )
        ;

        ngModel.$render = function () {
          if (api) { return $timeout(() => api.setCode(ngModel.$viewValue || '')); }
        };

        return $timeout(() => {
          element.redactor(defaults);
          api = element.data('redactor');

          const origSyncCode = api.syncCode;
          api.syncCode = function () {
            origSyncCode.call(api);
            return updateModel(api.getCode());
          };

          return ngModel.$render();
        });
      }
    })

  ];

  return Admin_Main_Directive_DpRedactor;
});
