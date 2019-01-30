// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
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
    * Custom on-change directive
    *
    * Example
    * -------
    * <input dp-change="submit" />
    */
  const Admin_Main_Directive_DpChange = [ () =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        return element.bind('change', () => scope.$eval(attrs.dpChange));
      }
    })
  
  ];

  return Admin_Main_Directive_DpChange;
});