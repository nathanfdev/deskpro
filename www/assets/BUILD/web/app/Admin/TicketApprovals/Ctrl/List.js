define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_List';
      this.CTRL_AS = 'TicketApprovalsList';
      this.DEPS = [];
    }

    init() {
      this.approvalTypes = {};
      this.stats = {};
      this.list = [];
      this.tree = [];
    }

    initialLoad() {
      this.Api2.sendGet('/approval_types').then(res => this.approvalTypes = res.data);
      // const promise = this.statusData.loadList();
      // promise.then(list => this.list = list);
      //
      // this.$scope.$watchCollection('TicketStatusesList.list', (list) => {
      //   this.tree = this.statusData.getTree();
      // });
    }

    // getTopStatusRouteName(status) {
    //   return 'tickets.statuses.' + status;
    // }
    //
    // getStatusCount(status) {
    //   return this.stats[status] || 0;
    // }
  }
  Admin_TicketApprovals_Ctrl_List.initClass();

  return Admin_TicketApprovals_Ctrl_List.EXPORT_CTRL();
});
