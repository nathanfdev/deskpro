/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/Builder/DataService/ReportCustomAbstract'
], function(
  ReportCustomAbstract
)  {
  let ReportBuilderCustom;
  return (ReportBuilderCustom = class ReportBuilderCustom extends ReportCustomAbstract {
    getUrlPart() {
      return 'builder';
    }
  });
});
