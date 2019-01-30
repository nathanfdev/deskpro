/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  /*
    * Description
    * -----------
    *
    * This sets the initial focus once a form is loaded.
    *
    * Example
    * -------
    * <input autofocus>
  */
  const Admin_Main_Directive_Autofocus = [ '$rootScope', '$timeout', ($rootScope, $timeout) =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        element.focus();

        const done = false;
        $timeout(() => element.focus()
        , 150);
        return $timeout(() => element.focus()
        , 250);
      }
    })
  
  ];

  return Admin_Main_Directive_Autofocus;
});