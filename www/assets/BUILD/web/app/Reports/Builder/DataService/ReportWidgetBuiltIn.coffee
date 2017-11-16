define [
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], (
  ReportsBuiltInAbstract,
)  ->
  class ReportWidgetBuiltIn extends ReportsBuiltInAbstract

    getUrlPart: ->
      return 'widget'