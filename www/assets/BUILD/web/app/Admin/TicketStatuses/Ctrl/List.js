define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
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
      return this.Api.sendGet('/ticket_statuses/stats').then(res => this.stats = res.data.status_stats);
    }

    getStatusCount(status) {
      return this.stats[status] || 0;
    }
  }
  Admin_TicketStatuses_Ctrl_List.initClass();

  return Admin_TicketStatuses_Ctrl_List.EXPORT_CTRL();
});
