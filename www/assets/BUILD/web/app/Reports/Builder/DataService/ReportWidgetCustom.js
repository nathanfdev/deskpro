define [
  'Reports/Builder/DataService/ReportCustomAbstract'
], (
  ReportCustomAbstract
)  ->
  class ReportWidgetCustom extends ReportCustomAbstract
    getUrlPart: ->
      return 'widget'