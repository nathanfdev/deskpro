define([
  'Reports/Builder/DataService/ReportCustomAbstract'
], (
  ReportCustomAbstract
) => {
  class ReportBuilderCustom extends ReportCustomAbstract {
    getUrlPart() {
      return 'builder';
    }
  }
  return ReportBuilderCustom;
});
