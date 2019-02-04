define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_NewsSettings_Ctrl_NewsSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_NewsSettings_Ctrl_NewsSettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = [];
    }

    init() {
      return this.$scope.brand_id = this.$stateParams.brandId;
    }

    initialLoad() {
      return this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/portal/news`).then(res => this.$scope.settings = res.data.data);
    }

    save() {
      this.startSpinner('saving');
      return this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/portal/news`, this.$scope.settings).then(() => {
        this.Growl.success('Settings saved');
        return this.stopSpinner('saving');
      });
    }
  }
  Admin_NewsSettings_Ctrl_NewsSettings.initClass();

  return Admin_NewsSettings_Ctrl_NewsSettings.EXPORT_CTRL();
});
