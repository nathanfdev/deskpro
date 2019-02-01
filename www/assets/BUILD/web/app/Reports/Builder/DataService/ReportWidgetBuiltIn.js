define([
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], function(
  ReportsBuiltInAbstract
)  {
  class ReportWidgetBuiltIn extends ReportsBuiltInAbstract {
    getUrlPart() {
      return 'widget';
    }
  }
  return ReportWidgetBuiltIn;
});
