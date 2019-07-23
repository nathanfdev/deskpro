define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketApprovals_Ctrl_TemplateEdit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketApprovals_Ctrl_TemplateEdit';
      this.CTRL_AS = 'TicketApprovalsTemplateEdit';
      this.DEPS    = ['$stateParams'];
    }

    init() {
      this.typeId = parseInt(this.$stateParams.id || 0);
    }

    initialLoad() {

    }
  }
  Admin_TicketApprovals_Ctrl_TemplateEdit.initClass();

  return Admin_TicketApprovals_Ctrl_TemplateEdit.EXPORT_CTRL();
});
