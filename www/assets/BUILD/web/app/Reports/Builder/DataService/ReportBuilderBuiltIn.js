// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
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