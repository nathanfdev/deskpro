define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_EditAwaitingAgent extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingAgent';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('awaiting_agent') : undefined);
    }
  }
  Admin_TicketStatuses_Ctrl_EditAwaitingAgent.initClass();

  return Admin_TicketStatuses_Ctrl_EditAwaitingAgent.EXPORT_CTRL();
});