define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_EditArchived extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditArchived';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('archived') : undefined);
      this.$scope.settings = {
        enabled:           false,
        auto_archive_time: 2419200
      };
      this.$scope.times = [
        { id: 86400, label: '1 day' },
        { id: 259200, label: '3 days' },
        { id: 432000, label: '5 days' },
        { id: 604800, label: '1 week' },
        { id: 1209600, label: '2 weeks' },
        { id: 1814400, label: '3 weeks' },
        { id: 2592000, label: '1 month' },
        { id: 5184000, label: '2 months' },
        { id: 7776000, label: '3 months' },
        { id: 15552000, label: '6 months' },
        { id: 23328000, label: '9 months' },
        { id: 31536000, label: '1 year' },
        { id: 63072000, label: '2 years' }
      ];
    }

    initialLoad() {
      const promise = this.Api.sendGet('/ticket_statuses/archived').success((data) => {
        this.$scope.settings.enabled = data.archived_info.enabled;
        return this.$scope.settings.auto_archive_time = parseInt(data.archived_info.auto_archive_time);
      });

      return promise;
    }

    saveSettings() {
      this.startSpinner('saving_settings');
      const promise = this.Api.sendPostJson('/ticket_statuses/archived/settings', this.$scope.settings).then(() => this.stopSpinner('saving_settings'));
      return promise;
    }

    resetSearchTables() {
      this.startSpinner('is_resetting');
      return this.Api.sendPost('/ticket_statuses/archived/reset-search-tables').then(() => {
        this.$scope.reset_done = true;
        return this.stopSpinner('is_resetting');
      }
      , () => this.stopSpinner('is_resetting'));
    }
  }
  Admin_TicketStatuses_Ctrl_EditArchived.initClass();

  return Admin_TicketStatuses_Ctrl_EditArchived.EXPORT_CTRL();
});
