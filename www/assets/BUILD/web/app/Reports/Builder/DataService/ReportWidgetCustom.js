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
  let ReportWidgetCustom;
  return (ReportWidgetCustom = class ReportWidgetCustom extends ReportCustomAbstract {
    getUrlPart() {
      return 'widget';
    }
  });
});