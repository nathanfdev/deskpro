define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_EditResolved extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditResolved';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('resolved') : undefined);
    }



    save() {
      this.Growl.success(this.getRegisteredMessage('saved_settings'));
      this.$scope.$broadcast('trigger.save');
      return this.$timeout(
        () => this.$state.go(this.$state.current, {}, {reload: true}),
        200
      );
    }
  }
  Admin_TicketStatuses_Ctrl_EditResolved.initClass();



  return Admin_TicketStatuses_Ctrl_EditResolved.EXPORT_CTRL();
});