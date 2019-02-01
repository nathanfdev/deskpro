define([
  'Reports/Builder/DataService/ReportCustomAbstract'
], function(
  ReportCustomAbstract
)  {
  class ReportBuilderCustom extends ReportCustomAbstract {
    getUrlPart() {
      return 'builder';
    }
  }
  return ReportBuilderCustom;
});
