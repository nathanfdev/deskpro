define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base) {
  class Admin_Settings_Ctrl_UpdaterSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Settings_Ctrl_UpdaterSettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS = [];
    }

    init() {
      this.settings = null;
      this.info = null;
      this.didManualSet = false;
      return this.manualForm = {
        delay: '60'
      };
    }

    initialLoad() {
      const p1 = this.Api2.sendGet('/helpdesk/updater/settings').then(response => this.settings = response.data.data);
      const p2 = this.Api2.sendGet('/helpdesk/updater/status').then(response => this.info = response.data.data);
      return this.$q.all([p1, p2]);
    }

    save() {
      const postData = this.settings;

      this.startSpinner('saving');
      return this.Api2.sendPutJson('/helpdesk/updater/settings', postData).success(() => this.initialLoad().then(() => this.stopSpinner('saving').then(() => this.Growl.success(this.getRegisteredMessage('saved_settings'))))).error((info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }

    doManualSchedule() {
      const postData = {
        delay: parseInt(this.manualForm.delay) || 0
      };

      this.startSpinner('saving_manual');
      return this.Api2.sendPostJson('/helpdesk/updater/manual-schedule', postData).success(() => this.initialLoad().then(() => {
        this.didManualSet = true;
        return this.stopSpinner('saving_manual').then(() => this.Growl.success(this.getRegisteredMessage('saved_settings')));
      })).error((info, code) => {
        this.stopSpinner('saving_manual', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_Settings_Ctrl_UpdaterSettings.initClass();

  return Admin_Settings_Ctrl_UpdaterSettings.EXPORT_CTRL();
});
