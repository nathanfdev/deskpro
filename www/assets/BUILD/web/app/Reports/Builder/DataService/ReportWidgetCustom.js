define([
  'Reports/Builder/DataService/ReportCustomAbstract'
], function(
  ReportCustomAbstract
)  {
  class ReportWidgetCustom extends ReportCustomAbstract {
    getUrlPart() {
      return 'widget';
    }
  }
  return ReportWidgetCustom;
});