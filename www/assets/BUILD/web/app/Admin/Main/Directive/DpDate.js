// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['moment'], function(moment){
  /*
    * Description
    * -----------
    *
    * This converts model datetime from UTC to local timezone when rendered and back when saved
    *
    */
  const Admin_Main_Directive_DpDate = ['DpDateService', '$parse', (ds, $parse) =>
    ({
      restrict: 'A',
      scope: {
        dpDate: "=dpDate"
      },
      link(scope, el, attr) {
        const format = attr.format || "fulltime";

        const update = function() {
          const datestr = scope.dpDate;
          let result  = null;

          if (datestr) {
            result = ds.format(datestr, format);
          }

          if (result) {
            return el.text(ds.format(datestr, format));
          } else if (datestr) {
            return el.text(datestr);
          }
        };

        update();

        return scope.$watch('dpDate', () => update());
      }
    })
  
  ];

  return Admin_Main_Directive_DpDate;
});