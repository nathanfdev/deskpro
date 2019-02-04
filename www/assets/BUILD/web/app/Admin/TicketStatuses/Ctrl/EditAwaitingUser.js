define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_TicketStatuses_Ctrl_EditAwaitingUser extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingUser';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('awaiting_user') : undefined);

      return this.$scope.editTemplate = esc => this.$modal.open({
        templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
        controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
        resolve:     {
          templateName() { return 'DeskPRO:emails_user:ticket-rate.html.twig'; }
        }
      });
    }


    save() {
      this.Growl.success(this.getRegisteredMessage('saved_settings'));
      this.$scope.$broadcast('trigger.save');
      return this.$timeout(
        () => this.$state.go(this.$state.current, {}, { reload: true }),
        200
      );
    }
  }
  Admin_TicketStatuses_Ctrl_EditAwaitingUser.initClass();


  return Admin_TicketStatuses_Ctrl_EditAwaitingUser.EXPORT_CTRL();
});
