define [
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], (
  ReportsBuiltInAbstract,
)  ->
  class ReportBuilderBuiltIn extends ReportsBuiltInAbstract

    init: ->
      @setSubLists ['Tickets', 'Chats', 'Ideas', 'People & Organizations', 'Knowledgebase', 'News', 'Downloads',
                                  'Feedback', 'Tasks', 'Twitter']

    getUrlPart: ->
      return 'builder'