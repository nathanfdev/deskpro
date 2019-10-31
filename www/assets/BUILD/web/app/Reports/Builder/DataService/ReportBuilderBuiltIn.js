define([
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], (
  ReportsBuiltInAbstract
) => {
  class ReportBuilderBuiltIn extends ReportsBuiltInAbstract {

    init() {
      return this.setSubLists(['Tickets', 'Chats', 'Ideas', 'People & Organizations', 'Knowledgebase', 'News', 'Downloads',
        'CommunityTopic.js', 'Tasks', 'Twitter']);
    }

    getUrlPart() {
      return 'builder';
    }
  }
  return ReportBuilderBuiltIn;
});
