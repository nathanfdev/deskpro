define(function() {
  /*
    * Description
    * -----------
    *
    * Custom on-change directive
    *
    * Example
    * -------
    * <input dp-change="submit" />
    */
  const Admin_Main_Directive_DpChange = [() =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        return element.bind('change', () => scope.$eval(attrs.dpChange));
      }
    })

  ];

  return Admin_Main_Directive_DpChange;
});
