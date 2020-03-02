define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_KbSettings_Ctrl_KbSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_KbSettings_Ctrl_KbSettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = [];
    }

    init() {
      this.$scope.form_errors = {};
      this.$scope.brand_id = this.$stateParams.brandId;
    }

    initialLoad() {
      return this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/portal/kb`).then(res => this.$scope.settings = res.data.data);
    }

    save() {
      this.$scope.form_errors = {};
      this.startSpinner('saving');

      return this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/portal/kb`, this.$scope.settings).then(
        () => {
          this.Growl.success('Settings saved');
          this.stopSpinner('saving');
        },
        (response) => {
          this.stopSpinner('saving');

          if (response.status === 400) {
            const { errors } = response.data;
            this.$scope.form_errors = errors;
          }
        }
      );
    }
  }
  Admin_KbSettings_Ctrl_KbSettings.initClass();


  return Admin_KbSettings_Ctrl_KbSettings.EXPORT_CTRL();
});
