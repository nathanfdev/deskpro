define([
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], function(
  ReportsBuiltInAbstract
)  {
  class ReportBuilderBuiltIn extends ReportsBuiltInAbstract {

    init() {
      return this.setSubLists(['Tickets', 'Chats', 'Ideas', 'People & Organizations', 'Knowledgebase', 'News', 'Downloads',
                                  'Feedback', 'Tasks', 'Twitter']);
    }

    getUrlPart() {
      return 'builder';
    }
  }
  return ReportBuilderBuiltIn;
});
