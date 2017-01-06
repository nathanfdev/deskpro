define [
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], (
  ReportBuiltInAbstract
)  ->
  class ReportWidgetCustom extends ReportBuiltInAbstract
    getUrlPart: ->
      return 'widget'