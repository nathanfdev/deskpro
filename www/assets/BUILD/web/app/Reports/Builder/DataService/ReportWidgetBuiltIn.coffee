define [
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], (
  ReportsBuiltInAbstract,
)  ->
  class ReportBuilderBuiltIn extends ReportsBuiltInAbstract

    getUrlPart: ->
      return 'widget'