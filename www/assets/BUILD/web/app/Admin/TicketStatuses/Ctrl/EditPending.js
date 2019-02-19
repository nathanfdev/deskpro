define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_TicketStatuses_Ctrl_EditPending extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditPending';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('pending') : undefined);
    }
  }
  Admin_TicketStatuses_Ctrl_EditPending.initClass();

  return Admin_TicketStatuses_Ctrl_EditPending.EXPORT_CTRL();
});
