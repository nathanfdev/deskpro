// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_List';
      this.CTRL_AS = 'TicketStatusesList';
      this.DEPS = [];
    }

    init() {
      this.stats = {};
    }

    initialLoad() {
      return this.Api.sendGet('/ticket_statuses/stats').then( res => {
        return this.stats = res.data.status_stats;
      });
    }

    getStatusCount(status) {
      return this.stats[status] || 0;
    }
  }
  Admin_TicketStatuses_Ctrl_List.initClass();

  return Admin_TicketStatuses_Ctrl_List.EXPORT_CTRL();
});