define [
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], (
  ReportBuiltInAbstract
)  ->
  class ReportBuilderCustom extends ReportBuiltInAbstract
    getUrlPart: ->
      return 'builder'