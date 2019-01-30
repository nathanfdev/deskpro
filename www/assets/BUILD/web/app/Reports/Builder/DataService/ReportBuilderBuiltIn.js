/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/Builder/DataService/ReportBuiltInAbstract'
], function(
  ReportsBuiltInAbstract,
)  {
  let ReportBuilderBuiltIn;
  return (ReportBuilderBuiltIn = class ReportBuilderBuiltIn extends ReportsBuiltInAbstract {

    init() {
      return this.setSubLists(['Tickets', 'Chats', 'Ideas', 'People & Organizations', 'Knowledgebase', 'News', 'Downloads',
                                  'Feedback', 'Tasks', 'Twitter']);
    }

    getUrlPart() {
      return 'builder';
    }
  });
});