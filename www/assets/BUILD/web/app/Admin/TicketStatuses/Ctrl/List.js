define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_List';
      this.CTRL_AS = 'TicketStatusesList';
      this.DEPS = [];
    }

    init() {
      this.stats = {};
      this.list = [];
      this.statusData = this.DataService.get('TicketStatuses');
    }

    initialLoad() {
      this.Api.sendGet('/ticket_statuses/stats').then(res => this.stats = res.data.status_stats);
      const promise = this.statusData.loadList();
      promise.then(list => this.list = list);
    }

    getStatusCount(status) {
      return this.stats[status] || 0;
    }
  }
  Admin_TicketStatuses_Ctrl_List.initClass();

  return Admin_TicketStatuses_Ctrl_List.EXPORT_CTRL();
});
