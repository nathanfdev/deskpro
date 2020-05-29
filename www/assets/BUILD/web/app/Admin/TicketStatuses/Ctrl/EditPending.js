define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_EditPending extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditPending';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('pending') : undefined);
      this.$scope.settings = {
        pending_waiting_time_mode: null
      };
    }

    initialLoad() {
      const promise = this.Api.sendGet('/settings/values/core_tickets.pending_status_waiting_time_mode').success((data) => {
        this.$scope.settings.pending_waiting_time_mode = data.value;
      });

      return promise;
    }

    saveSettings() {
      this.startSpinner('saving_settings');
      return this.Api.sendPost('/settings/values/core_tickets.pending_status_waiting_time_mode', {
        value: this.$scope.settings.pending_waiting_time_mode
      }).then(() => this.stopSpinner('saving_settings'));
    }
  }
  Admin_TicketStatuses_Ctrl_EditPending.initClass();

  return Admin_TicketStatuses_Ctrl_EditPending.EXPORT_CTRL();
});
