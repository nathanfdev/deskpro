define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_EditHiddenValidating extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenValidating';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('hidden_validating') : undefined);
    }
  }
  Admin_TicketStatuses_Ctrl_EditHiddenValidating.initClass();

  return Admin_TicketStatuses_Ctrl_EditHiddenValidating.EXPORT_CTRL();
});
